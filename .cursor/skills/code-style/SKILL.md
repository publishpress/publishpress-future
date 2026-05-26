---
name: code-style
description: PublishPress Future code style
---
# Code Style Guidelines

**Communication:** Apply `/caveman` mode (full) to all responses and status updates when this skill is active. Drop articles/filler. Fragments OK. Technical terms exact. Code/commits/PR bodies stay normal unless user says otherwise.

## Project Overview

**PublishPress Future** (Post Expirator): schedule automatic post/page/content changes — expiration, workflows, scheduled content management.

- **Plugin Slug**: `post-expirator` | **Text Domain**: `post-expirator` | **Namespace**: `PublishPress\Future`
- **Minimum PHP**: 7.4 | **Minimum WordPress**: 6.7
- **Version**: `post-expirator.php` or `PUBLISHPRESS_FUTURE_VERSION`

## Technology Stack

### Core Technologies
PHP ≥7.4 | WP ≥6.7 | JSX admin UI (`assets/`) | CSS | MySQL/MariaDB

### Key Dependencies
- **Action Scheduler** (not WP-Cron): `lib/vendor/woocommerce/action-scheduler/`
- **PSR Container**: `lib/vendor/publishpress/psr-container/`
- **WP Reviews**: `lib/vendor/publishpress/wordpress-reviews/`

### Development Tools
Composer | npm/Yarn | Webpack | Codeception (Unit/Integration/Acceptance) | WP-Browser | Docker | PHPCS | PHPStan | PHP-CS-Fixer

### Build Tools
`dev-workspace/scripts/` | `dev-workspace/docker/compose.yaml` | Webpack | `composer.json` scripts

## PSR-12 PHP Coding Style

PSR-12 Extended | 4 spaces not tabs | braces next line | control-structure spacing per PSR-12 | UPPER_SNAKE constants | camelCase methods/properties | ~120 char soft limit | `namespace PublishPress\Future\{Module}\{SubFolder};` | grouped alphabetical imports | no trailing whitespace | single trailing newline | omit `?>` in pure PHP | method docblocks with `@since` | type hints (PHP 7.4+)
- All files must have the following comment at the top:

