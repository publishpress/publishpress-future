---
name: code-style
description: Code Style and Project Structure Guidelines for PublishPress Future Plugin
---
# Code Style Guidelines

## Project Overview

**PublishPress Future** (formerly Post Expirator) is a WordPress plugin that allows users to schedule automatic changes to posts, pages, and other content types. It provides post expiration functionality, advanced workflow automation, and scheduled content management.

- **Plugin Slug**: `post-expirator`
- **Text Domain**: `post-expirator`
- **Namespace**: `PublishPress\Future`
- **Minimum PHP**: 7.4
- **Minimum WordPress**: 6.7
- **Current Version**: Check `post-expirator.php` or `PUBLISHPRESS_FUTURE_VERSION` constant

## Technology Stack

### Core Technologies
- **PHP**: ≥ 7.4 (with type declarations and modern PHP features)
- **WordPress**: ≥ 6.7
- **JavaScript/React**: JSX components for admin UI (in `assets/` directory)
- **CSS**: Custom styles for admin interface
- **MySQL/MariaDB**: Database storage

### Key Dependencies
- **WooCommerce Action Scheduler**: Background task processing (instead of WP-Cron)
  - Located in `lib/vendor/woocommerce/action-scheduler/`
  - Used for reliable background processing of expiration actions
- **PSR Container**: Dependency injection container interface
  - Located in `lib/vendor/publishpress/psr-container/`
- **WordPress Reviews Library**: Review prompts
  - Located in `lib/vendor/publishpress/wordpress-reviews/`

### Development Tools
- **Composer**: PHP dependency management
- **npm/Yarn**: JavaScript dependency management
- **Webpack**: JavaScript bundling
- **Codeception**: Testing framework (Unit, Integration, Acceptance tests)
- **WP-Browser**: WordPress testing utilities (lucatume/wp-browser)
- **Docker**: Development and testing environment
- **PHP_CodeSniffer**: Code style checking
- **PHPStan**: Static analysis
- **PHP-CS-Fixer**: Automated code style fixing

### Build Tools
- **Build Scripts**: Located in `dev-workspace/scripts/`
- **Docker Compose**: Development environment (`dev-workspace/docker/compose.yaml`)
- **Webpack Config**: JavaScript build configuration
- **Composer Scripts**: Build, test, and deployment automation (see `composer.json`)

# Code Style Guidelines

## PSR-12 PHP Coding Style

- Follow PSR-12 Extended Coding Style Guide for all PHP code
- Use 4 spaces for indentation, never tabs
- Opening braces for classes, methods, and functions must go on the next line
- Control structure keywords must be followed by one space
- Opening parentheses for control structures must not have a space after them
- Closing parentheses for control structures must not have a space before them
- There must be one space after control structure closing parentheses and before braces
- Class constants must be declared in all upper case with underscore separators
- Method names must be declared in camelCase
- Property names should be declared in camelCase
- Lines should not exceed 120 characters (soft limit)
- Use namespace declaration at the top of PHP files: `namespace PublishPress\Future\{Module}\{SubFolder};`
- Import statements must be grouped and ordered alphabetically within each group
- There must not be trailing whitespace at the end of lines
- Files must end with a single blank line
- PHP closing tags must be omitted from files containing only PHP
- All methods should have a docblock comment above it with a @since tag indicating the version it was introduced
- Use type declarations for all parameters, return types, and properties where possible (minimum PHP 7.4 compatibility)
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
- **Layered Architecture**: Clear separation between Core, Framework, Modules, and Views
- **Feature Modules**: Organize code by feature/functionality within the Modules layer
- **Cohesion**: Keep related code together in feature-specific module directories
- **Dependency Injection**: Use DI container for managing dependencies
- **Clear Naming**: Use descriptive, purpose-driven names for classes and methods
- **Separation of Concerns**: Core infrastructure separate from framework components, separate from business features

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
- **Modules**: Named after the feature they provide (Expirator, Workflows, Debug, Backup)
- **Controllers**: Named by their purpose with "Controller" suffix (BlockEditorController, RestAPIController)
- **Models**: Named after the entity with "Model" suffix (WorkflowModel, PostModel)
- **Facades**: Named after the WordPress functionality with "Facade" suffix (HooksFacade, DatabaseFacade)
- **Interfaces**: Named after the contract with "Interface" suffix (ModuleInterface, LoggerInterface)
- **Abstract classes**: Named with "Abstract" suffix (HooksAbstract, ServicesAbstract)
- **Database Schemas**: Named after the table with "Schema" suffix (DebugLogSchema, ActionArgsSchema)
- **Migrations**: Prefixed with version number (V30000ActionArgsSchema, V40000WorkflowScheduledStepsSchema)

