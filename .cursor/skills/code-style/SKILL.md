---
name: code-style
description: Code style + project structure for PublishPress Future
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

### Directory Structure
```
src/
├── Core/                                    # Core infrastructure layer
│   ├── Autoloader.php                      # PSR-4 autoloader
│   ├── Plugin.php                          # Main plugin class
│   ├── Paths.php                           # Path management
│   ├── HookableInterface.php               # Hookable interface
│   ├── HooksAbstract.php                   # Hook constants base
│   └── DI/                                 # Dependency Injection
│       ├── Container.php                   # DI container implementation
│       ├── ContainerInterface.php          # Container interface
│       ├── ServiceProvider.php             # Service provider base
│       ├── ServiceProviderInterface.php    # Service provider interface
│       └── ServicesAbstract.php            # Service constants
├── Framework/                               # Reusable framework components
│   ├── InitializableInterface.php          # Initializable interface
│   ├── ModuleInterface.php                 # Module interface
│   ├── BaseException.php                   # Base exception
│   ├── Cache/                              # Cache handlers
│   │   ├── GenericCacheHandler.php
│   │   └── GenericCacheHandlerInterface.php
│   ├── Database/                           # Database utilities
│   │   ├── DBTableSchemaHandler.php
│   │   └── Interfaces/
│   │       ├── DBTableSchemaHandlerInterface.php
│   │       └── DBTableSchemaInterface.php
│   ├── Logger/                             # Logging system
│   │   ├── Logger.php
│   │   ├── LoggerInterface.php
│   │   ├── LogLevelAbstract.php
│   │   └── DBTableSchemas/
│   │       └── DebugLogSchema.php
│   ├── System/                             # System utilities
│   │   ├── DateTimeHandler.php
│   │   └── DateTimeHandlerInterface.php
│   └── WordPress/                          # WordPress abstractions
│       ├── Exceptions/                     # WordPress exceptions
│       ├── Facade/                         # WordPress function facades
│       │   ├── CronFacade.php
│       │   ├── DatabaseFacade.php
│       │   ├── DateTimeFacade.php
│       │   ├── EmailFacade.php
│       │   ├── HooksFacade.php
│       │   └── ... (more facades)
│       ├── Models/                         # WordPress data models
│       │   ├── PostModel.php
│       │   ├── TermModel.php
│       │   ├── UserModel.php
│       │   └── CurrentUserModel.php
│       └── Utils/                          # WordPress utilities
│           └── WorkflowSanitizationUtil.php
├── Modules/                                 # Feature modules layer
│   ├── Expirator/                          # Post expiration feature
│   │   ├── Module.php                      # Module definition
│   │   ├── HooksAbstract.php               # Hook constants
│   │   ├── CapabilitiesAbstract.php        # Capability constants
│   │   ├── PostMetaAbstract.php            # Post meta constants
│   │   ├── ExpirationActionsAbstract.php   # Action constants
│   │   ├── ExpirationScheduler.php         # Scheduling logic
│   │   ├── Controllers/                    # UI & API controllers
│   │   │   ├── BlockEditorController.php
│   │   │   ├── ClassicEditorController.php
│   │   │   ├── BulkEditController.php
│   │   │   ├── QuickEditController.php
│   │   │   ├── RestAPIController.php
│   │   │   └── ... (more controllers)
│   │   ├── Models/                         # Data models
│   │   │   ├── ExpirablePostModel.php
│   │   │   ├── ExpirationActionsModel.php
│   │   │   ├── PostTypeModel.php
│   │   │   └── ... (more models)
│   │   ├── ExpirationActions/              # Expiration actions
│   │   │   ├── ChangePostStatus.php
│   │   │   ├── DeletePost.php
│   │   │   ├── PostCategoryAdd.php
│   │   │   └── ... (more actions)
│   │   ├── DBTableSchemas/                 # Database schemas
│   │   │   └── ActionArgsSchema.php
│   │   ├── Migrations/                     # Database migrations
│   │   │   ├── V30000ActionArgsSchema.php
│   │   │   ├── V30001RestorePostMeta.php
│   │   │   └── ... (more migrations)
│   │   ├── Adapters/                       # Third-party adapters
│   │   │   └── CronToWooActionSchedulerAdapter.php
│   │   ├── Tables/                         # Admin list tables
│   │   │   └── ScheduledActionsTable.php
│   │   └── Interfaces/                     # Module interfaces
│   ├── Workflows/                          # Advanced workflow engine
│   │   ├── Module.php                      # Module definition
│   │   ├── HooksAbstract.php               # Hook constants
│   │   ├── CapabilitiesAbstract.php        # Capability constants
│   │   ├── TransientsAbstract.php          # Transient constants
│   │   ├── Controllers/                    # UI & API controllers
│   │   │   ├── WorkflowEditor.php
│   │   │   ├── WorkflowsList.php
│   │   │   ├── PostType.php
│   │   │   └── ... (more controllers)
│   │   ├── Models/                         # Data models
│   │   │   ├── WorkflowModel.php
│   │   │   ├── WorkflowsModel.php
│   │   │   ├── PostModel.php
│   │   │   └── ... (more models)
│   │   ├── Domain/                         # Domain logic
│   │   │   ├── Engine/                     # Workflow execution engine
│   │   │   │   ├── WorkflowEngine.php
│   │   │   │   ├── JsonLogicEngine.php
│   │   │   │   ├── ExecutionContext.php
│   │   │   │   └── ... (more engine components)
│   │   │   ├── Steps/                      # Workflow step types
│   │   │   │   ├── Actions/                # Action steps
│   │   │   │   ├── Triggers/               # Trigger steps
│   │   │   │   └── Processors/             # Step processors
│   │   │   └── Caches/                     # Domain caches
│   │   ├── Rest/                           # REST API
│   │   │   ├── RestApiManager.php
│   │   │   └── RestApiV1.php
│   │   ├── DBTableSchemas/                 # Database schemas
│   │   │   └── WorkflowScheduledStepsSchema.php
│   │   ├── Migrations/                     # Database migrations
│   │   ├── Infrastructure/                 # Infrastructure concerns
│   │   │   └── Safety/
│   │   │       └── WorkflowExecutionSafeguard.php
│   │   ├── Interfaces/                     # Module interfaces
│   │   └── Views/                          # Module views
│   ├── Debug/                              # Debug & logging feature
│   │   ├── Module.php
│   │   ├── HooksAbstract.php
│   │   ├── Debug.php
│   │   ├── DebugInterface.php
│   │   ├── Controllers/
│   │   │   └── Controller.php
│   │   └── Views/
│   │       └── raw-debug-log.html.php
│   ├── Backup/                             # Backup feature
│   │   ├── Module.php
│   │   ├── HooksAbstract.php
│   │   └── Controllers/
│   │       ├── BackupAdminPage.php
│   │       └── BackupRestApi.php
│   ├── Settings/                           # Settings management
│   │   ├── Module.php
│   │   └── Controllers/
│   │       └── Controller.php
│   ├── WooCommerce/                        # WooCommerce integration
│   │   └── Module.php
│   ├── VersionNotices/                     # Version notices
│   │   └── Module.php
│   └── InstanceProtection/                 # Instance protection
│       └── Module.php
└── Views/                                   # Global view templates
    ├── menu-general.php
    ├── menu-defaults.php
    ├── menu-display.php
    ├── bulk-edit.php
    ├── quick-edit.php
    └── ... (more views)
```

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

