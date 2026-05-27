---
name: wp-plugin-qa-auditor
description: WP plugin security + code quality audit
---

# WP Plugin Security & Code Quality Auditor

Activate on security audit or code quality review request. Output GHSA findings + spreadsheet metrics.

## Mission

Audit security, code quality, maintainability. Produce:
1. **Spreadsheet metrics** — tab-separated for Plugin Health Spreadsheet
2. **Security advisories** — GHSA vuln files (if issues found)

## Exclude Dirs

Read `.cursor/skills/_fragments/exclude-dirs.md` before grep/search/phpmetrics.

## Audit Methodology

### Phase 1: Security Assessment (CRITICAL)

Delegate to security-audit skill. Read `.cursor/skills/security-audit/SKILL.md`. GHSA files + Security Score. Return score + critical findings.

### Phase 2: Code Quality

#### 2.1 Architecture & Design (0-5.0)

- God classes/files: `find . -name "*.php" -not -path "*/vendor/*" -not -path "*/tests/*" -exec wc -l {} \;` (>700 lines — legacy function-only files too)
- SOLID, separation of concerns, patterns
- Coupling / cohesion / SRP

**Scale:** 5.0 excellent → 4.0 healthy threshold → 2.5 needs-work → <2.5 critical

#### 2.2 Code Maintainability (0-5.0)

- Globals: Grep `global $`
- Large fns: >100 lines
- SQL: direct `$wpdb` count
- TODOs: `TODO|FIXME|HACK|XXX` (exclude vendor/tests)
- Error handling consistency
- Duplication

**Scale:** 5.0 excellent → 4.0 healthy threshold → 2.5 needs-work → <2.5 critical

#### 2.3 Documentation (0-5.0)

- PHPDoc classes/methods
- Inline comments on complex logic
- README quality + examples

**Scale:** 5.0 excellent → 4.0 healthy threshold → 2.5 needs-work → <2.5 critical

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

Read `.cursor/skills/_fragments/audit-report-qa.md` when generating the report.

### Output 2: GHSA Advisory Files

Read `.cursor/skills/_fragments/ghsa-template.md` when generating advisories. Per vuln → `.md` in `/security-audit/` (from security-audit skill).

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
