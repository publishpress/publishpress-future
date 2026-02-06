---
name: prepare-changelog-release
description: Prepares the CHANGELOG.md file for release by updating the [Unreleased] section with the current plugin version and date. Use when the user asks to prepare changelog for release, update changelog for release, or finalize changelog entries.
---

# Prepare Changelog for Release

Updates the unreleased section of CHANGELOG.md with the current plugin version and date, following the project's changelog format.

## Instructions

When preparing the changelog for release:

1. **Read the current plugin version** from `publishpress-hub-statistics.php` (Version field in the file header)

2. **Read CHANGELOG.md** to identify:
   - The [Unreleased] section (typically starts at line 4)
   - All entries under [Unreleased] (bullet points between [Unreleased] and the next version)

3. **Update the changelog** by replacing the [Unreleased] header with:
   ```
   [VERSION] - DD MMM, YYYY
   ```
   Where:
   - VERSION is the current version from the plugin file
   - DD is the day (2 digits)
   - MMM is the month (3-letter abbreviation: Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, Oct, Nov, Dec)
   - YYYY is the 4-digit year

4. **Add a new [Unreleased] section** at the top (after line 3) with empty content:
   ```
   [Unreleased]

   ```

## Format Requirements

- Use exactly one blank line between sections
- Maintain the existing bullet point format for entries
- Follow the format: `[VERSION] - DD MMM, YYYY` (e.g., `[1.0.0] - 02 Feb, 2026`)
- Add comma after day in the date
- Use 3-letter month abbreviation

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
