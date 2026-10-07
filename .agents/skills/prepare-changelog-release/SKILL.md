---
name: prepare-changelog-release
description: Prep CHANGELOG.md for release
---

# Prepare Changelog for Release

Update `CHANGELOG.md` unreleased section with current plugin version + date. Follow project changelog format.

## Instructions

1. **Read version** from `post-expirator.php` header `Version` field
2. **Read CHANGELOG.md** — find `[Unreleased]` (~line 4), all bullets until next version
3. **Replace** `[Unreleased]` header with:
   ```
   [VERSION] - DD MMM, YYYY
   ```
   - VERSION = plugin file version
   - DD = 2-digit day
   - MMM = 3-letter month (Jan–Dec)
   - YYYY = 4-digit year
4. **Add new `[Unreleased]`** after line 3 (empty):

   ```
   [Unreleased]

   ```

## Format Requirements

- One blank line between sections
- Keep existing bullet format
- `[VERSION] - DD MMM, YYYY` (e.g. `[1.0.0] - 02 Feb, 2026`)
- Comma after day; 3-letter month

## Example Transformation

**Before:**
```markdown
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

[Unreleased]

- Added: New feature X
- Fixed: Bug in feature Y

[1.0.0] - 02 Feb, 2026
```

**After (assuming version 1.1.0 and date 05 Feb, 2026):**
```markdown
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

[Unreleased]

[1.1.0] - 05 Feb, 2026

- Added: New feature X
- Fixed: Bug in feature Y

[1.0.0] - 02 Feb, 2026
```
