---
name: wp-plugin-security-auditor
description: WordPress plugin security and code quality audit
---

# WP Plugin Security & Code Quality Auditor

**Communication:** Apply caveman mode (full) to all responses and status updates. Drop articles/filler. Fragments OK. Technical terms exact.

Activate when user requests security audit or code quality review of WP plugin. Produces GHSA findings + spreadsheet-compatible metrics.

## Mission

Comprehensive audit: security, code quality, maintainability. Generate:
1. **Spreadsheet metrics** — tab-separated for Plugin Health Spreadsheet
2. **Security advisories** — GHSA-format vuln files (when issues found)

## Exclude Dirs

NEVER analyze (all searches/greps/phpmetrics):
`/vendor/` `/lib/vendor/` `/dist/` `/.git/` `.*` `/dev-workspace-cache/` `/dev-workspace/` `/node_modules/` `/tests/`

## Audit Methodology

### Phase 1: Security Assessment (CRITICAL PRIORITY)

Delegate to security-audit skill. Read + follow `.cursor/skills/security-audit/SKILL.md`. Produce GHSA advisory files, obtain Security Score. Bring Security Score + critical findings back.

### Phase 2: Code Quality

#### 2.1 Architecture & Design (0-5.0)

Analyze:
- God classes/files: `find . -name "*.php" -not -path "*/vendor/*" -not -path "*/tests/*" -exec wc -l {} \;` (files >700 lines — includes legacy files with functions only, no classes)
- SOLID principles, separation of concerns, design patterns
- Coupling: tight deps between classes/modules
- Cohesion: SRP adherence

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | Clean arch, SOLID, well-organized |
| 3.5-4.4 | Good | Solid structure, minor improvements |
| 2.5-3.4 | Fair | Functional but needs refactor, some anti-patterns |
| 1.5-2.4 | Poor | Spaghetti, tight coupling, hard to modify |
| 0.0-1.4 | Critical | Chaotic, no clear patterns |

#### 2.2 Code Maintainability (0-5.0)

Identify:
- Global vars: Grep `global $`
- Large fns: >100 lines
- SQL queries: direct `$wpdb` calls count
- TODOs: `TODO|FIXME|HACK|XXX` (exclude vendor/tests)
- Error handling: consistency + completeness
- Code duplication: repeated logic

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | Clean, self-documenting, easy to understand |
| 3.5-4.4 | Good | Readable, consistent style, minor issues |
| 2.5-3.4 | Fair | Understandable with effort, inconsistent |
| 1.5-2.4 | Poor | Hard to read, cryptic logic, poor naming |
| 0.0-1.4 | Critical | Unmaintainable |

#### 2.3 Documentation (0-5.0)

Check:
- PHPDoc on classes + methods
- Inline comments for complex logic
- README.md existence + quality
- Code examples

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | Comprehensive, well-commented, examples |
| 3.5-4.4 | Good | Good coverage, key areas documented |
| 2.5-3.4 | Fair | Basic, some gaps |
| 1.5-2.4 | Poor | Minimal, mostly undocumented |
| 0.0-1.4 | Critical | None |

**Code Quality Score:** `(Architecture + Maintainability + Documentation) ÷ 3`

### Phase 3: Dependencies

Check composer.json:
- Outdated pkgs (3+ years = HIGH RISK)
- Stripe (v13+), PayPal
- Unmaintained libs (no updates 2+ years)
- PHP min 7.4, WP version req

Payment security (if applicable):
- Stripe: SDK version, API version, PCI patterns, webhook verification
- PayPal: IPN/webhook, payment verification
- API keys: wp_options or constants, NOT plaintext
- Card data: must NOT exist (PCI violation)

### Phase 4: Performance & Architecture

- N+1 query patterns (loops with DB queries)
- Caching: transients, object cache
- WP coding standards compliance
- PSR-12 compliance
- Test suite: PHPUnit, Codeception, WP_UnitTestCase

### Phase 5: PHPMetrics

```bash
phpmetrics --report-html=metrics --exclude=vendor,lib/vendor,tests,dist,dev-workspace .
```

Extract: Violations, LOC, Classes, Avg Cyclomatic Complexity, Avg Bugs by Class.

## Recommendation Logic

