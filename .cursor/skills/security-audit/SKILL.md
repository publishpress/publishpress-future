---
name: wp-plugin-security-auditor
description: WordPress plugin security audit
---

# WP Plugin Security Auditor

Use these instructions when the user requests a security audit of a WordPress plugin. This generates detailed security findings (GHSA format) and a security-focused report for tracking plugin health.

## Mission

Conduct a thorough security audit of a WordPress plugin. Generate:

1. **Security report** - AUDIT_REPORT.md with findings and scores
2. **Security advisories** - Detailed vulnerability reports in GHSA format (when issues found)

## Directories to Exclude

NEVER analyze code in these directories (applies to all searches and greps):

- `/vendor/`
- `/lib/vendor/`
- `/dist/`
- `/.git/` and all hidden folders (`.*`)
- `/dev-workspace-cache/`
- `/dev-workspace/`
- `/node_modules/`
- `/tests/`

## Audit Methodology

### Phase 1: Security Vulnerability Search

Use Grep tool to search for these patterns (exclude vendor, lib, tests, dist, dev-workspace):

**SQL Injection:**

- Pattern: `\$wpdb->get_results.*\$` or `\$wpdb->query.*\$` without `prepare()`
- Look for: Direct string interpolation in SQL queries
- Example: `$wpdb->get_results("SELECT * FROM table WHERE id = $id")`

**XSS (Cross-Site Scripting):**

- Pattern: `echo \$_(POST|GET|REQUEST)` without escaping
- Look for: Unescaped output, missing `esc_html()`, `esc_attr()`, `esc_url()`
- Check: React `dangerouslySetInnerHTML` with user input

**CSRF (Cross-Site Request Forgery):**

- Pattern: Form submissions and AJAX handlers without `wp_verify_nonce()`
- Look for: POST handlers missing nonce verification
- Check: Admin forms without nonce fields

**Authentication & Authorization:**

- Pattern: Missing `current_user_can()` checks before privileged operations
- Look for: Weak API keys, insecure REST endpoints, missing capability checks
- Check: REST API routes without proper `permission_callback`

**Dangerous Functions:**

- Pattern: `eval\(|exec\(|system\(|shell_exec\(|passthru\(|base64_decode\(`
- Look for: Remote code execution risks
- Check: `unserialize()` with user input, `create_function()`

**File Operations:**

- Pattern: File upload handlers, `move_uploaded_file()`, `file_put_contents()`
- Look for: Insufficient validation, path traversal (`../`), missing extension checks
- Check: Directory traversal risks in file paths

### Phase 2: WordPress-Specific Security Checks

- Verify all user input is sanitized using appropriate functions (`sanitize_text_field`, `sanitize_email`, `absint`, etc.)
- Verify all output is escaped using appropriate functions (`esc_html`, `esc_attr`, `esc_url`, `wp_kses`, etc.)
- Check that `$wpdb->prepare()` is used for all dynamic SQL queries
- Verify nonce implementation on all form submissions and AJAX handlers
- Check capability verification (`current_user_can`) before privileged operations
- Analyze REST API `permission_callback` implementations
- Review hooks and filters for potential injection points
- Check for proper use of `wp_remote_*` functions vs. curl

### Phase 3: Dependencies Security Analysis

**Check composer.json files for:**

- Outdated packages (3+ years old = HIGH RISK)
- Payment SDKs: Stripe (current: v13+), PayPal (current versions)
- Unmaintained libraries (no updates in 2+ years)
- PHP version requirements (min 7.4)
- WordPress version requirements

**Payment Security (if applicable):**

- Stripe: SDK version, API version, PCI compliance patterns, webhook verification
- PayPal: IPN/webhook handling, payment verification
- API key storage (should use wp_options or constants, NOT plaintext in code)
- Card data handling (should NOT exist - PCI compliance violation)

## Security Scoring (0-5.0, one decimal)

**Score Guidelines:**

- **4.5-5.0 (Excellent):** No significant issues, follows security best practices
- **3.5-4.4 (Good):** Minor issues only, easily fixable
- **2.5-3.4 (Fair):** Some security concerns, patchable (outdated dependencies, weak validation)
- **1.5-2.4 (Poor):** Serious issues (outdated payment SDKs, weak auth, no CSRF)
- **0.0-1.4 (Critical):** Active vulnerabilities (SQL injection, auth bypass, XSS)

Grade each finding: CRITICAL / HIGH / MEDIUM / LOW with file:line references

## Recommendation Logic

- **HEALTHY:** Security ≥4.0
- **NEEDS-WORK:** Security 2.5-3.9
- **CRITICAL:** Security <2.5

## Output Format

Generate TWO types of outputs:

### Output 1: AUDIT_REPORT.md

Create a file named `AUDIT_REPORT.md` in the plugin root:

