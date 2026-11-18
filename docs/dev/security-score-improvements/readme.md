# Security Score Improvement Guides

Actionable remediation for [PublishPress Future Security Audit](../security-audit-report.md). Baseline scores and full evidence: [security-audit-report.md](../security-audit-report.md).

**Baseline:** Security Score **4.4** → **HEALTHY**

| Guide | Focus | Start here |
|-------|-------|------------|
| [Authentication & Authorization](authentication-and-authorization.md) | Workflow publish caps, schedule caps | Caps are in [Already in place](authentication-and-authorization.md) |
| [Injection & Output Encoding](injection-and-output-encoding.md) | Shortcode, SQL, templates | Keep the wrapper allow-list |
| [File Handling & Downloads](file-handling-and-downloads.md) | Log download, template include | No open issue |
| [Secrets & Dependencies](secrets-and-dependencies.md) | Bundled libraries, PHP header | Align the PHP requirement text |
| [Recommendation](recommendation.md) | HEALTHY | Exit criteria 1–4 are met |

**Advisory:** [GHSA-9cgh-x983-rc5r](https://github.com/publishpress/publishpress-future/security/advisories/GHSA-9cgh-x983-rc5r). Fixed in 4.12.0. Draft until that version is released. Stock roles were not affected.

## Thresholds

| Label | Security Score |
|-------|----------------|
| HEALTHY | ≥4.0 |
| NEEDS-WORK | 2.5–3.9 |
| CRITICAL | <2.5 |

## How to use

1. Read [Authentication & Authorization](authentication-and-authorization.md). The listed caps are already in place.
2. Treat a row as open only when it is still listed as open in that guide.
3. Re-run the security audit and update [security-audit-report.md](../security-audit-report.md).

## Related

- [security-audit-report.md](../security-audit-report.md)
