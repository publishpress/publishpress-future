# Injection and Output Encoding

Baseline: **4.4** HEALTHY. Evidence: [security-audit-report.md](../security-audit-report.md).

No open injection finding.

## Keep

| Control | Evidence | Done-when |
|---------|----------|-----------|
| Shortcode wrapper allow-list | `SettingsFacade.php:539-551` allows `p`, `div`, `span`. Other values become `div`. | A wrapper value of `script` or `textarea` renders as `div`. The date is `esc_html`. |
| Shortcode output | `ShortcodeController.php:170-179` | `class` is `esc_attr`. Empty wrapper returns escaped text only. |
| Footer text | `ContentController.php:93` | Footer HTML is `esc_html`. Style is `esc_attr`. |
| SQL | Workflow and logger queries use `$wpdb->prepare`. | New queries keep placeholders. Identifiers stay internal. |
| JSON Logic | `JsonLogicEngine.php` registers string operators only. | Workflow JSON cannot name a PHP function. |

## Not a finding

CVE-2026-5247 (wrapper tag and date text) is fixed in this tree. Do not reopen it without a new output path.

Stored workflow JSON is edited by `edit_publishpress_workflows`. That is the same principal as the workflow editor. It is not cross-user XSS by itself.
