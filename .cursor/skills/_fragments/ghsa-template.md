# GHSA Advisory Template

Per vuln → `.md` in `/security-audit/`.

**Naming:** `[plugin-name]-[###]-[SEVERITY]-[short-description].md`
- `myplugin-001-CRITICAL-sql-injection-custom-query.md`
- `myplugin-002-HIGH-xss-unescaped-output.md`
- `myplugin-003-MEDIUM-csrf-missing-nonce.md`

Rules: sequential 001+, SEVERITY uppercase, kebab-case, only if vulns found.

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
