# PublishPress Future Security Audit

Reviewed tree: `4.12.0-beta.1` (`post-expirator.php`). First-party code only: `src/`, `legacy/`, `post-expirator.php`, `services.php`. Vendor code under `lib/vendor/` is a dependency note, not a plugin finding.

## SPREADSHEET DATA

**IMPORTANT**: TAB characters between columns, not spaces.

```
Metric	Score/Value	Notes
Security Score	4.4	No confirmed exploit for a stock role or an unauthenticated user. The four prior cap concerns are fixed in this tree. Score stays 4.4 until a new scored audit.
Recommendation	HEALTHY	Score is at least 4.0. Workflow, trash, taxonomy, and settings caps are enforced in this tree.
```

## 1. Security Assessment

### Score: 4.4/5.0

🔴 **Critical Issues:**

- None.

🟡 **Concerns:**

- None. The four cap items below are fixed in this tree.

🟢 **Strengths:**

- Workflow enable requires `publish_publishpress_workflows`. Disable and delete require `unpublish_publishpress_workflows` (`RestApiV1::updateWorkflow`, `RestApiV1::deleteWorkflow`, `WorkflowsList::updateWorkflowStatus`). See GHSA-9cgh-x983-rc5r.
- Status `trash` requires `delete_post`. Post ID 0 uses the post type `delete_posts` cap (`CurrentUserModel::userCanScheduleStatusChange`). Stock roles that can edit a post already have this cap.
- Taxonomy actions require `assign_terms` and a taxonomy registered for the post type (`CurrentUserModel::userCanScheduleAction`). `GET /publishpress-future/v1/terms/{taxonomy}` returns no terms without `assign_terms`. Stock roles that can edit a post already have this cap.
- Settings save returns before nonce checks when `current_user_can('manage_options')` is false (`SettingsController::processFormSubmission`, `Settings\Controllers\Controller::processFormSubmission`).
- `[futureaction]` and `[postexpirator]` escape the date and allow only `p`, `div`, and `span` as the wrapper (`src/Modules/Expirator/Controllers/ShortcodeController.php:170-179`, `src/Modules/Settings/SettingsFacade.php:539-551`). This closes the CVE-2026-5247 wrapper path in this tree.
- Future Actions settings return unless `page` is `publishpress-future` or `publishpress-future-settings` (`src/Modules/Expirator/Controllers/SettingsController.php:398-402`).
- REST routes set `permission_callback`. Post expiration checks `read_post` or `edit_post` plus `publishpress_future_expire_post` (`src/Modules/Expirator/Controllers/RestAPIController.php:104-136`). Backup and debug log require `manage_options`.
- MCP abilities are private, reject extra JSON types, and check caps in each permission callback (`src/Modules/Mcp/Registry.php:102-133`). Schedule writes check `assign_terms` (`src/Modules/Mcp/Services/SchedulesService.php:200`).
- Term creation checks `edit_terms` (`src/Modules/Settings/Models/TaxonomiesModel.php:73-79`).
- Debug log download checks `manage_options` and a nonce, then sends `text/plain` (`src/Modules/Debug/Controllers/Controller.php:97-127`).
- Workflow SQL uses `$wpdb->prepare`. The legacy template include stays under `src/Views/` (`legacy/classes/Display.class.php:520-524`).
- No first-party `wp_ajax_nopriv_` handler and no `unserialize()` of request data.

## 2. Dependencies

**Critical Issues:** None in first-party code.

**Immediate Updates:** None required for this score.

**Bundled libraries** (`lib/vendor/composer/installed.php`):

- `woocommerce/action-scheduler` 4.1.0
- `jwadhams/json-logic-php` 1.5.1
- PublishPress libraries: bundled-translations 1.0.6, instance-protection 2.1.0, pimple 3.5.0.11, psr-container 2.0.2.1, wordpress-banners 1.3.1, wordpress-reviews 1.2.2, wordpress-version-notices 2.2.1

JSON Logic operators in workflow conditions are data keys, not PHP callables (`src/Modules/Workflows/Domain/Engine/JsonLogicEngine.php`). Workflow edit stays on `edit_publishpress_workflows`.

⚠️ **PHP Version Warning:** `post-expirator.php` says `Requires PHP: 7.4`. `composer.json` requires PHP `>=8.2`. This is informational. It does not change the score.

**WordPress Version:** Requires at least 6.8 (`post-expirator.php`).

## 3. Final Recommendation: HEALTHY

**Rationale:** No confirmed issue is reachable by an unauthenticated user or by a stock role. The four prior cap concerns are fixed in this tree. The score stays 4.4 until a new scored audit.

**Key Factors:**

- Shortcode output encoding and the settings page allowlist are present.
- REST, backup, debug download, and MCP abilities check a capability.
- Workflow publish and unpublish caps, trash `delete_post`, taxonomy `assign_terms`, and settings `manage_options` are enforced in this tree.
