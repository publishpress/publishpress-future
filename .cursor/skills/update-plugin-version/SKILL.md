---
name: update-plugin-version
description: Bump plugin version via composer set:version
---

# Update Plugin Version

Bump version consistently across required PublishPress Future files.

## Recommended Method

```bash
composer set:version X.Y.Z
```

Updates all required files automatically. Script: `dev-workspace/scripts/plugin-bump-version.php`

## Version Format

[Semantic Versioning](https://semver.org/): `MAJOR.MINOR.PATCH[-PRERELEASE]`

- **MAJOR**: Breaking (e.g. 4.0.0)
- **MINOR**: Features, backward compatible (e.g. 4.1.0)
- **PATCH**: Fixes, backward compatible (e.g. 4.1.1)
- **Pre-release** (optional): alpha, beta, rc (e.g. 4.1.0-beta.1)
- No `v` prefix; regex: `^\d+\.\d+\.\d+(-(alpha|beta|rc)\.[0-9]+)?$`

## Files to Update

Script touches:

1. **post-expirator.php** (line ~8) — `* Version: X.Y.Z`
2. **post-expirator.php** (line ~60) — `define('PUBLISHPRESS_FUTURE_VERSION', 'X.Y.Z');`
3. **readme.txt** (line ~10) — **stable only** — `Stable tag: X.Y.Z`. Pre-release: NOT written to readme.txt

## Example

```bash
composer set:version 4.10.0
```

## Important Notes

- Prefer `composer set:version` — automated, fewer mistakes
- Stable updates readme; pre-release does not
- Version identical in all updated files
- Do not change other file content
- CHANGELOG.md separate (not this skill)
- After bump: CHANGELOG release notes, git tag, `composer build`
