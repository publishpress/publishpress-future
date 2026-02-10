---
name: wp-plugin-security-auditor
description: WordPress plugin security and code quality audit for acquisition evaluation
---

# WP Plugin Security & Code Quality Auditor

Use these instructions when the user requests a security audit, code quality review, or acquisition evaluation of a WordPress plugin. This generates both detailed security findings (GHSA format) and spreadsheet-compatible metrics for acquisition decisions.

## Mission

Conduct a comprehensive technical due diligence audit covering security, code quality, and maintainability. Generate both:
1. **Spreadsheet metrics** - Tab-separated data for Plugin Acquisition Evaluation Spreadsheet
2. **Security advisories** - Detailed vulnerability reports in GHSA format (when issues found)

The output is formatted for direct paste into evaluation spreadsheets with scores aligned to acquisition decision criteria.

## Directories to Exclude

NEVER analyze code in these directories (applies to all searches, greps, and phpmetrics):
- `/vendor/`
- `/lib/vendor/`
- `/dist/`
- `/.git/` and all hidden folders (`.*`)
- `/dev-workspace/`
- `/node_modules/`
- `/tests/`

## Audit Methodology

### Phase 1: Security Assessment (CRITICAL PRIORITY)

#### Search for Critical Vulnerabilities

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

#### WordPress-Specific Security Checks

- Verify all user input is sanitized using appropriate functions (`sanitize_text_field`, `sanitize_email`, `absint`, etc.)
- Verify all output is escaped using appropriate functions (`esc_html`, `esc_attr`, `esc_url`, `wp_kses`, etc.)
- Check that `$wpdb->prepare()` is used for all dynamic SQL queries
- Verify nonce implementation on all form submissions and AJAX handlers
- Check capability verification (`current_user_can`) before privileged operations
- Analyze REST API `permission_callback` implementations
- Review hooks and filters for potential injection points
- Check for proper use of `wp_remote_*` functions vs. curl

#### Security Scoring (0-5.0, one decimal)

**Score Guidelines:**
- **4.5-5.0 (Excellent):** No significant issues, follows security best practices
- **3.5-4.4 (Good):** Minor issues only, easily fixable
- **2.5-3.4 (Fair):** Some security concerns, patchable (outdated dependencies, weak validation)
- **1.5-2.4 (Poor):** Serious issues (outdated payment SDKs, weak auth, no CSRF)
- **0.0-1.4 (Critical):** Active vulnerabilities (SQL injection, auth bypass, XSS)

Grade each finding: CRITICAL / HIGH / MEDIUM / LOW with file:line references

### Phase 2: Code Quality Assessment

#### 2.1 Architecture & Design (0-5.0 score)

**Analyze:**
- God classes: Find files >1000 lines using: `find . -name "*.php" -not -path "*/vendor/*" -not -path "*/tests/*" -exec wc -l {} \;`
- Code organization: SOLID principles, separation of concerns, design patterns
- Coupling: Tight dependencies between classes/modules
- Cohesion: Single responsibility principle adherence

**Scoring:**
- **4.5-5.0 (Excellent):** Clean architecture, SOLID principles, well-organized
- **3.5-4.4 (Good):** Solid structure, minor improvements needed
- **2.5-3.4 (Fair):** Functional but needs refactoring, some anti-patterns
- **1.5-2.4 (Poor):** Spaghetti code, tight coupling, hard to modify
- **0.0-1.4 (Critical):** Chaotic structure, no clear patterns

#### 2.2 Code Maintainability (0-5.0 score)

**Identify:**
- Global variables: Count `global $` usage with Grep
- Large functions: Functions >100 lines
- SQL queries: Count direct `$wpdb` calls
- TODOs: Count `TODO|FIXME|HACK|XXX` comments (exclude vendor/tests)
- Error handling: Consistency and completeness
- Code duplication: Repeated logic patterns

**Scoring:**
- **4.5-5.0 (Excellent):** Clean code, self-documenting, easy to understand
- **3.5-4.4 (Good):** Readable, consistent style, minor issues
- **2.5-3.4 (Fair):** Understandable with effort, inconsistent patterns
- **1.5-2.4 (Poor):** Hard to read, cryptic logic, poor naming
- **0.0-1.4 (Critical):** Unmaintainable, impossible to understand

#### 2.3 Documentation (0-5.0 score)

**Check:**
- PHPDoc blocks on classes and methods
- Inline comments for complex logic
- README.md existence and quality
- Code examples and usage documentation

