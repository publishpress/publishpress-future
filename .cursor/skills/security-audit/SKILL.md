---
name: wp-plugin-security-auditor
description: WP plugin security audit
---

# WP Plugin Security Auditor

**Communication:** Apply `/caveman` mode (full) to all responses and status updates when this skill is active. Drop articles/filler. Fragments OK. Technical terms exact. Code/commits/PR bodies stay normal unless user says otherwise.

Activate on security audit request. GHSA findings + security report.

## Mission

Security audit WP plugin. Generate:

1. **Security report** — AUDIT_REPORT.md + scores
2. **Security advisories** — GHSA vuln files (if issues)

## Exclude Dirs

NEVER analyze:
`/vendor/` `/lib/vendor/` `/dist/` `/.git/` `.*` `/dev-workspace-cache/` `/dev-workspace/` `/node_modules/` `/tests/`

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

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | No significant issues, best practices |
| 3.5-4.4 | Good | Minor, easily fixable |
| 2.5-3.4 | Fair | Patchable (outdated deps, weak validation) |
| 1.5-2.4 | Poor | Serious (outdated payment SDKs, weak auth, no CSRF) |
| 0.0-1.4 | Critical | Active vulns (SQLi, auth bypass, XSS) |

Grade findings: CRITICAL / HIGH / MEDIUM / LOW with file:line.

## Recommendation Logic

- **HEALTHY:** Security ≥4.0
- **NEEDS-WORK:** Security 2.5-3.9
- **CRITICAL:** Security <2.5

## Output Format

### Output 1: AUDIT_REPORT.md

```markdown
# [PLUGIN_NAME] Security Audit

## SPREADSHEET DATA

**IMPORTANT**: TAB characters between columns, not spaces.

    ```
    Metric	Score/Value	Notes
    Security Score	[X.X]	[Main findings, max 2 sentences]
    Recommendation	[HEALTHY/NEEDS-WORK/CRITICAL]	[One-line rationale]
    ```

## 1. Security Assessment

### Score: X.X/5.0

🔴 **Critical Issues:**
- [Issue] (file:line)

🟡 **Concerns:**
- [Issue] (file:line)

🟢 **Strengths:**
- [Positive finding]

## 2. Dependencies

**Critical Issues:** [Outdated/risky pkgs]
**Immediate Updates:** [What needs updating]
**PHP Version:** [Current req]
**WordPress Version:** [Current req]

## 3. Final Recommendation: [HEALTHY/NEEDS-WORK/CRITICAL]

**Rationale:** [2-3 sentences]

**Key Factors:**
- [Factor 1]
- [Factor 2]
- [Factor 3]
```

### Output 2: GHSA Advisory Files

Per vuln → `.md` in `/security-audit/`.

**Naming:** `[plugin-name]-[###]-[SEVERITY]-[short-description].md`
- `myplugin-001-CRITICAL-sql-injection-custom-query.md`
- `myplugin-002-HIGH-xss-unescaped-output.md`
- `myplugin-003-MEDIUM-csrf-missing-nonce.md`

Rules: sequential 001+, SEVERITY uppercase, kebab-case, only if vulns.

**GHSA format:**

```markdown
## Security Advisory

### Summary
[One-line description]

### Severity
[Critical / High / Medium / Low]

### CVSS Score
[CVSS 3.1 score, e.g., 8.8 (High)]

### CWE
[CWE ID + name, e.g., CWE-89: SQL Injection]

### Affected Versions
[Version range or "all versions"]

### Vulnerability Details
**Type:** [type]
**Location:** [file:line]
**Attack Vector:** [Network/Local]
**User Interaction:** [Required/None]
**Privileges Required:** [None/Low/High]

### Description
[Technical: what code does, why vulnerable, attacker outcome]

### Proof of Concept
[Steps or payloads]

### Vulnerable Code
      ```php
      [Snippet with file:line]
      ```

### Remediation
[Fix with corrected code]
      ```php
      [Fixed snippet]
      ```

### References
- [OWASP links]
- [WP security docs]
```

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
