---
name: prepare-changelog-release
description: Prep CHANGELOG.md for release — version + date on [Unreleased]. Use for release changelog prep/finalize.
---

# Prepare Changelog for Release

**Communication:** Apply `/caveman` mode (full) to all responses and status updates when this skill is active. Drop articles/filler. Fragments OK. Technical terms exact. Code/commits/PR bodies stay normal unless user says otherwise.

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
- Comma after day
- 3-letter month

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

## Month Abbreviations Reference

| Month | Abbreviation |
|-------|--------------|
| January | Jan |
| February | Feb |
| March | Mar |
| April | Apr |
| May | May |
| June | Jun |
| July | Jul |
| August | Aug |
| September | Sep |
| October | Oct |
| November | Nov |
| December | Dec |