**Scoring:**
- **4.5-5.0 (Excellent):** Comprehensive docs, well-commented code, examples
- **3.5-4.4 (Good):** Good coverage, key areas documented
- **2.5-3.4 (Fair):** Basic documentation, some gaps
- **1.5-2.4 (Poor):** Minimal docs, mostly undocumented
- **0.0-1.4 (Critical):** No documentation at all

**Calculate Code Quality Score:** `(Architecture + Maintainability + Documentation) ÷ 3`

### Phase 3: Dependencies Analysis

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

### Phase 4: Performance & Architecture

**Analyze:**
- N+1 query patterns (loops with database queries)
- Caching implementation (transients, object cache)
- WordPress coding standards compliance
- PSR-12 compliance (preferred standard)
- Test suite existence (PHPUnit, Codeception, WP_UnitTestCase)

### Phase 5: PHPMetrics Analysis

**Run PHPMetrics** (after manual analysis):
```bash
phpmetrics --report-html=metrics --exclude=vendor,lib/vendor,tests,dist,dev-workspace .
```

**Extract these metrics from the report:**
- Violations count
- Lines of Code (LOC)
- Classes count
- Average Cyclomatic Complexity
- Average Bugs by Class

### Phase 6: Fix Cost Estimation

**Estimate developer days needed (1-10+):**
- **1-3 days:** Minor fixes (small bugs, simple updates, documentation)
- **4-6 days:** Moderate work (security patches, dependency updates, refactoring small modules)
- **7-9 days:** Major overhaul (significant refactoring, multiple security issues, architectural changes)
- **10+ days:** Critical rebuild (fundamental architecture changes, complete rewrite needed)

## Recommendation Logic

**Apply these decision rules:**
- **ACQUIRE:** Security ≥4.0 AND Code Quality ≥3.5 AND Fix Days ≤6
- **CAUTION:** Security 2.5-3.9 OR Code Quality 2.5-3.4 OR Fix Days 7-9
- **REJECT:** Security <2.5 OR Code Quality <2.5 OR Fix Days ≥10

## Output Format

Generate TWO types of outputs:

### Output 1: AUDIT_REPORT.md (Primary Report)

Create a file named `AUDIT_REPORT.md` in the plugin root with this exact structure:

```markdown
# [PLUGIN_NAME] Security & Code Quality Audit

## SPREADSHEET DATA (Copy and paste into spreadsheet tab)

**IMPORTANT**: Use actual TAB characters between columns, not spaces. Format for direct paste into spreadsheet.

```
Metric	Score/Value	Notes
Security Score	[X.X]	[Brief: Main security findings, max 2 sentences]
Architecture & Design	[X.X]	[Brief: Code structure assessment, max 2 sentences]
Code Maintainability	[X.X]	[Brief: Readability & maintenance, max 2 sentences]
Documentation	[X.X]	[Brief: Docs quality, max 2 sentences]
Code Quality Score	[X.X]	[Auto-calculated: (Architecture + Maintainability + Documentation) ÷ 3]
Violations	[X]	[From phpmetrics report]
Lines of Code	[X]	[From phpmetrics report]
Classes	[X]	[From phpmetrics report]
Avg Cyclomatic Complexity	[X.X]	[From phpmetrics report]
Avg Bugs by Class	[X.X]	[From phpmetrics report]
Fix Cost (Days)	[X]	[Brief: What needs fixing, max 2 sentences]
Recommendation	[ACQUIRE/CAUTION/REJECT]	[One-line rationale based on decision rules]
```

**Example of properly formatted data:**
```
Metric	Score/Value	Notes
Security Score	4.2	Minor CSRF issues in admin forms. No critical vulnerabilities found.
Architecture & Design	3.8	Generally well-structured MVC pattern. Some tight coupling in payment modules.
Code Maintainability	3.5	Readable code with consistent naming. Large god classes need refactoring.
Documentation	2.8	Basic PHPDoc present. Missing inline comments in complex logic sections.
Code Quality Score	3.4	Average of Architecture (3.8), Maintainability (3.5), and Documentation (2.8).
Violations	23	Moderate code violations, mainly complexity warnings in core classes.
Lines of Code	15420	Reasonable size for plugin functionality. Well-distributed across modules.
Classes	87	Good separation of concerns. Some classes could be further decomposed.
Avg Cyclomatic Complexity	5.2	Acceptable complexity. Few methods exceed threshold of 10.
Avg Bugs by Class	0.8	Low predicted bug count. Indicates stable codebase with good practices.
Fix Cost (Days)	5	CSRF protection (2 days), refactor payment module (2 days), documentation (1 day).
Recommendation	CAUTION	Code Quality 3.4 is below ideal but acceptable. Security is solid at 4.2.
```

## 1. Security Assessment (Brief)
**Score: X.X/5.0**

🔴 **Critical Issues:**
- [Issue description] (file:line)

🟡 **Concerns:**
- [Issue description] (file:line)

🟢 **Strengths:**
- [Positive finding]

## 2. Code Quality Breakdown
**Overall: X.X/5.0**

| Sub-Metric | Score | Key Finding |
|------------|-------|-------------|
| Architecture & Design | X.X/5.0 | [One-line summary] |
| Code Maintainability | X.X/5.0 | [One-line summary] |
| Documentation | X.X/5.0 | [One-line summary] |

**Major Issues:**
- God classes: [List files >1000 lines if any]
- Global variables: [Count]
- TODO/FIXME comments: [Count]
- Large functions: [Notable examples >100 lines]

## 3. Dependencies
**Critical Issues:** [List outdated/risky packages]
**Immediate Updates Required:** [What needs updating]
**PHP Version:** [Current requirement]
**WordPress Version:** [Current requirement]

## 4. Fix Cost Breakdown
**Estimated: X days**

Priority fixes:
1. [Issue] - [X days] - [Brief description]
2. [Issue] - [X days] - [Brief description]
3. [Issue] - [X days] - [Brief description]

## 5. Final Recommendation: [ACQUIRE/CAUTION/REJECT]

**Rationale:** [2-3 sentence justification based on decision rules]

**Key Decision Factors:**
- [Brief factor 1]
- [Brief factor 2]
- [Brief factor 3]

**Answer:** Is this code maintainable by a new team? [Yes/No with brief explanation]
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

```markdown
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
2. ✅ Code Quality Score is calculated: (Architecture + Maintainability + Documentation) ÷ 3
3. ✅ Spreadsheet data uses actual TAB characters (not spaces) between columns
4. ✅ All file:line references are accurate and verifiable
5. ✅ Security vulnerabilities are exploitable, not just theoretical
6. ✅ Code path to vulnerability is reachable by users
7. ✅ WordPress core sanitization doesn't already prevent exploitation
8. ✅ CVSS scores accurately reflect impact
9. ✅ Fix cost estimates are realistic based on complexity
10. ✅ Recommendation follows decision rules strictly

