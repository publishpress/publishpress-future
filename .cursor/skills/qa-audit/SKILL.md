---
name: wp-plugin-security-auditor
description: WordPress plugin security and code quality audit
---

# WP Plugin Security & Code Quality Auditor

Use these instructions when the user requests a security audit or code quality review of a WordPress plugin. This generates both detailed security findings (GHSA format) and spreadsheet-compatible metrics for tracking plugin health.

## Mission

Conduct a comprehensive audit covering security, code quality, and maintainability. Generate both:

1. **Spreadsheet metrics** - Tab-separated data for Plugin Health Spreadsheet
2. **Security advisories** - Detailed vulnerability reports in GHSA format (when issues found)

The output is formatted for direct paste into tracking spreadsheets with scores aligned to plugin health criteria.

## Directories to Exclude

NEVER analyze code in these directories (applies to all searches, greps, and phpmetrics):

- `/vendor/`
- `/lib/vendor/`
- `/dist/`
- `/.git/` and all hidden folders (`.*`)
- `/dev-workspace-cache/`
- `/dev-workspace/`
- `/node_modules/`
- `/tests/`

## Audit Methodology

### Phase 1: Security Assessment (CRITICAL PRIORITY)

> **Delegate to the security-audit skill.** Read and follow `.cursor/skills/security-audit/SKILL.md` to conduct the full security assessment, produce the GHSA advisory files, and obtain the Security Score. Bring the resulting Security Score and any critical findings back into this report.

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

## Recommendation Logic

**Apply these decision rules:**

- **HEALTHY:** Security ≥4.0 AND Code Quality ≥3.5
- **NEEDS-WORK:** Security 2.5-3.9 OR Code Quality 2.5-3.4
- **CRITICAL:** Security <2.5 OR Code Quality <2.5

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
      Recommendation	[HEALTHY/NEEDS-WORK/CRITICAL]	[One-line rationale based on decision rules]
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
      Recommendation	NEEDS-WORK	Code Quality 3.4 is below ideal but acceptable. Security is solid at 4.2.
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

## 4. Final Recommendation: [HEALTHY/NEEDS-WORK/CRITICAL]

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
4. ✅ Security Score comes from the security-audit skill output
5. ✅ Recommendation follows decision rules strictly

### Reporting Standards

- Be objective and concise: 1-2 sentences max in spreadsheet Notes column
- Provide code snippets as evidence with file:line references
- Brief descriptions in main report, detailed in individual GHSA files

## Execution Workflow

Follow this sequence for comprehensive audit:

1. **Run security assessment** (delegate to `.cursor/skills/security-audit/SKILL.md`):

   - Follow that skill in full to produce the Security Score and GHSA advisory files
   - Bring the Security Score and any critical findings back into this report

2. **Search for code quality signals** (use Grep tool):

   - TODO/FIXME comments
   - Global variable usage

3. **Analyze architecture** (use Read/Glob tools):

   - Find large files (>1000 lines)
   - Identify god classes
   - Check dependency files (composer.json)
   - Review main plugin structure

4. **Run PHPMetrics** (use Shell tool):

   ```bash
   phpmetrics --report-html=metrics --exclude=vendor,lib/vendor,tests,dist,dev-workspace .
   ```

   - Extract metrics from generated report

5. **Calculate scores:**

   - Security: from security-audit skill output
   - Architecture & Design: 0-5.0
   - Code Maintainability: 0-5.0
   - Documentation: 0-5.0
   - Code Quality: (Architecture + Maintainability + Documentation) ÷ 3

6. **Apply recommendation logic:**

   - HEALTHY: Security ≥4.0 AND Code Quality ≥3.5
   - NEEDS-WORK: Security 2.5-3.9 OR Code Quality 2.5-3.4
   - CRITICAL: Security <2.5 OR Code Quality <2.5

7. **Generate outputs:**

   - Create AUDIT_REPORT.md with spreadsheet data (GHSA advisory files were already created by the security-audit skill)

## Interaction Protocol

- Request additional files if needed to trace data flow
- Explain severity reasoning if unclear
- Highlight systemic issues when patterns emerge
- Provide actionable remediation, not just problem identification
- When uncertain about exploitability, report with caveats
- If PHPMetrics fails to run, estimate metrics from manual analysis
- Always complete the full audit workflow before generating final report
