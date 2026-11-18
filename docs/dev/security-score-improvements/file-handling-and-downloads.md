# File Handling and Downloads

Baseline: **4.4** HEALTHY. Evidence: [security-audit-report.md](../security-audit-report.md).

No open file read, upload, or path traversal finding.

## Keep

| Control | Evidence | Done-when |
|---------|----------|-----------|
| Debug log download | `Debug/Controllers/Controller.php:97-127` | Request without `manage_options` or a valid nonce returns 403. Body is `text/plain`. |
| Template include | `Display.class.php:520-524` | `realpath` must stay under `src/Views/`. A path outside that directory returns before `include`. |
| Backup import | `BackupRestApi.php:333-351` | Body is `json_decode` to an array. `manage_options` is required. No `unserialize` of the upload. |

## Hardening

| Item | Evidence | Done-when |
|------|----------|-----------|
| Prefix check includes a directory separator | `Display.class.php:523` uses `strpos($templatePath, $allowedBasePath) !== 0` | Compare against `$allowedBasePath . DIRECTORY_SEPARATOR` so a sibling directory named `Views-extra` cannot match. |

This hardening item is theoretical. The template path is filtered PHP, not a public parameter.