## Clean Code Principles

### Functions and Methods
- **Single Responsibility**: Each function should do one thing well
- **Small Functions**: Keep functions under 20 lines when possible
- **Descriptive Names**: Use intention-revealing names (`calculateMonthlyRevenue()` not `calc()`)
- **Minimize Parameters**: Limit to 3 parameters; use objects for more complex data
- **No Side Effects**: Functions should be predictable and not modify external state unexpectedly
- **Command Query Separation**: Methods either do something or return something, not both

### Variables and Constants
- Use descriptive variable names (`$userEmail` not `$e`)
- Avoid mental mapping (`$i`, `$j`, `$k` in nested loops)
- Use constants for magic numbers and strings
- Prefer intention-revealing boolean variables (`$isUserActive` not `$flag`)

### Comments and Documentation
- Write self-documenting code; comments should explain "why", not "what"
- Update comments when code changes
- Use PHPDoc blocks for all public methods and classes
- Avoid redundant comments that restate the code

### Error Handling
- Use exceptions for exceptional cases, not control flow
- Create custom exception classes for domain-specific errors
- Fail fast: validate inputs early and clearly
- Don't return null; use Optional pattern or throw exceptions

## SOLID Principles

### Single Responsibility Principle (SRP)
- Each class should have only one reason to change
- Separate concerns: data access, business logic, presentation
- Use composition over inheritance to combine responsibilities

### Open/Closed Principle (OCP)
- Classes should be open for extension, closed for modification
- Use interfaces and abstract classes for extensibility
- Leverage Strategy, Decorator, and Template Method patterns

### Liskov Substitution Principle (LSP)
- Derived classes must be substitutable for their base classes
- Maintain behavioral contracts in inheritance hierarchies
- Avoid strengthening preconditions or weakening postconditions

### Interface Segregation Principle (ISP)
- Clients shouldn't depend on interfaces they don't use
- Create specific, focused interfaces rather than large, general ones
- Use composition to combine multiple interfaces when needed

### Dependency Inversion Principle (DIP)
- High-level modules shouldn't depend on low-level modules
- Both should depend on abstractions (interfaces)
- Use dependency injection containers and constructor injection

## Object Calisthenics

### The 9 Rules

1. **Only One Level of Indentation Per Method**
   - Extract nested logic into private methods
   - Improves readability and testability

2. **Don't Use the ELSE Keyword**
   - Use early returns and guard clauses
   - Reduces cyclomatic complexity

3. **Wrap All Primitives and Strings**
   - Create value objects for domain concepts
   - Example: `Email`, `Money`, `UserId` instead of raw strings/integers

4. **First Class Collections**
   - Wrap collections in domain-specific classes
   - Add behavior to collection classes (filtering, transformation)

5. **One Dot Per Line**
   - Avoid method chaining that violates Law of Demeter
   - `$user->getProfile()->getEmail()` → use delegation

6. **Don't Abbreviate**
   - Use full, descriptive names for variables, methods, and classes
   - `$userRepository` not `$userRepo`

7. **Keep All Entities Small**
   - Classes should be under 50 lines
   - Methods should be under 10 lines
   - Split large classes using composition

8. **No Classes with More Than Two Instance Variables**
   - Forces high cohesion and single responsibility
   - Use value objects and composition

9. **No Getters/Setters/Properties**
   - Tell objects what to do, don't ask for their data
   - Implement behavior instead of exposing internal state

## Design Patterns Used in This Project

### Creational Patterns
- **Dependency Injection Container**: Central DI container manages all dependencies
  - Located in `src/Core/DI/Container.php`
  - Service definitions in `services.php`
  - Constructor injection for all dependencies
- **Factory Pattern**: Used for creating model instances
  - Example: `PostTypeDefaultDataModelFactory`
  - Factories injected as closures for lazy instantiation
- **Service Provider**: Registers services in the DI container
  - Base class: `src/Core/DI/ServiceProvider.php`
  - Interface: `ServiceProviderInterface`

### Structural Patterns
- **Facade Pattern**: WordPress functions wrapped in Facade classes
  - Located in `src/Framework/WordPress/Facade/`
  - Examples: `HooksFacade`, `DatabaseFacade`, `EmailFacade`, `CronFacade`
  - Provides testable interfaces to WordPress global functions
  - Allows mocking in unit tests
