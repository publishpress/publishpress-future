---
name: wp-plugin-security-auditor
description: WP plugin security audit
---

# WP Plugin Security Auditor

Activate on security audit request. GHSA findings + security report.

## Mission

Security audit WP plugin. Generate:

1. **Security report** — AUDIT_REPORT.md + scores
2. **Security advisories** — GHSA vuln files (if issues)

## Exclude Dirs

Read `.cursor/skills/_fragments/exclude-dirs.md` before grep/search.

## Audit Methodology

### Phase 1: Vulnerability Search

Grep, exclude vendor/lib/tests/dist/dev-workspace:

**SQL Injection:**
- `\$wpdb->get_results.*\$` or `\$wpdb->query.*\$` without `prepare()`
- Direct string interpolation in SQL

**XSS:**
- `echo \$_(POST|GET|REQUEST)` without escaping
- Missing `esc_html()`/`esc_attr()`/`esc_url()`
- React `dangerouslySetInnerHTML` + user input

**CSRF:**
- Forms/AJAX without `wp_verify_nonce()`
- POST handlers / admin forms missing nonce

**Auth & Authorization:**
- Missing `current_user_can()` before privileged ops
- Weak API keys, insecure REST, missing capability checks, REST without `permission_callback`

**Dangerous Functions:**
- `eval\(|exec\(|system\(|shell_exec\(|passthru\(|base64_decode\(`
- `unserialize()` + user input, `create_function()`

**File Ops:**
- `move_uploaded_file()`, `file_put_contents()`
- Missing validation, path traversal (`../`), no extension checks

### Phase 2: WP-Specific Checks

- Input: `sanitize_text_field`, `sanitize_email`, `absint`, etc.
- Output: `esc_html`, `esc_attr`, `esc_url`, `wp_kses`, etc.
- `$wpdb->prepare()` on dynamic SQL
- Nonces on forms + AJAX
- `current_user_can()` before privileged ops
- REST `permission_callback`
- Hooks/filters injection points
- `wp_remote_*` vs curl

### Phase 3: Dependencies

composer.json:
- Outdated (3+ years = HIGH RISK)
- Stripe (v13+), PayPal
- Unmaintained (2+ years)
- PHP min 7.4, WP version req

Payment (if applicable): Stripe SDK/API/PCI/webhooks; PayPal IPN/webhook; API keys wp_options/constants not plaintext; no card data

## Security Scoring (0-5.0, one decimal)

**Scale:** 5.0 excellent → 4.0 healthy threshold → 2.5 needs-work → <2.5 critical

Grade findings: CRITICAL / HIGH / MEDIUM / LOW with file:line.

## Recommendation Logic

- **HEALTHY:** Security ≥4.0
- **NEEDS-WORK:** Security 2.5-3.9
- **CRITICAL:** Security <2.5

## Output Format

### Output 1: AUDIT_REPORT.md

Read `.cursor/skills/_fragments/audit-report-security.md` when generating the report.

### Output 2: GHSA Advisory Files

Read `.cursor/skills/_fragments/ghsa-template.md` when generating advisories.

## Security Accuracy Checklist

1. ✅ file:line accurate + verifiable
2. ✅ Exploitable, not theoretical only
3. ✅ Reachable by users
4. ✅ WP core sanitization doesn't already block
5. ✅ CVSS reflects impact
6. ✅ Recommendation per rules

**False positive check:** sanitized before vuln fn? capability earlier? nonce in parent? escaping before XSS? Trace multi-file data flow.

## Execution Workflow

1. Grep vulns: SQLi, XSS, CSRF, dangerous fns, file ops
2. WP practices: sanitization, escaping, nonces, caps, REST callbacks
3. Deps: composer.json outdated, payment SDKs, unmaintained
4. Score 0-5.0
5. Recommendation logic
6. AUDIT_REPORT.md + GHSA per vuln

## Interaction Protocol

- Request files to trace data flow
- Explain severity when unclear
- Highlight systemic patterns
- Actionable remediation
- Uncertain exploitability → caveats
- Complete workflow before final report
