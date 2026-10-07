# Security Audit Report Template

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