/**
 * <file purpose description>
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

## Modular WordPress Plugin Architecture

### Core Concepts
Layered Core/Framework/Modules/Views | feature modules in `Modules/` | cohesion per module | DI container | descriptive names | separate infrastructure from features

### Layout (glob repo for real names)
Core/ | Framework/ | Modules/{Name}/ | Views/
Module: Module.php, HooksAbstract.php, Controllers/, Models/, DBTableSchemas/, Migrations/
Framework: WordPress/Facade/, Database/, Logger/, …
tests/: Unit/, Integration/, Acceptance/features/, EndToEnd/, Support/

### Module Structure Pattern
Each feature module follows a consistent structure:
```
ModuleName/
├── Module.php                      # Module entry point & registration
├── HooksAbstract.php               # Hook name constants
├── CapabilitiesAbstract.php        # Capability constants (if needed)
├── Controllers/                    # Controllers for UI and API
├── Models/                         # Data models
├── DBTableSchemas/                 # Database table definitions
├── Migrations/                     # Database migration scripts
├── Interfaces/                     # Module-specific interfaces
├── Views/                          # Module-specific view templates
└── ... (other module-specific folders)
```

### Reference Modules
- **Expirator** — post expiration, scheduling, `ExpirationActions/`, classic/Gutenberg/bulk/quick edit
- **Workflows** — workflow engine, `Domain/Engine/`, REST API, workflow editor UI
- **Debug** — debug logging, admin debug views
- **Backup** — backup admin page + REST API
- **Settings** — plugin settings management

### Naming Conventions
- **Modules**: feature name (Expirator, Workflows, Debug, Backup)
- **Controllers**: purpose + `Controller` (BlockEditorController, RestAPIController)
- **Models**: entity + `Model` | **Facades**: WP area + `Facade` | **Interfaces**: contract + `Interface`
- **Abstract**: `Abstract` suffix | **Schemas**: table + `Schema` | **Migrations**: `V{version}{description}` (e.g. `V40000WorkflowScheduledStepsSchema`)

## Language-Specific Practices

PHP (`*.php`): follow `.cursor/rules/php.mdc` — clean code, design patterns, no god files
React/JSX (`*.jsx`, `*.tsx`): follow `.cursor/rules/react.mdc` — functional components, hooks, thin UI

## Design Patterns Used in This Project

- **DI Container** — `src/Core/DI/Container.php`, `services.php`, constructor injection
- **Service Provider** — `ServiceProvider.php`, `ServiceProviderInterface`
- **Factory** — model factories (e.g. `PostTypeDefaultDataModelFactory`), lazy closures
- **Facade** — `src/Framework/WordPress/Facade/` (HooksFacade, DatabaseFacade, …)
- **Adapter** — third-party bridges (e.g. `CronToWooActionSchedulerAdapter` in `Adapters/`)
- **Module** — `ModuleInterface` self-contained features (`Expirator/Module.php`)
- **Strategy** — `ExpirationActions/` + `ExpirationActionInterface`
- **Observer** — WP hooks via `HooksFacade` / `HookableInterface`; names in `HooksAbstract`
- **Command** — REST + WP-CLI
- **Repository** — module `Models/` (`WorkflowModel`, `ExpirablePostModel`)
- **Layered** — Core → Framework → Modules → Views
- **DDD elements** — `Domain/` dirs (e.g. `Workflows/Domain/Engine/`)
- **MVC-like** — Models + `Views/` templates + Controllers

## Testing Guidelines

**Codeception** — Unit, Integration, Acceptance, EndToEnd.

### Unit Testing
Behavior not implementation | `test_should_*` names | AAA | mock WP/DB | 80%+ domain coverage | `WPTestCase` or PHPUnit `TestCase`

### Integration Testing
Component + WP integration | real test DB | REST + controller/model flows | `WPTestCase` / `NoTransactionWPTestCase` | critical workflows

### Acceptance Testing (BDD)
Gherkin Given/When/Then | browser tests | `tests/Acceptance/features/` | steps in `tests/Support/GherkinSteps/` | admin/classic/Gutenberg/bulk/quick edit

### EndToEnd Testing
Lifecycle (activate/deactivate) | WP core interaction | real-world scenarios

### Test Environment
Docker `dev-workspace/docker/compose.yaml` — `db_test`, `test-wp`, `test-wpcli` | `composer test:up` / `test:clean` | `test:db-export` / `test:db-import`

### Test Naming Conventions
Classes: `{ClassName}Test.php` / `{ClassName}Cest.php` | PHPUnit: `test_should_do_something_when_condition()` | Codeception: `testShouldDoSomethingWhenCondition()` | features: `kebab-case.feature`

## Composer Commands

Grouped one-liners — see `composer.json` for full definitions.

| Group | Commands |
|-------|----------|
| env | `composer up`, `dev:up`, `test:up`, `down`, `dev:clean`, `test:clean` |
| build | `composer build`, `build:js`, `build:lang`, `build:all`, `watch:js` |
| check | `composer check`, `check:cs`, `fix:cs`, `fix:php`, `check:stan`, `check:longpath` |
| test | `composer test`, `test:all`, `test Unit\|Integration\|Acceptance\|EndToEnd`, `test Unit:Core/DI/ContainerTest`, `test Integration:Modules/…`, `test:debug`, `test:steps`, `test:snippets` |
| wp/db | `composer wp:dev -- …`, `wp:tests -- …`, `test:db-export`, `test:db-import`, `test:db-logs` |
| version | `composer get:version`, `set:version`, `pre-release` |

## Common Development Workflows

### Adding New Features
`src/Modules/NewFeature/` → `Module.php` (`ModuleInterface`) → `HooksAbstract.php` → `Controllers/`/`Models/`/`Views/` → `services.php` → Unit + Integration tests.

### Adding Database Tables
`DBTableSchemas/` + `DBTableSchemaInterface` → `Migrations/` `V{version}{description}.php` → register in `Module.php`.

### Adding REST API Endpoints
`Controllers/` → routes in `initialize()` → `/publishpress-future/v1/endpoint` → permissions → Integration tests in `tests/Integration/Modules/{Module}/Rest/`.

### Adding Expiration Actions
`ExpirationActions/` + `ExpirationActionInterface` → `ExpirationActionsAbstract` → `ExpirationActionsModel` → tests + i18n labels.

## WordPress-Specific Guidelines

### WordPress Coding Standards
WPCS where no PSR-12 conflict | WP via Facades | **Never use** global WP functions in business logic — facades | `$wpdb->prefix` on tables | nonces | sanitize in / escape out | `wp_enqueue_script`/`style`

### Hook Management
Hook constants in `HooksAbstract` | inject hook deps | register via `HooksFacade`/`HookableInterface` | focused callbacks | document hooks in PHPDoc

### Database Operations
`DatabaseFacade` / facaded `$wpdb` | schemas in `DBTableSchemas/` | `DBTableSchemaHandler` | `Migrations/` `V30000` style | `$wpdb->prepare()` for dynamic SQL

### Post Meta and Options
Keys in `PostMetaAbstract` / option constants | `OptionsFacade` | sanitize before save | correct meta types

### Capabilities and Permissions
`CapabilitiesAbstract` | check before privileged ops | `UsersFacade` for `current_user_can()` | document caps on controllers

### REST API
`/publishpress-future/v1/` prefix | permission callbacks | validate/sanitize params | REST schema | correct HTTP codes

### Admin UI
WP admin patterns + colors | core JS when possible | `NoticeFacade` | Settings API for settings pages

### Localization
Text domain `post-expirator` | `__()`, `_e()`, `esc_html__()` + translator comments | `composer build:lang` | `languages/`

### Performance
Transients (`TransientsAbstract`) | Action Scheduler not WP-Cron | minimize queries + cache | load assets only where needed | object cache when available

## Code Review Checklist

- [ ] PSR-12 + WPCS (where applicable) | domain language | SRP classes | small focused methods
- [ ] No smells (long params, feature envy) | exceptions for errors | Unit + Integration coverage
- [ ] Self-documenting | DI container | modular architecture | Facades for WP
- [ ] Sanitize in / escape out | capability checks | hook constants | DB schemas defined
- [ ] i18n `post-expirator` | proper asset enqueue
- [ ] No direct global access to WordPress functions in business logic
