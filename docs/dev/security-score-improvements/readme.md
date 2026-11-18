# Security Score Improvement Guides

Actionable remediation for [PublishPress Future Security Audit](../security-audit-report.md). Baseline scores and full evidence: [security-audit-report.md](../security-audit-report.md).

**Baseline:** Security Score **4.4** → **HEALTHY**

| Guide | Focus | Start here |
|-------|-------|------------|
| [Authentication & Authorization](authentication-and-authorization.md) | Workflow publish caps, schedule caps | Enforce `publish_publishpress_workflows` on REST and the list form |
| [Injection & Output Encoding](injection-and-output-encoding.md) | Shortcode, SQL, templates | Keep the wrapper allow-list |
| [File Handling & Downloads](file-handling-and-downloads.md) | Log download, template include | No open issue |
| [Secrets & Dependencies](secrets-and-dependencies.md) | Bundled libraries, PHP header | Align the PHP requirement text |
| [Recommendation](recommendation.md) | HEALTHY | Close the publish cap gap |

**Advisory:** [GHSA-9cgh-x983-rc5r](https://github.com/publishpress/publishpress-future/security/advisories/GHSA-9cgh-x983-rc5r). Fixed in 4.12.0. Draft until that version is released. Stock roles were not affected.

## Thresholds

| Label | Security Score |
|-------|----------------|
| HEALTHY | ≥4.0 |
| NEEDS-WORK | 2.5–3.9 |
| CRITICAL | <2.5 |

## How to use

1. Work the P0 row in [Authentication & Authorization](authentication-and-authorization.md) first.
2. Use **Done-when** on each row before claiming progress.
3. Re-run the security audit and update [security-audit-report.md](../security-audit-report.md).

## Related

- [security-audit-report.md](../security-audit-report.md)