- **HEALTHY:** Security ≥4.0 AND Code Quality ≥3.5
- **NEEDS-WORK:** Security 2.5-3.9 OR Code Quality 2.5-3.4
- **CRITICAL:** Security <2.5 OR Code Quality <2.5

## Output Format

### Output 1: AUDIT_REPORT.md

```markdown
# [PLUGIN_NAME] Security & Code Quality Audit

## SPREADSHEET DATA

**IMPORTANT**: TAB characters between columns, not spaces.

      ```
      Metric	Score/Value	Notes
      Security Score	[X.X]	[Main findings, max 2 sentences]
      Architecture & Design	[X.X]	[Code structure, max 2 sentences]
      Code Maintainability	[X.X]	[Readability, max 2 sentences]
      Documentation	[X.X]	[Docs quality, max 2 sentences]
      Code Quality Score	[X.X]	[(Architecture + Maintainability + Documentation) ÷ 3]
      Violations	[X]	[From phpmetrics]
      Lines of Code	[X]	[From phpmetrics]
      Classes	[X]	[From phpmetrics]
      Avg Cyclomatic Complexity	[X.X]	[From phpmetrics]
      Avg Bugs by Class	[X.X]	[From phpmetrics]
      Recommendation	[HEALTHY/NEEDS-WORK/CRITICAL]	[One-line rationale]
      ```

## 1. Security Assessment (Brief)

**Score: X.X/5.0**

🔴 **Critical Issues:**
- [Issue] (file:line)

🟡 **Concerns:**
- [Issue] (file:line)

🟢 **Strengths:**
- [Positive finding]

## 2. Code Quality Breakdown

**Overall: X.X/5.0**

| Sub-Metric | Score | Key Finding |
|------------|-------|-------------|
| Architecture & Design | X.X/5.0 | [One-line] |
| Code Maintainability | X.X/5.0 | [One-line] |
| Documentation | X.X/5.0 | [One-line] |

**Major Issues:**
- God classes/files: [Files >700 lines, classes or function-only files]
- Global vars: [Count]
- TODO/FIXME: [Count]
- Large fns: [Notable >100 lines]

## 3. Dependencies

**Critical Issues:** [Outdated/risky pkgs]
**Immediate Updates:** [What needs updating]
**PHP Version:** [Current req]
**WordPress Version:** [Current req]

## 4. Final Recommendation: [HEALTHY/NEEDS-WORK/CRITICAL]

**Rationale:** [2-3 sentences based on decision rules]

**Key Factors:**
- [Factor 1]
- [Factor 2]
- [Factor 3]
```

### Output 2: GHSA Advisory Files

Per vuln → separate `.md` in `/security-audit/` (created by security-audit skill).

**Naming:** `[plugin-name]-[###]-[SEVERITY]-[short-description].md`

Rules: sequential from 001, SEVERITY uppercase, description kebab-case, only if vulns found.

## QA Checklist

1. ✅ Scores: one decimal (3.7 not 3 or 3.70)
2. ✅ Code Quality = (Arch + Maint + Docs) ÷ 3
3. ✅ Spreadsheet: actual TAB chars
4. ✅ Security Score from security-audit skill
5. ✅ Recommendation follows decision rules

Reporting: 1-2 sentences in Notes column, code snippets with file:line, brief in main report / detailed in GHSA files.

## Execution Workflow

1. **Security assessment** — delegate to `.cursor/skills/security-audit/SKILL.md`, get Security Score + GHSA files
2. **Code quality signals** (Grep): TODO/FIXME, global var usage
3. **Architecture analysis** (Read/Glob): large files >700 lines (god classes + function-only legacy files), composer.json, main plugin structure
4. **PHPMetrics** (Shell): extract violations, LOC, classes, complexity, bugs
5. **Calculate scores:** Security (from skill), Architecture 0-5.0, Maintainability 0-5.0, Documentation 0-5.0, Code Quality avg
6. **Apply recommendation logic**
7. **Generate:** AUDIT_REPORT.md (GHSA files already created by security-audit skill)

## Interaction Protocol

- Request files if needed to trace data flow
- Explain severity reasoning when unclear
- Highlight systemic patterns
- Actionable remediation, not just problem ID
- Uncertain about exploitability → report with caveats
- PHPMetrics fails → estimate from manual analysis
- Complete full workflow before final report