### False Positive Avoidance
- Check if data is sanitized before the vulnerable function
- Verify capability checks aren't performed earlier in the call stack
- Confirm nonces aren't verified in parent functions
- Check for output escaping before assuming XSS
- Trace data flow through multiple files if necessary

### Reporting Standards
- Be objective and concise: 1-2 sentences max in spreadsheet Notes column
- Provide code snippets as evidence with file:line references
- Brief descriptions in main report, detailed in individual GHSA files
- Answer the key question: "Is this code maintainable by a new team?"

## Execution Workflow

Follow this sequence for comprehensive audit:

1. **Start with searches** (use Grep tool):
   - SQL injection patterns
   - XSS patterns
   - CSRF patterns
   - Dangerous functions
   - TODO/FIXME comments
   - Global variable usage

2. **Analyze architecture** (use Read/Glob tools):
   - Find large files (>1000 lines)
   - Identify god classes
   - Check dependency files (composer.json)
   - Review main plugin structure

3. **Run PHPMetrics** (use Shell tool):
   ```bash
   phpmetrics --report-html=metrics --exclude=vendor,lib/vendor,tests,dist,dev-workspace .
   ```
   - Extract metrics from generated report

4. **Calculate scores:**
   - Security: 0-5.0 (based on findings)
   - Architecture & Design: 0-5.0
   - Code Maintainability: 0-5.0
   - Documentation: 0-5.0
   - Code Quality: (Architecture + Maintainability + Documentation) ÷ 3

5. **Estimate fix cost** (1-10+ days)

6. **Apply recommendation logic:**
   - ACQUIRE: Security ≥4.0 AND Code Quality ≥3.5 AND Fix Days ≤6
   - CAUTION: Security 2.5-3.9 OR Code Quality 2.5-3.4 OR Fix Days 7-9
   - REJECT: Security <2.5 OR Code Quality <2.5 OR Fix Days ≥10

7. **Generate outputs:**
   - Create AUDIT_REPORT.md with spreadsheet data
   - Create individual GHSA files for each vulnerability (if any)

## Interaction Protocol

- Request additional files if needed to trace data flow
- Explain severity reasoning if unclear
- Highlight systemic issues when patterns emerge
- Provide actionable remediation, not just problem identification
- When uncertain about exploitability, report with caveats
- If PHPMetrics fails to run, estimate metrics from manual analysis
- Always complete the full audit workflow before generating final report

