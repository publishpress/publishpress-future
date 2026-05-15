---
name: wp-plugin-security-auditor
description: WP plugin security + code quality audit
---

# WP Plugin Security & Code Quality Auditor

**Communication:** Apply `/caveman` mode (full) to all responses and status updates when this skill is active. Drop articles/filler. Fragments OK. Technical terms exact. Code/commits/PR bodies stay normal unless user says otherwise.

Activate on security audit or code quality review request. Output GHSA findings + spreadsheet metrics.

## Mission

Audit security, code quality, maintainability. Produce:
1. **Spreadsheet metrics** — tab-separated for Plugin Health Spreadsheet
2. **Security advisories** — GHSA vuln files (if issues found)

## Exclude Dirs

NEVER analyze (grep/search/phpmetrics):
`/vendor/` `/lib/vendor/` `/dist/` `/.git/` `.*` `/dev-workspace-cache/` `/dev-workspace/` `/node_modules/` `/tests/`

## Audit Methodology

### Phase 1: Security Assessment (CRITICAL)

Delegate to security-audit skill. Read `.cursor/skills/security-audit/SKILL.md`. GHSA files + Security Score. Return score + critical findings.

### Phase 2: Code Quality

#### 2.1 Architecture & Design (0-5.0)

- God classes/files: `find . -name "*.php" -not -path "*/vendor/*" -not -path "*/tests/*" -exec wc -l {} \;` (>700 lines — legacy function-only files too)
- SOLID, separation of concerns, patterns
- Coupling / cohesion / SRP

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | Clean arch, SOLID, well-organized |
| 3.5-4.4 | Good | Solid structure, minor improvements |
| 2.5-3.4 | Fair | Functional, needs refactor, some anti-patterns |
| 1.5-2.4 | Poor | Spaghetti, tight coupling, hard to modify |
| 0.0-1.4 | Critical | Chaotic, no clear patterns |

#### 2.2 Code Maintainability (0-5.0)

- Globals: Grep `global $`
- Large fns: >100 lines
- SQL: direct `$wpdb` count
- TODOs: `TODO|FIXME|HACK|XXX` (exclude vendor/tests)
- Error handling consistency
- Duplication

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | Clean, self-documenting |
| 3.5-4.4 | Good | Readable, consistent, minor issues |
| 2.5-3.4 | Fair | Understandable with effort, inconsistent |
| 1.5-2.4 | Poor | Hard to read, cryptic logic |
| 0.0-1.4 | Critical | Unmaintainable |

#### 2.3 Documentation (0-5.0)

- PHPDoc classes/methods
- Inline comments on complex logic
- README quality + examples

| Score | Grade | Criteria |
|-------|-------|----------|
| 4.5-5.0 | Excellent | Comprehensive, examples |
| 3.5-4.4 | Good | Good coverage |
| 2.5-3.4 | Fair | Basic, gaps |
| 1.5-2.4 | Poor | Minimal |
| 0.0-1.4 | Critical | None |

**Code Quality Score:** `(Architecture + Maintainability + Documentation) ÷ 3`

### Phase 3: Dependencies

composer.json:
- Outdated (3+ years = HIGH RISK)
- Stripe (v13+), PayPal
- Unmaintained (2+ years no updates)
- PHP min 7.4, WP version req

Payment (if applicable): Stripe SDK/API/PCI/webhooks; PayPal IPN/webhook; API keys in wp_options/constants not plaintext; no card data (PCI)

### Phase 4: Performance & Architecture

- N+1 queries
- Caching: transients, object cache
- WP coding standards, PSR-12
- Tests: PHPUnit, Codeception, WP_UnitTestCase

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
- God classes/files: [>700 lines]
- Global vars: [Count]
- TODO/FIXME: [Count]
- Large fns: [>100 lines]

## 3. Dependencies

**Critical Issues:** [Outdated/risky pkgs]
**Immediate Updates:** [What needs updating]
**PHP Version:** [Current req]
**WordPress Version:** [Current req]

## 4. Final Recommendation: [HEALTHY/NEEDS-WORK/CRITICAL]

**Rationale:** [2-3 sentences]

**Key Factors:**
- [Factor 1]
- [Factor 2]
- [Factor 3]
```

### Output 2: GHSA Advisory Files

Per vuln → `.md` in `/security-audit/` (from security-audit skill).

**Naming:** `[plugin-name]-[###]-[SEVERITY]-[short-description].md`

Rules: sequential 001+, SEVERITY uppercase, kebab description, only if vulns found.

## QA Checklist

1. ✅ Scores one decimal (3.7 not 3.70)
2. ✅ Code Quality = (Arch + Maint + Docs) ÷ 3
3. ✅ Spreadsheet: real TAB chars
4. ✅ Security Score from security-audit skill
5. ✅ Recommendation per rules

Reporting: 1-2 sentences Notes column; snippets with file:line; brief report / detail in GHSA.

## Execution Workflow

1. Security — `.cursor/skills/security-audit/SKILL.md`, score + GHSA
2. Code quality signals — Grep TODO/FIXME, globals
3. Architecture — Read/Glob: files >700 lines, composer.json, plugin structure
4. PHPMetrics — violations, LOC, classes, complexity, bugs
5. Scores — Security (skill), Arch/Maint/Docs 0-5.0, Code Quality avg
6. Recommendation logic
7. Generate AUDIT_REPORT.md (GHSA from security-audit)

## Interaction Protocol

- Request files to trace data flow
- Explain severity when unclear
- Highlight systemic patterns
- Actionable remediation
- Uncertain exploitability → report with caveats
- PHPMetrics fails → manual estimate
- Complete workflow before final report
