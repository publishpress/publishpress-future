# PublishPress Future Security Audit

Reviewed tree: `4.12.0-beta.1` (`post-expirator.php`). First-party code only: `src/`, `legacy/`, `post-expirator.php`, `services.php`. Vendor code under `lib/vendor/` is a dependency note, not a plugin finding.

## SPREADSHEET DATA

**IMPORTANT**: TAB characters between columns, not spaces.

```
Metric	Score/Value	Notes
Security Score	4.4	No confirmed exploit for a stock role or an unauthenticated user. One likely cap gap on workflow publish.
Recommendation	HEALTHY	Score is at least 4.0. Fix the publish cap check before sites split workflow caps.
```

## 1. Security Assessment

### Score: 4.4/5.0

🔴 **Critical Issues:**

- None.

🟡 **Concerns:**

- Workflow publish, unpublish, and delete check `edit_publishpress_workflows` only (`src/Modules/Workflows/Rest/RestApiV1.php:125-150`, `src/Modules/Workflows/Rest/RestApiV1.php:295-357`, `src/Modules/Workflows/Controllers/WorkflowsList.php:322-341`). The post type and the Capabilities screen treat `publish_publishpress_workflows` and `unpublish_publishpress_workflows` as separate caps (`src/Modules/Workflows/Controllers/PostType.php:76-85`, `legacy/classes/Facade.class.php:194-206`). Stock install grants all three caps to administrator only (`legacy/classes/Facade.class.php:111-113`). Exploitability: Likely. Reachability: Capability `edit_publishpress_workflows` when an admin has removed the publish cap.
- Status change to `trash` does not check `delete_post` (`src/Modules/Expirator/Models/CurrentUserModel.php:61-64`, `src/Modules/Expirator/Models/CurrentUserModel.php:159-175`). `delete` does check `delete_post` (line 87-89). Cron runs the saved action with no logged-in user (`src/Modules/Expirator/ExpirationScheduler.php:254-264`) through `wp_update_post` (`src/Framework/WordPress/Models/PostModel.php:73-84`). Stock roles that can edit a post can also delete it. Exploitability: Theoretical. Reachability: custom role with `publishpress_future_expire_post` and `edit_post` but without `delete_post`.
- Taxonomy actions do not check `assign_terms` (`src/Modules/Expirator/Models/CurrentUserModel.php:91-98`). `POST /publishpress-future/v1/post-expiration/{id}` accepts any taxonomy key (`src/Modules/Expirator/Controllers/RestAPIController.php:521-528`). Execution calls `wp_set_object_terms` (`src/Framework/WordPress/Models/PostModel.php:300-302`). Stock `category` and `post_tag` use `assign_terms = edit_posts`, which `edit_post` already implies. Exploitability: Theoretical. Reachability: custom taxonomy whose `assign_terms` the user lacks.
- Settings save checks the page slug and a nonce, not `manage_options`, inside the handler (`src/Modules/Expirator/Controllers/SettingsController.php:391-411`, `src/Modules/Settings/Controllers/Controller.php:289-338`). The menu cap is `manage_options` (`src/Modules/Expirator/Controllers/ScheduledActionsController.php:119-127`). WordPress nonces are bound to the user and the session. Exploitability: Theoretical.

🟢 **Strengths:**

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

**Rationale:** No confirmed issue is reachable by an unauthenticated user or by a stock role. The score stays under 5.0 because workflow publish caps are advertised as separate and two handlers ignore them. Trash and taxonomy checks are theoretical on stock WordPress caps.

**Key Factors:**

- Shortcode output encoding and the settings page allowlist are present.
- REST, backup, debug download, and MCP abilities check a capability.
- Publish and unpublish workflow caps are not enforced on the REST update route or the list status form.