### Naming Conventions
- **Modules**: feature name (Expirator, Workflows, Debug, Backup)
- **Controllers**: purpose + `Controller` (BlockEditorController, RestAPIController)
- **Models**: entity + `Model` | **Facades**: WP area + `Facade` | **Interfaces**: contract + `Interface`
- **Abstract**: `Abstract` suffix | **Schemas**: table + `Schema` | **Migrations**: `V{version}{description}` (e.g. `V40000WorkflowScheduledStepsSchema`)

## Clean Code Principles

### Functions and Methods
SRP per function | <20 lines when possible | intention-revealing names | ≤3 params (object for more) | no surprise side effects | command/query separation

### Variables and Constants
Descriptive names | no mental-mapping `$i`/`$j` | constants not magic values | `$isUserActive` not `$flag`

### Comments and Documentation
Self-documenting code; comments explain why | update with code | PHPDoc on public API | no redundant restatements

### Error Handling
Exceptions for exceptional cases | domain exception classes | fail fast on bad input | avoid null — Optional or throw

## SOLID Principles

### Single Responsibility Principle (SRP)
One reason to change per class; split data/business/presentation; prefer composition.

### Open/Closed Principle (OCP)
Open for extension, closed for modification; interfaces/abstracts; Strategy/Decorator/Template Method.

