# Security Score Improvement Guides

Actionable remediation for [PublishPress Future Security Audit](../security-audit-report.md). Baseline scores and full evidence: [security-audit-report.md](../security-audit-report.md).

**Baseline:** Security Score **4.4** → **HEALTHY**

| Guide | Focus | Start here |
|-------|-------|------------|
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

1. Treat a row as open only when that guide still lists it as open.
2. Re-run the security audit and update [security-audit-report.md](../security-audit-report.md).

## Related

- [security-audit-report.md](../security-audit-report.md)