```markdown
# [PLUGIN_NAME] Security Audit

## SPREADSHEET DATA

**IMPORTANT**: Use actual TAB characters between columns, not spaces.

    ```
    Metric	Score/Value	Notes
    Security Score	[X.X]	[Brief: Main security findings, max 2 sentences]
    Recommendation	[HEALTHY/NEEDS-WORK/CRITICAL]	[One-line rationale based on decision rules]
    ```

## 1. Security Assessment

### Score: X.X/5.0**

🔴 **Critical Issues:**

- [Issue description] (file:line)

🟡 **Concerns:**

- [Issue description] (file:line)

🟢 **Strengths:**

- [Positive finding]

## 2. Dependencies

**Critical Issues:** [List outdated/risky packages]
**Immediate Updates Required:** [What needs updating]
**PHP Version:** [Current requirement]
**WordPress Version:** [Current requirement]

## 3. Final Recommendation: [HEALTHY/NEEDS-WORK/CRITICAL]

**Rationale:** [2-3 sentence justification based on decision rules]

**Key Decision Factors:**

- [Brief factor 1]
- [Brief factor 2]
- [Brief factor 3]
```

### Output 2: Individual Security Advisories (GHSA Format)

For EACH security vulnerability found, create a **separate markdown file** in the `/security-audit/` directory.

#### File Naming Convention

Use this format: `[plugin-name]-[###]-[SEVERITY]-[short-description].md`

**Examples:**

- `myplugin-001-CRITICAL-sql-injection-custom-query.md`
- `myplugin-002-HIGH-xss-unescaped-output.md`
- `myplugin-003-MEDIUM-csrf-missing-nonce.md`

**Rules:**

- Number issues sequentially starting from 001
- Severity in UPPERCASE: CRITICAL, HIGH, MEDIUM, LOW
- Description in lowercase with hyphens (kebab-case)
- Only create these files if vulnerabilities are found

#### GHSA Advisory Format

Each security advisory file must contain:

```
## Security Advisory

### Summary

[One-line description of the vulnerability]

### Severity

[Critical / High / Medium / Low]

### CVSS Score

[Calculate CVSS 3.1 score, e.g., 8.8 (High)]

### CWE

[CWE ID and name, e.g., CWE-89: SQL Injection]

### Affected Versions

[Version range or "all versions" based on code analysis]

### Vulnerability Details

**Type:** [Vulnerability type]
**Location:** [File path and line number(s)]
**Attack Vector:** [Network/Local]
**User Interaction:** [Required/None]
**Privileges Required:** [None/Low/High]

### Description

[Detailed technical description of the vulnerability, explaining:
- What the vulnerable code does
- Why it's vulnerable
- What an attacker could achieve]

### Proof of Concept

[Provide specific steps or example payloads to demonstrate the vulnerability]

### Vulnerable Code

      ```php
      [Exact code snippet showing the vulnerable code with file path and line numbers]
      ```

### Remediation

[Specific code fix with corrected code example]

      ```php
      [Fixed code snippet]
      ```

### References

- [Relevant OWASP links]
- [WordPress security documentation]
- [Any other relevant references]
```

## Quality Assurance Checklist

### Accuracy Requirements

1. ✅ All numeric scores use exactly one decimal place (e.g., 3.7, not 3 or 3.70)
2. ✅ Spreadsheet data uses actual TAB characters (not spaces) between columns
3. ✅ All file:line references are accurate and verifiable
4. ✅ Security vulnerabilities are exploitable, not just theoretical
5. ✅ Code path to vulnerability is reachable by users
6. ✅ WordPress core sanitization doesn't already prevent exploitation
7. ✅ CVSS scores accurately reflect impact
8. ✅ Recommendation follows decision rules strictly

### False Positive Avoidance

- Check if data is sanitized before the vulnerable function
- Verify capability checks aren't performed earlier in the call stack
- Confirm nonces aren't verified in parent functions
- Check for output escaping before assuming XSS
- Trace data flow through multiple files if necessary

## Execution Workflow

Follow this sequence for a thorough security audit:

1. **Search for vulnerabilities** (use Grep tool):
   - SQL injection patterns
   - XSS patterns
   - CSRF patterns
   - Dangerous functions
   - File operation risks

2. **Verify WordPress security practices** (use Read/Grep tools):
   - Input sanitization
   - Output escaping
   - Nonce usage
   - Capability checks
   - REST API permission callbacks

3. **Review dependencies** (use Read tool on composer.json):
   - Outdated packages
   - Payment SDK versions
   - Unmaintained libraries

4. **Score security findings:**
   - Security: 0-5.0 (based on findings)

5. **Apply recommendation logic:**
   - HEALTHY: Security ≥4.0
   - NEEDS-WORK: Security 2.5-3.9
   - CRITICAL: Security <2.5

6. **Generate outputs:**
   - Create AUDIT_REPORT.md
   - Create individual GHSA files for each vulnerability (if any)

## Interaction Protocol

- Request additional files if needed to trace data flow
- Explain severity reasoning if unclear
- Highlight systemic issues when patterns emerge
- Provide actionable remediation, not just problem identification
- When uncertain about exploitability, report with caveats
- Always complete the full audit workflow before generating the final report
