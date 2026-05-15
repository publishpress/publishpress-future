---
name: update-plugin-version
description: Bump plugin version (main file, readme.txt) for PublishPress Future release. Use for version bump / release prep.
---

# Update Plugin Version

**Communication:** Apply `/caveman` mode (full) to all responses and status updates when this skill is active. Drop articles/filler. Fragments OK. Technical terms exact. Code/commits/PR bodies stay normal unless user says otherwise.

Bump version consistently across required PublishPress Future files.

## Recommended Method

```bash
composer set:version X.Y.Z
```

Updates all required files automatically.

## Version Format

[Semantic Versioning](https://semver.org/): `MAJOR.MINOR.PATCH[-PRERELEASE]`

### Stable Versions
- **MAJOR**: Breaking (e.g. 4.0.0)
- **MINOR**: Features, backward compatible (e.g. 4.1.0)
- **PATCH**: Fixes, backward compatible (e.g. 4.1.1)

### Pre-release (optional)
- **alpha**: Early dev (e.g. 4.1.0-alpha.1)
- **beta**: Feature complete, testing (e.g. 4.1.0-beta.1)
- **rc**: Release candidate (e.g. 4.1.0-rc.1)

## Files to Update

Script touches:

1. **post-expirator.php** (line ~8) — `* Version: X.Y.Z` (e.g. `* Version: 4.9.4`)
2. **post-expirator.php** (line ~60) — `define('PUBLISHPRESS_FUTURE_VERSION', 'X.Y.Z');`
3. **readme.txt** (line ~10) — **stable only** — `Stable tag: X.Y.Z`. Pre-release: NOT written to readme.txt

## Update Process

### Composer (recommended)

```bash
composer set:version 4.10.0
composer set:version 4.10.0-beta.1
```

### Manual

1. **Validate format**
   - Stable: `X.Y.Z`
   - Pre-release: `X.Y.Z-TYPE.N` (alpha/beta/rc)
   - No `v` prefix
   - Regex: `^\d+\.\d+\.\d+(-(alpha|beta|rc)\.[0-9]+)?$`
2. **Update files** — constant ~line 60, header ~line 8, readme stable tag ~line 10 (stable only). Exact formats above. Keep spacing.
3. **Verify** — same version everywhere; readme updated for stable only; formatting intact

## Examples

### Stable (4.10.0)

**Command:**
```bash
composer set:version 4.10.0
```

**post-expirator.php (line ~8):**
```php
 * Version: 4.10.0
```

**post-expirator.php (line ~60):**
```php
define('PUBLISHPRESS_FUTURE_VERSION', '4.10.0');
```

**readme.txt (line ~10):**
```
Stable tag: 4.10.0
```

### Pre-release (4.10.0-beta.1)

**Command:**
```bash
composer set:version 4.10.0-beta.1
```

**post-expirator.php (line ~8):**
```php
 * Version: 4.10.0-beta.1
```

**post-expirator.php (line ~60):**
```php
define('PUBLISHPRESS_FUTURE_VERSION', '4.10.0-beta.1');
```

**readme.txt:**
```
NOT UPDATED (remains at previous stable version)
```

## Important Notes

- Prefer `composer set:version X.Y.Z` — automated, fewer mistakes
- Script: `dev-workspace/scripts/plugin-bump-version.php`
- Stable updates readme; pre-release does not
- Version identical in all updated files
- Do not change other file content
- CHANGELOG.md separate (not this skill)
- After bump: CHANGELOG release notes, git tag, `composer build`
