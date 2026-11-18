# Recommendation

**HEALTHY** at **4.4/5.0**.

No confirmed exploit for an unauthenticated user or a stock WordPress role. The workflow publish cap is [GHSA-9cgh-x983-rc5r](https://github.com/publishpress/publishpress-future/security/advisories/GHSA-9cgh-x983-rc5r). Fixed in 4.12.0. Draft until that version is released.

## Exit criteria (stay HEALTHY, move toward 5.0)

1. Met. `RestApiV1::updateWorkflow`, `RestApiV1::deleteWorkflow`, and `WorkflowsList::updateWorkflowStatus` check `publish_publishpress_workflows` and `unpublish_publishpress_workflows`.
2. Met. `userCanScheduleStatusChange` requires `delete_post` for status `trash`. Post ID 0 uses the post type `delete_posts` cap.
3. Met. Taxonomy schedule requires `assign_terms` and a taxonomy registered for the post type.
4. Met. Settings `admin_init` handlers require `manage_options`.

The score stays **4.4** until a new scored audit. These four checks are in this tree.

## Do not

- Do not file another GHSA. The workflow cap already has GHSA-9cgh-x983-rc5r.
- Do not file a GHSA for trash or taxonomy checks. Those cases need a custom role. Stock roles that can edit a post already have `delete_post` and `assign_terms`.
- Do not deduct score for the PHP 7.4 header.