- **Adapter Pattern**: Adapts third-party libraries to plugin interfaces
  - Example: `CronToWooActionSchedulerAdapter` (WP-Cron to Action Scheduler)
  - Located in module `Adapters/` directories
- **Module Pattern**: Self-contained feature modules
  - Each module implements `ModuleInterface`
  - Modules register themselves with the plugin
  - Example: `src/Modules/Expirator/Module.php`

### Behavioral Patterns
- **Strategy Pattern**: Expiration actions are interchangeable strategies
  - Located in `src/Modules/Expirator/ExpirationActions/`
  - All implement `ExpirationActionInterface`
  - Examples: `DeletePost`, `ChangePostStatus`, `PostCategoryAdd`
- **Observer Pattern**: WordPress hooks system
  - Managed through `HooksFacade` and `HookableInterface`
  - Hook names defined in `HooksAbstract` classes
- **Command Pattern**: REST API endpoints and WP-CLI commands
  - REST API controllers execute commands
  - WP-CLI commands in module Controllers
- **Repository Pattern**: Models abstract data access
  - Located in module `Models/` directories
  - Examples: `WorkflowModel`, `ExpirablePostModel`, `PostModel`

### Architectural Patterns
- **Layered Architecture**: Clear separation of concerns
  - Core Layer: Infrastructure and bootstrapping
  - Framework Layer: Reusable components
  - Modules Layer: Business logic and features
  - Views Layer: Presentation
- **Domain-Driven Design Elements**:
  - `Domain/` directories contain business logic
  - Example: `src/Modules/Workflows/Domain/Engine/`
  - Separation of domain logic from infrastructure
- **MVC-like Pattern** (adapted for WordPress):
  - Models: Data and business logic
  - Views: PHP templates in `Views/` directories
  - Controllers: Handle requests, coordinate between models and views

## Testing Guidelines

This project uses **Codeception** for testing with multiple test suites: Unit, Integration, Acceptance, and EndToEnd.

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
- Test behavior, not implementation details
- Use descriptive test method names: `test_should_calculate_expiration_correctly_for_scheduled_posts()`
- Follow AAA pattern: Arrange, Act, Assert
- Mock external dependencies (WordPress functions, database, etc.)
- Aim for high test coverage in domain and business logic (80%+)
- Use `WPTestCase` for WordPress-specific unit tests
- Use standard PHPUnit `TestCase` for framework/core logic tests

### Integration Testing
- Test the interaction between components and WordPress
- Test database operations, models, and data access
- Use actual WordPress test database (not mocked)
- Test REST API endpoints
- Test controller interactions with models
- Extend `WPTestCase` or `NoTransactionWPTestCase` for integration tests
- Focus on critical business workflows

### Acceptance Testing (BDD)
- Use Gherkin syntax (Given/When/Then) for feature files
- Test user-facing functionality through the browser
- Feature files located in `tests/Acceptance/features/`
- Step definitions in `tests/Support/GherkinSteps/`
- Test admin UI, classic editor, Gutenberg editor, bulk/quick edit
- Examples:
  - Post expiration workflows
  - Settings management
  - Bulk and quick edit operations

### EndToEnd Testing
- Test complete plugin lifecycle (activation, deactivation, etc.)
- Test plugin interactions with WordPress core
- Test real-world usage scenarios

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
- Tests run in Docker containers (see `dev-workspace/docker/compose.yaml`)
- Services: `db_test`, `test-wp`, `test-wpcli`
- Environment setup: `composer test:up`
- Environment cleanup: `composer test:clean`
- Database operations: `composer test:db-export`, `composer test:db-import`

### Test Naming Conventions
- Test classes: `{ClassName}Test.php` or `{ClassName}Cest.php`
- Test methods (PHPUnit): `test_should_do_something_when_condition()`
- Test methods (Codeception): `testShouldDoSomethingWhenCondition()`
- Feature files: `kebab-case.feature`

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

When adding a new feature module:

1. Create module directory: `src/Modules/NewFeature/`
2. Create `Module.php` implementing `ModuleInterface`
3. Create `HooksAbstract.php` for hook constants
4. Create `Controllers/`, `Models/`, `Views/` subdirectories as needed
5. Register module in `services.php`
6. Add tests in `tests/Unit/Modules/NewFeature/` and `tests/Integration/Modules/NewFeature/`

### Adding Database Tables

1. Create schema class in module's `DBTableSchemas/` directory
2. Implement `DBTableSchemaInterface`
3. Create migration in module's `Migrations/` directory
4. Version prefix: `V{version}{description}.php` (e.g., `V40000NewFeatureSchema.php`)
5. Register schema and migration in module's `Module.php`

