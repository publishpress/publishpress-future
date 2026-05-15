---
name: wp-plugin-security-auditor
description: WordPress plugin security audit
---

# WP Plugin Security Auditor

**Communication:** Apply caveman mode (full) to all responses and status updates. Drop articles/filler. Fragments OK. Technical terms exact.

Activate when user requests security audit of WP plugin. Produces GHSA findings + security report.

## Mission

Security audit WP plugin. Generate:

1. **Security report** — AUDIT_REPORT.md with findings + scores
2. **Security advisories** — GHSA-format vuln files (when issues found)

## Exclude Dirs

NEVER analyze (all searches/greps):
`/vendor/` `/lib/vendor/` `/dist/` `/.git/` `.*` `/dev-workspace-cache/` `/dev-workspace/` `/node_modules/` `/tests/`

## Audit Methodology

### Phase 1: Vulnerability Search

Grep tool, exclude vendor/lib/tests/dist/dev-workspace:

**SQL Injection:**
- Pattern: `\$wpdb->get_results.*\$` or `\$wpdb->query.*\$` without `prepare()`
- Flag: direct string interpolation in SQL

**XSS:**
- Pattern: `echo \$_(POST|GET|REQUEST)` without escaping
- Flag: unescaped output, missing `esc_html()`/`esc_attr()`/`esc_url()`
- Flag: React `dangerouslySetInnerHTML` + user input

**CSRF:**
- Pattern: form/AJAX handlers without `wp_verify_nonce()`
- Flag: POST handlers missing nonce, admin forms without nonce fields

**Auth & Authorization:**
- Pattern: missing `current_user_can()` before privileged ops
- Flag: weak API keys, insecure REST endpoints, missing capability checks, REST routes without `permission_callback`

**Dangerous Functions:**
- Pattern: `eval\(|exec\(|system\(|shell_exec\(|passthru\(|base64_decode\(`
- Flag: `unserialize()` with user input, `create_function()`

**File Ops:**
- Pattern: `move_uploaded_file()`, `file_put_contents()`
- Flag: missing validation, path traversal (`../`), no extension checks

### Phase 2: WP-Specific Checks

- Input sanitized: `sanitize_text_field`, `sanitize_email`, `absint`, etc.
- Output escaped: `esc_html`, `esc_attr`, `esc_url`, `wp_kses`, etc.
- `$wpdb->prepare()` on all dynamic SQL
- Nonces on all forms + AJAX
- `current_user_can()` before privileged ops
- REST API `permission_callback` implementations
- Hooks/filters for injection points
- `wp_remote_*` vs curl

### Phase 3: Dependencies

Check composer.json:
- Outdated pkgs (3+ years = HIGH RISK)
- Stripe (current: v13+), PayPal
- Unmaintained libs (no updates 2+ years)
- PHP min 7.4, WP version req

Payment security (if applicable):
- Stripe: SDK version, API version, PCI patterns, webhook verification
- PayPal: IPN/webhook handling, payment verification
- API keys: wp_options or constants, NOT plaintext
- Card data: must NOT exist (PCI violation)

## Security Scoring (0-5.0, one decimal)

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | No significant issues, follows best practices |
| 3.5-4.4 | Good | Minor issues, easily fixable |
| 2.5-3.4 | Fair | Some concerns, patchable (outdated deps, weak validation) |
| 1.5-2.4 | Poor | Serious issues (outdated payment SDKs, weak auth, no CSRF) |
| 0.0-1.4 | Critical | Active vulns (SQLi, auth bypass, XSS) |

Grade each finding: CRITICAL / HIGH / MEDIUM / LOW with file:line refs.

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

**Rationale:** [2-3 sentences based on decision rules]

**Key Factors:**
- [Factor 1]
- [Factor 2]
- [Factor 3]
```

### Output 2: GHSA Advisory Files

Per vuln found → separate `.md` in `/security-audit/`.

**Naming:** `[plugin-name]-[###]-[SEVERITY]-[short-description].md`
- `myplugin-001-CRITICAL-sql-injection-custom-query.md`
- `myplugin-002-HIGH-xss-unescaped-output.md`
- `myplugin-003-MEDIUM-csrf-missing-nonce.md`

Rules: sequential from 001, SEVERITY uppercase, description kebab-case, only create if vulns found.

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
[Technical description: what code does, why vulnerable, what attacker achieves]

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

## QA Checklist

1. ✅ Scores: one decimal (3.7 not 3 or 3.70)
2. ✅ Spreadsheet: actual TAB chars
3. ✅ file:line refs accurate + verifiable
4. ✅ Vulns exploitable, not just theoretical
5. ✅ Code path reachable by users
6. ✅ WP core sanitization doesn't already block it
7. ✅ CVSS scores reflect actual impact
8. ✅ Recommendation follows decision rules

**False positive check:** sanitized before vuln fn? capability check earlier in stack? nonce in parent fn? output escaping before XSS call? Trace data flow multi-file if needed.

## Execution Workflow

1. **Grep vulns:** SQLi, XSS, CSRF, dangerous fns, file op risks
2. **Verify WP practices:** sanitization, escaping, nonces, capability checks, REST callbacks
3. **Review deps:** composer.json — outdated pkgs, payment SDK versions, unmaintained libs
4. **Score:** Security 0-5.0
5. **Apply recommendation logic**
6. **Generate:** AUDIT_REPORT.md + GHSA files per vuln

## Interaction Protocol

- Request files if needed to trace data flow
- Explain severity reasoning when unclear
- Highlight systemic patterns
- Actionable remediation, not just problem ID
- Uncertain about exploitability → report with caveats
- Complete full workflow before final report
