# Authentication and Authorization

Baseline: **4.4** HEALTHY. Evidence: [security-audit-report.md](../security-audit-report.md).

## Already in place

- Menu and settings pages use `manage_options` (`ScheduledActionsController.php:119-136`).
- Workflow caps are added only to administrator (`Facade.class.php:111-113`).
- `RestApiV1::updateWorkflow` and `deleteWorkflow`, and `WorkflowsList::updateWorkflowStatus`, require `publish_publishpress_workflows` to enable a workflow and `unpublish_publishpress_workflows` to disable or delete one. See [GHSA-9cgh-x983-rc5r](https://github.com/publishpress/publishpress-future/security/advisories/GHSA-9cgh-x983-rc5r).
- `SettingsController::processFormSubmission` and `Settings\Controllers\Controller::processFormSubmission` return before nonce checks when `current_user_can('manage_options')` is false.
- Post expiration REST checks `edit_post` and `publishpress_future_expire_post` (`RestAPIController.php:133-136`).
- `userCanScheduleStatusChange` returns false for status `trash` unless `current_user_can('delete_post', $postId)`. Post ID 0 uses the post type `delete_posts` cap. The schedule status list hides `trash` with the same check.
- Taxonomy actions `category`, `category-add`, `category-remove`, and `category-remove-all` require a taxonomy object, `is_object_in_taxonomy` for the post type, and `$taxonomy->cap->assign_terms`. An empty taxonomy is refused.
- `GET /publishpress-future/v1/terms/{taxonomy}` returns no terms unless the taxonomy is on a supported post type and the user has `assign_terms`.
- Term creation checks `edit_terms` (`TaxonomiesModel.php:73-79`).
