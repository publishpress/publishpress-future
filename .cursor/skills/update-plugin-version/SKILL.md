---
name: update-plugin-version
description: Updates plugin version across all required files (main plugin file, readme.txt) when releasing a new version. Use when the user asks to update the version, bump version, prepare a release, or mentions version numbers for the PublishPress Future plugin.
---

# Update Plugin Version

Updates the plugin version consistently across all required files for the PublishPress Future plugin.

## Recommended Method

Use the built-in Composer command:

```bash
composer set:version X.Y.Z
```

This command automatically updates all necessary files.

## Version Format

Follow [Semantic Versioning](https://semver.org/): `MAJOR.MINOR.PATCH[-PRERELEASE]`

### Stable Versions
- **MAJOR**: Breaking changes (e.g., 4.0.0)
- **MINOR**: New features, backward compatible (e.g., 4.1.0)
- **PATCH**: Bug fixes, backward compatible (e.g., 4.1.1)

### Pre-release Versions (optional)
- **alpha**: Early development (e.g., 4.1.0-alpha.1)
- **beta**: Feature complete, testing (e.g., 4.1.0-beta.1)
- **rc**: Release candidate (e.g., 4.1.0-rc.1)

## Files to Update

The version update script modifies these files:

1. **post-expirator.php** (line ~8)
   - Format: `* Version: X.Y.Z`
   - Example: `* Version: 4.9.4`

2. **post-expirator.php** (line ~60)
   - Format: `define('PUBLISHPRESS_FUTURE_VERSION', 'X.Y.Z');`
   - Example: `define('PUBLISHPRESS_FUTURE_VERSION', '4.9.4');`

3. **readme.txt** (line ~10) - **Only for stable versions**
   - Format: `Stable tag: X.Y.Z`
   - Example: `Stable tag: 4.9.4`
   - Note: Pre-release versions are NOT written to readme.txt

## Update Process

### Using Composer Command (Recommended)

```bash
# Update to stable version
composer set:version 4.10.0

# Update to pre-release version
composer set:version 4.10.0-beta.1
```

### Manual Update Process

If not using the composer command:

1. **Validate version format**
   - Stable: X.Y.Z (e.g., 4.9.4, 5.0.0)
   - Pre-release: X.Y.Z-TYPE.N (e.g., 4.10.0-alpha.1, 4.10.0-beta.2, 4.10.0-rc.1)
   - No 'v' prefix
   - Must match regex: `^\d+\.\d+\.\d+(-(alpha|beta|rc)\.[0-9]+)?$`

2. **Update all required files**
   - Update version constant in post-expirator.php (around line 60)
   - Update version header in post-expirator.php (around line 8)
   - Update stable tag in readme.txt (around line 10) - **only for stable versions**
   - Use the EXACT format shown above for each file
   - Maintain spacing and formatting

3. **Verify changes**
   - Check that all updated files have the same version
   - For stable versions, ensure readme.txt was updated
   - For pre-release versions, ensure readme.txt was NOT updated
   - Ensure no formatting was broken

## Examples

### Stable Version Update (4.10.0)

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

### Pre-release Version Update (4.10.0-beta.1)

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

- **Use the composer command** (`composer set:version X.Y.Z`) - it's automated and prevents mistakes
- The version script is located at `dev-workspace/scripts/plugin-bump-version.php`
- Stable versions update readme.txt; pre-release versions do NOT
- Version must be identical in all updated files
- Do not modify any other content in these files
- The CHANGELOG.md is managed separately (not updated by this skill)
- After updating version, consider:
  - Updating CHANGELOG.md with release notes
  - Creating a git tag for the release
  - Building the plugin package (`composer build`)
