# Authentication and Authorization

Baseline: **4.4** HEALTHY. Evidence: [security-audit-report.md](../security-audit-report.md).

## P0

| Item | Evidence | Exploitability | Done-when |
|------|----------|----------------|-----------|
| Enforce publish and unpublish caps | `RestApiV1.php:295-357` publishes and deletes on `edit_publishpress_workflows` only. `WorkflowsList.php:322-341` does the same. MCP already checks publish and unpublish (`WorkflowsService.php:252-263`). | Likely when caps are split | `updateWorkflow` returns 403 without `publish_publishpress_workflows` when status becomes `publish`, and without `unpublish_publishpress_workflows` when status leaves `publish` or the workflow is deleted. `updateWorkflowStatus` uses the same checks. A user with only the edit cap cannot enable a workflow. |

## P1

| Item | Evidence | Exploitability | Done-when |
|------|----------|----------------|-----------|
| Trash status checks `delete_post` | `CurrentUserModel.php:159-175` allows `trash` without `delete_post`. `PostModel.php:73-84` writes the status with `wp_update_post` during cron. | Theoretical (custom role) | `userCanScheduleStatusChange` returns false for `trash` unless `current_user_can('delete_post', $postId)`. |
| Taxonomy actions check `assign_terms` | `CurrentUserModel.php:91-98`. REST accepts any taxonomy at `RestAPIController.php:521-528`. | Theoretical (custom taxonomy) | Schedule is refused when the user lacks `$taxonomy->cap->assign_terms` or the taxonomy is not registered for the post type. |
| Settings handlers check `manage_options` | `SettingsController.php:391-411`. `Settings/Controllers/Controller.php:289-338`. | Theoretical | Both handlers return before nonce checks when `current_user_can('manage_options')` is false. |

## Already in place

- Menu and settings pages use `manage_options` (`ScheduledActionsController.php:119-136`).
- Workflow caps are added only to administrator (`Facade.class.php:111-113`).
- Post expiration REST checks `edit_post` and `publishpress_future_expire_post` (`RestAPIController.php:133-136`).
- Term creation checks `edit_terms` (`TaxonomiesModel.php:73-79`).
