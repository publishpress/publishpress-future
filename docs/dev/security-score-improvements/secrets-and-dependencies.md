# Secrets and Dependencies

Baseline: **4.4** HEALTHY. Evidence: [security-audit-report.md](../security-audit-report.md).

No hardcoded API key or password in first-party PHP. No payment SDK.

## Bundled

| Package | Version | Note |
|---------|---------|------|
| woocommerce/action-scheduler | 4.1.0 | Current 4.1 line. Async request is vendor code. |
| jwadhams/json-logic-php | 1.5.1 | Used for workflow conditions. Operators are allow-listed in `JsonLogicEngine.php`. |
| publishpress/* | see report | First-party libraries. Reviews AJAX is vendor code. |

## P2

| Item | Evidence | Done-when |
|------|----------|-----------|
| PHP requirement text matches composer | Header `Requires PHP: 7.4`. `composer.json` `>=8.2`. | Plugin header, readme, and composer state one minimum. This does not change the security score. |

Email notification addresses are options set by `manage_options`. They are not secrets. Do not log raw message bodies in the debug table when debug is on for a production site. The debug screen already requires `manage_options`.