### Liskov Substitution Principle (LSP)
Subtypes substitutable for base; keep behavioral contracts; don't strengthen preconditions or weaken postconditions.

### Interface Segregation Principle (ISP)
Small focused interfaces; no fat interfaces; compose when needed.

### Dependency Inversion Principle (DIP)
High/low level depend on abstractions; constructor injection + DI container.

## Object Calisthenics

### The 9 Rules

1. **Only One Level of Indentation Per Method** — extract nested logic to private methods
2. **Don't Use the ELSE Keyword** — early returns / guard clauses
3. **Wrap All Primitives and Strings** — value objects (`Email`, `Money`, `UserId`)
4. **First Class Collections** — domain collection classes with behavior
5. **One Dot Per Line** — avoid Law of Demeter chains; delegate
6. **Don't Abbreviate** — full names (`$userRepository` not `$userRepo`)
7. **Keep All Entities Small** — class <50 lines; method <10; split via composition
8. **No Classes with More Than Two Instance Variables** — cohesion via value objects
9. **No Getters/Setters/Properties** — behavior over exposed state

## Design Patterns Used in This Project

### Creational Patterns
- **DI Container**: `src/Core/DI/Container.php`, `services.php`, constructor injection
- **Factory**: model factories (e.g. `PostTypeDefaultDataModelFactory`), lazy closures
- **Service Provider**: `ServiceProvider.php`, `ServiceProviderInterface`

### Structural Patterns
- **Facade**: `src/Framework/WordPress/Facade/` — testable WP wrappers (HooksFacade, DatabaseFacade, …)
- **Adapter**: third-party bridges (e.g. `CronToWooActionSchedulerAdapter` in `Adapters/`)
- **Module**: `ModuleInterface` self-contained features (`Expirator/Module.php`)

### Behavioral Patterns
- **Strategy**: `ExpirationActions/` + `ExpirationActionInterface` (`DeletePost`, `ChangePostStatus`, …)
- **Observer**: WP hooks via `HooksFacade` / `HookableInterface`; names in `HooksAbstract`
- **Command**: REST + WP-CLI
- **Repository**: module `Models/` (`WorkflowModel`, `ExpirablePostModel`)

### Architectural Patterns
- **Layered**: Core → Framework → Modules → Views
- **DDD elements**: `Domain/` dirs (e.g. `Workflows/Domain/Engine/`)
- **MVC-like**: Models + `Views/` templates + Controllers

## Testing Guidelines

**Codeception** — Unit, Integration, Acceptance, EndToEnd.

### Running Tests

```bash
# Run all tests
composer test

# Run specific suite
composer test Unit
composer test Integration
composer test Acceptance
composer test EndToEnd

# Run specific test file
composer test Integration:Modules/Workflows/Domain/Engine/ExecutionContextTest

# Run with debug mode
composer test:debug Integration
```

### Unit Testing
Behavior not implementation | `test_should_*` names | AAA | mock WP/DB | 80%+ domain coverage | `WPTestCase` or PHPUnit `TestCase`

### Integration Testing
Component + WP integration | real test DB | REST + controller/model flows | `WPTestCase` / `NoTransactionWPTestCase` | critical workflows

### Acceptance Testing (BDD)
Gherkin Given/When/Then | browser tests | `tests/Acceptance/features/` | steps in `tests/Support/GherkinSteps/` | admin/classic/Gutenberg/bulk/quick edit

### EndToEnd Testing
Lifecycle (activate/deactivate) | WP core interaction | real-world scenarios

