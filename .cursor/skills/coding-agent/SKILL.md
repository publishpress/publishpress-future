---
name: coding-agent
description: Launch coding agent for PublishPress Future
---
# Coding Agent Skill

**Communication:** Apply `/caveman` mode (full) to all responses and status updates when this skill is active. Drop articles/filler. Fragments OK. Technical terms exact. Code/commits/PR bodies stay normal unless user says otherwise.

## Purpose

Implementation agent for PublishPress Future — follow project conventions, ship working code.

## When to Use

Features, bugs, refactors, functionality changes, standards updates, new/extended modules.

## How to Use

```
@coding-agent implement [your instructions here]
```

Examples:
```
@coding-agent implement a new expiration action to send email notifications
@coding-agent fix scheduled actions not triggering on multisite
@coding-agent add validation for workflow step configurations
@coding-agent implement a REST endpoint for exporting scheduled actions as CSV
```

## Mandatory First Step

**Read `.cursor/skills/code-style/SKILL.md` in full** before writing code. It is the source of truth for:

- Project overview, stack, PSR-12, architecture
- Directory structure and module patterns
- WordPress guidelines (Facades, hooks, REST, i18n)
- Composer commands (test, check, build, env)
- Testing organization and code review checklist

Do not duplicate those rules here — follow code-style.

## Implementation Workflow

1. **Understand** — read task + existing code in target module
2. **Plan** — identify layer (Core/Framework/Modules), files to touch, tests needed
3. **Implement** — match nearby patterns; copy shape from reference files below
4. **Verify** — `composer check:cs` → `fix:cs`/`fix:php` → `check:stan` → `check:lint` → relevant `composer test` → `build:js` if JSX → `build:lang` if strings
5. **Report** — summary, files changed, testing steps

## Task Recipes

Copy existing code shape — do not invent new patterns.

| Task | Reference files | Also update |
|------|-----------------|-------------|
| New module | `src/Modules/Settings/Module.php`, `HooksAbstract.php` | `services.php`, Unit + Integration tests |
| Controller | `src/Modules/Expirator/Controllers/BlockEditorController.php` | `HooksAbstract`, module `Module.php` if needed |
| REST endpoint | `src/Modules/Workflows/Rest/RestApiV1.php` | `/publishpress-future/v1/` prefix, `permission_callback`, Integration test |
| Expiration action | `src/Modules/Expirator/ExpirationActions/ChangePostStatus.php` | `ExpirationActionsAbstract`, `ExpirationActionsModel`, i18n label |
| DB schema | `src/Modules/Expirator/DBTableSchemas/ActionArgsSchema.php` | `Migrations/V{version}{Description}.php`, register in module |
| DI registration | `services.php`, `ServicesAbstract` | constructor injection; `InitializableInterface` for auto-init |
| JSX UI | `assets/jsx/workflow-editor/` | functional components, `__('text', 'post-expirator')` |

### Container Registration

`ServicesAbstract` constant → entry in `services.php` → constructor injection → `InitializableInterface` for controllers/services that hook on init.

### Key Facades

`src/Framework/WordPress/Facade/`: HooksFacade, DatabaseFacade, OptionsFacade, CronFacade (prefer Action Scheduler), EmailFacade, DateTimeFacade, UsersFacade, SiteFacade, NoticeFacade, RequestFacade, SanitizationFacade, ErrorFacade.

**Never use global WordPress functions in business logic.**

## Pre-Completion Checklists

From code-style — confirm before done:

**Security:** nonces, caps, sanitize (Facades), escape output, prepared SQL, `ABSPATH` guard, no raw superglobals, REST permissions.

**Quality:** PHP 7.4 type hints, file + method PHPDoc with `@since`, DI not `new`, Abstract constants, tests for non-trivial logic.

**Architecture:** correct layer, `ModuleInterface`, `DBTableSchemaInterface`, `V{version}{description}` migrations, `services.php` registration.

## Output Format

1. Summary — what and why
2. Files changed
3. Testing steps
4. Notes — follow-ups if any

## Example

**User:** `@coding-agent implement a new expiration action to send email notifications`

**Agent:** read code-style → read `ExpirationActions/` → copy `ChangePostStatus.php` shape → implement `ExpirationActionInterface` → add constant in `ExpirationActionsAbstract` → register in `ExpirationActionsModel` → PHPDoc `@since` → i18n label → Integration test → run checks.

## Related Files

`post-expirator.php` | `services.php` | `.phpcs.xml` | `codeception.yml` | `tests/` | `assets/` | `languages/` | `.cursor/rules/`
