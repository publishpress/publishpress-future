# QA Audit Report Template

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