### Test Organization
```
tests/
├── Unit/                                    # Unit tests
│   ├── Core/                               # Core infrastructure tests
│   │   ├── DI/                             # Container tests
│   │   └── PathsTest.php
│   ├── Framework/                          # Framework component tests
│   │   └── Logger/
│   └── Modules/                            # Module-specific unit tests
│       └── Workflows/
│           └── Domain/
│               └── Engine/
├── Integration/                             # Integration tests
│   ├── Core/
│   ├── Framework/                          # Framework integration tests
│   │   ├── Logger/
│   │   ├── System/
│   │   └── WordPress/
│   │       ├── Facade/
│   │       └── Models/
│   ├── Modules/                            # Module integration tests
│   │   ├── Expirator/
│   │   │   ├── DBTableSchemaHandlerTest.php
│   │   │   ├── DBTableSchemas/
│   │   │   └── Models/
│   │   └── Workflows/
│   │       ├── DBTableSchemas/
│   │       ├── Domain/
│   │       ├── Models/
│   │       └── Rest/
│   └── NoTransactionWPTestCase.php         # Base class for no-transaction tests
├── Acceptance/                              # BDD acceptance tests
│   ├── features/                           # Gherkin feature files
│   │   ├── bulk-edit.feature
│   │   ├── quick-edit.feature
│   │   ├── expiring-post-classic-editor.feature
│   │   ├── expiring-post-gutenberg.feature
│   │   └── settings/
│   │       ├── admin-menu.feature
│   │       ├── defaults.feature
│   │       └── post-types.*.feature
│   └── Acceptance.suite.yml
├── EndToEnd/                                # End-to-end tests
│   ├── ActivationCest.php
│   └── EndToEnd.suite.yml
└── Support/                                 # Test support files
    ├── GherkinSteps/                       # BDD step definitions
    │   ├── Cli.php
    │   ├── Post.php
    │   ├── Settings.php
    │   └── ... (more steps)
    ├── Data/                               # Test data
    │   ├── dump.sql
    │   └── plugins/
    └── *Tester.php                         # Tester classes
```

### Test Environment
Docker `dev-workspace/docker/compose.yaml` — `db_test`, `test-wp`, `test-wpcli` | `composer test:up` / `test:clean` | `test:db-export` / `test:db-import`

### Test Naming Conventions
Classes: `{ClassName}Test.php` / `{ClassName}Cest.php` | PHPUnit: `test_should_do_something_when_condition()` | Codeception: `testShouldDoSomethingWhenCondition()` | features: `kebab-case.feature`

## Common Development Workflows

### Setting Up Development Environment

```bash
# Start both development and test environments
composer up

# Start only development environment
composer dev:up

# Start only test environment
composer test:up

# Stop environments
composer down

# Clean up and remove containers
composer dev:clean
composer test:clean
```

### Building the Plugin

```bash
# Build complete plugin package (zip file)
composer build

# Build only JavaScript assets
composer build:js

# Build only language files
composer build:lang

# Build everything (JS + Lang + Package)
composer build:all

# Watch JavaScript for changes during development
composer watch:js
```

### Code Quality Checks

```bash
# Run all checks (PHP compatibility, linting, code standards)
composer check

# Check code standards only
composer check:cs

# Fix code standards automatically
composer fix:cs
composer fix:php

# Run static analysis
composer check:stan

# Check for long file paths (Windows compatibility)
composer check:longpath
```

### Running Tests

```bash
# Run all tests (Unit + Integration)
composer test:all

# Run specific test suite
composer test Unit
composer test Integration
composer test Acceptance
composer test EndToEnd

# Run specific test file or test
composer test Unit:Core/DI/ContainerTest
composer test Integration:Modules/Workflows/Domain/Engine/ExecutionContextTest

# Run tests in debug mode (with Xdebug)
composer test:debug Integration

# View Gherkin steps and snippets
composer test:steps
composer test:snippets
```

### Working with WordPress CLI

```bash
# Run WP-CLI in development environment
composer wp:dev -- plugin list
composer wp:dev -- post list

# Run WP-CLI in test environment
composer wp:tests -- plugin list
composer wp:tests -- db export
```

### Database Operations

```bash
# Export test database
composer test:db-export

# Import test database
composer test:db-import path/to/dump.sql

# View database logs
composer test:db-logs
```

### Version Management

```bash
# Get current plugin version
composer get:version

# Set new plugin version
composer set:version 4.10.0

# Prepare for release (creates branch and PR)
composer pre-release 4.10.0
```

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