### Adding REST API Endpoints

1. Create controller in module's `Controllers/` directory
2. Extend appropriate base controller or implement from scratch
3. Register routes in controller's `initialize()` method
4. Use namespace pattern: `/publishpress-future/v1/endpoint`
5. Implement permission callbacks
6. Add integration tests in `tests/Integration/Modules/{Module}/Rest/`

### Adding Expiration Actions

1. Create action class in `src/Modules/Expirator/ExpirationActions/`
2. Implement `ExpirationActionInterface`
3. Register action in `ExpirationActionsAbstract` constants
4. Register action in `ExpirationActionsModel`
5. Add tests for the action
6. Add translations for action labels

## WordPress-Specific Guidelines

### WordPress Coding Standards
- Follow WordPress Coding Standards where they don't conflict with PSR-12
- Use WordPress functions through Facade classes (HooksFacade, DatabaseFacade, etc.)
- Never use global WordPress functions directly in business logic; wrap them in facades
- Prefix all database table names with `$wpdb->prefix`
- Use `wp_nonce_field()` and `check_admin_referer()` for form security
- Sanitize all user inputs using appropriate WordPress functions
- Escape all outputs using `esc_html()`, `esc_attr()`, `esc_url()`, etc.
- Use `wp_enqueue_script()` and `wp_enqueue_style()` for assets

### Hook Management
- Define all hook names as constants in `HooksAbstract` classes
- Use dependency injection to pass hook dependencies
- Register hooks through `HooksFacade` or `HookableInterface`
- Keep hook callbacks focused and delegated to appropriate classes
- Document hook names and parameters in PHPDoc

### Database Operations
- All database operations should use `DatabaseFacade` or `$wpdb` through facades
- Define database schemas in `DBTableSchemas/` directory
- Use `DBTableSchemaHandler` for schema management
- Version migrations in `Migrations/` directory with version prefix (e.g., `V30000`)
- Never use raw SQL queries; use `$wpdb->prepare()` for dynamic queries

### Post Meta and Options
- Define post meta keys in `PostMetaAbstract` classes
- Define option keys in constants or abstract classes
- Use `OptionsFacade` for reading/writing options
- Always sanitize meta values before saving
- Use appropriate meta types (string, int, bool, array)

### Capabilities and Permissions
- Define all capability names in `CapabilitiesAbstract` classes
- Check capabilities before performing privileged operations
- Use `current_user_can()` through `UsersFacade`
- Document required capabilities in controller methods

### REST API
- Namespace all REST routes with plugin prefix: `/publishpress-future/v1/`
- Implement proper permission callbacks
- Validate and sanitize all request parameters
- Use WordPress REST API schema validation
- Return proper HTTP status codes

### Admin UI
- Use WordPress admin UI components and patterns
- Follow WordPress admin color schemes
- Use WordPress core JavaScript libraries when possible
- Implement proper admin notices through `NoticeFacade`
- Use WordPress Settings API for settings pages

### Localization
- Use text domain: `post-expirator`
- Wrap all user-facing strings with `__()`, `_e()`, `esc_html__()`, etc.
- Include translator comments for context when needed
- Build translation files: `composer build:lang`
- Keep language files in `languages/` directory

### Performance
- Use transients for caching expensive operations (define in `TransientsAbstract`)
- Use Action Scheduler for background tasks instead of WP-Cron
- Minimize database queries; use caching when appropriate
- Load assets only on pages where needed
- Use WordPress object cache when available

## Code Review Checklist

- [ ] Follows PSR-12 coding standards
- [ ] Follows WordPress Coding Standards (where applicable)
- [ ] Uses domain language consistently
- [ ] Classes have single responsibility
- [ ] Methods are small and focused
- [ ] No code smells (long parameter lists, feature envy, etc.)
- [ ] Proper error handling with exceptions
- [ ] Adequate test coverage (Unit + Integration)
- [ ] Clear, self-documenting code
- [ ] Dependencies are injected through DI container
- [ ] Follows established modular architecture pattern
- [ ] WordPress functions accessed through facades
- [ ] All user inputs are sanitized
- [ ] All outputs are properly escaped
- [ ] Proper capability checks for privileged operations
- [ ] Hook names defined as constants
- [ ] Database schemas properly defined
- [ ] Strings are translatable with correct text domain
- [ ] Assets enqueued properly (not hardcoded)
- [ ] No direct global access to WordPress functions in business logic
