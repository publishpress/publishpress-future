---
name: verify-wporg-release
description: >-
  Verify PublishPress Future releases using shared dev-workspace gates:
  composer check:release (pre) and composer check:wporg (post). Use after
  releasing to wordpress.org, before SVN deploy, or when checking whether a
  free plugin version is live/working on WordPress.org.
---

# Verify WordPress.org Release

Uses **publishpress/dev-workspace** scripts (requires **>= 1.8.0**).

## Commands

```bash
# Pre-release (stable only); fail if already on wp.org
composer check:release
composer check:release -- 4.10.5
composer check:release -- --allow-published   # rare re-tag / emergency

# Post-release: live ZIP, checksums, plugin page, update-check
composer check:wporg
composer check:wporg -- 4.10.5
```

`composer build` runs `check:release-if-stable` on the project root before packing (skips beta/rc/alpha).

## When

- **Before** GitHub release / SVN deploy → `check:release`
- **After** wordpress.org deploy → `check:wporg`
- User asks “is X.Y.Z live?” / “check wp.org”

## Agent workflow

1. Prefer `composer check:release` / `composer check:wporg` from the plugin root (runs via dev-workspace container).
2. If vendor package is older than 1.8.0, tell user to bump `publishpress/dev-workspace` and `composer update`.
3. Report with the script output table; map exit codes:
   - `0` + only PASS → healthy
   - `0` + WARN on update-check → healthy (Protect the Shire cooldown)
   - `1` → blocked

## What must pass

### check:release (pre)

Runs against a plugin folder (`PATH`, default project root).

| Check | Expect |
|-------|--------|
| Version | Stable `x.y.z` only |
| Header / constant / Stable tag | All = VERSION |
| CHANGELOG `[VERSION]` | If `CHANGELOG.md` exists must match |
| package.json `version` | Optional; if present must = VERSION |
| wordpress.org | VERSION **not** already published (unless `--allow-published`) |

### check:wporg (post)

| Check | Expect |
|-------|--------|
| Live ZIP | HTTP 200; Version + constant + Stable tag = VERSION |
| Checksums | HTTP 200; JSON version = VERSION |
| Plugin page | Shows VERSION |
| Update-check | Offers VERSION, or WARN during ~6h cooldown |

**Never call healthy** if live ZIP Version ≠ tag (e.g. `4.10.4-beta.2` inside `4.10.4`).

## Report format

```markdown
## Release check — VERSION

**Command:** check:release | check:wporg

| Check | Status | Detail |
|-------|--------|--------|
| … | PASS/WARN/FAIL | … |

**Verdict:** healthy | blocked | healthy (update cooldown)
**Next:** …
```

## Related

- Scripts: `vendor/publishpress/dev-workspace/scripts/check-release.sh`, `check-wporg.sh`
- Version bump: `.cursor/skills/update-plugin-version/SKILL.md`
- Changelog: `.cursor/skills/prepare-changelog-release/SKILL.md`
- Checklist: `.github/ISSUE_TEMPLATE/release-free-plugin.md`
