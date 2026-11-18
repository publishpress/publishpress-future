# Recommendation

**HEALTHY** at **4.4/5.0**.

No GHSA. No confirmed exploit for an unauthenticated user or a stock WordPress role.

## Exit criteria (stay HEALTHY, move toward 5.0)

1. `RestApiV1::updateWorkflow`, `RestApiV1::deleteWorkflow`, and `WorkflowsList::updateWorkflowStatus` check `publish_publishpress_workflows` and `unpublish_publishpress_workflows`.
2. `userCanScheduleStatusChange` requires `delete_post` for status `trash`.
3. Taxonomy schedule requires `assign_terms` and a taxonomy that belongs to the post type.
4. Settings `admin_init` handlers require `manage_options`.

After step 1, re-score. Expect about **4.6** if the other rows stay theoretical. A **5.0** needs steps 2-4 as well, or a written decision that those checks are out of scope because stock caps already match.

## Do not

- Do not file a GHSA for the workflow cap gap until a principal with edit and without publish can enable a workflow on a site that uses the split.
- Do not deduct score for the PHP 7.4 header.
