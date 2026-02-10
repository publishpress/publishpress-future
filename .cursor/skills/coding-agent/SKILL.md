# Coding Agent Skill

## Purpose
Launch a specialized coding agent to implement changes in the PublishPress Future codebase following project conventions and best practices.

## When to Use
Use this skill when you need to:
- Implement new features
- Fix bugs
- Refactor existing code
- Add or modify functionality
- Update code to follow standards
- Add new modules or extend existing ones

## How to Use

### Basic Usage
```
@coding-agent implement [your instructions here]
```

### Example Commands
```
@coding-agent implement a new expiration action to send email notifications

@coding-agent fix the issue where scheduled actions don't trigger on multisite

@coding-agent refactor the ExpirablePostModel to improve performance

@coding-agent add validation for workflow step configurations

@coding-agent implement a new REST endpoint for exporting scheduled actions as CSV
```

## Instructions for the Agent

When this skill is invoked, launch an agent with these guidelines:

### Project Context
- **Plugin:** PublishPress Future (formerly Post Expirator)
- **Text Domain:** `post-expirator`
- **Namespace:** `PublishPress\Future`
- **Language:** PHP 7.4+, JavaScript (React/JSX)
- **Framework:** WordPress plugin architecture
- **Minimum PHP:** 7.4
- **Minimum WordPress:** 6.7
- **Standards:** WordPress Coding Standards, PSR-12
- **Architecture:** Layered modular architecture with DI container
- **Frontend:** JSX syntax for React components
- **Background Processing:** WooCommerce Action Scheduler (not WP-Cron)

### Coding Standards

#### PHP
- Follow PSR-12 coding standards
- Use namespace declaration: `namespace PublishPress\Future\{Module}\{SubFolder};`
- Follow WordPress naming conventions
- Use dependency injection via DI container
- Type hint all parameters and return types (PHP 7.4 compatible)
- **Document all code with proper PHPDoc** (see "PHPDoc requirements" below)
- Never use global WordPress functions directly in business logic; use Facades
- Escape output: `esc_html()`, `esc_attr()`, `esc_url()`
- Sanitize input: `sanitize_text_field()`, `absint()`, etc.
- Verify nonces for actions
- Check capabilities through `UsersFacade` or `current_user_can()`
- Define hook names as constants in `HooksAbstract` classes
- Define capabilities in `CapabilitiesAbstract` classes
- Define post meta keys in `PostMetaAbstract` classes

#### PHPDoc requirements (mandatory for all new code)

- **File-level:** Every PHP file must have a docblock at the top with:
  - Short description (file purpose)
  - `@package PublishPress\Future`
  - `@author PublishPress`
  - `@copyright Copyright (c) 2026, PublishPress`
  - `@license GPLv2 or later`

- **Classes:** Use a brief class description where it adds clarity.

- **Methods and functions:** Every method and function must have a docblock with:
  - Short description (what it does)
  - `@param` for each parameter (with type and description)
  - `@return` when the return type is not void (with type and description)
  - `@throws` when the method throws exceptions
  - `@since {version}` (the version the method was introduced, e.g., 4.9.0)

#### JavaScript/React
- Use functional components only
- Use JSX syntax
- Translations: `__('text', 'post-expirator')`
- Text domain is always `post-expirator`
- Format numbers: `formatNumber()` or `number_format_i18n()`
- No emojis unless explicitly requested

#### File Organization
```
src/
├── Core/                           # Core infrastructure layer
│   ├── Autoloader.php
│   ├── Plugin.php
│   ├── Paths.php
│   ├── HookableInterface.php
│   ├── HooksAbstract.php
│   └── DI/                        # Dependency Injection
│       ├── Container.php
│       ├── ContainerInterface.php
│       ├── ServiceProvider.php
│       └── ServicesAbstract.php
├── Framework/                      # Reusable framework components
│   ├── InitializableInterface.php
│   ├── ModuleInterface.php
│   ├── BaseException.php
│   ├── Cache/
│   ├── Database/
│   ├── Logger/
│   └── WordPress/
│       ├── Facade/                # WordPress function wrappers
│       ├── Models/                # WordPress data models
│       └── Utils/
├── Modules/                        # Feature modules
│   ├── Expirator/                 # Post expiration feature
│   │   ├── Module.php
│   │   ├── HooksAbstract.php
│   │   ├── CapabilitiesAbstract.php
│   │   ├── PostMetaAbstract.php
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── ExpirationActions/
│   │   ├── DBTableSchemas/
│   │   ├── Migrations/
│   │   └── Interfaces/
│   ├── Workflows/                 # Advanced workflow engine
│   │   ├── Module.php
│   │   ├── HooksAbstract.php
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Domain/                # Domain logic
│   │   │   ├── Engine/
│   │   │   └── Steps/
│   │   ├── Rest/
│   │   └── Interfaces/
│   ├── Debug/
│   ├── Backup/
│   ├── Settings/
│   └── ... (other modules)
└── Views/                          # Global view templates
```

### Development Workflow

1. **Read existing code** to understand patterns
2. **Check coding standards** with `composer check:cs`
3. **Fix coding standards** with `composer fix:cs` or `composer fix:php`
4. **Run static analysis** with `composer check:stan`
5. **Verify syntax** with `composer check:lint`
6. **Test changes** with `composer test` (Unit and Integration tests)
7. **Build JavaScript assets** with `composer build:js` if JSX changed
8. **Build language files** with `composer build:lang` if translations added
9. **Update documentation** if needed

### Container Registration

When adding new services:
1. Add constant to `src/Core/DI/ServicesAbstract.php`
2. Register in `services.php` (root directory)
3. Inject dependencies through constructor
4. Services implementing `InitializableInterface` will be auto-initialized

### Module Creation

When adding a new feature module:
1. Create directory: `src/Modules/{ModuleName}/`
2. Create `Module.php` implementing `ModuleInterface`
3. Create `HooksAbstract.php` for hook name constants
4. Create subdirectories as needed:
   - `Controllers/` - UI and API controllers
   - `Models/` - Data models
   - `DBTableSchemas/` - Database table definitions
   - `Migrations/` - Database migration scripts
   - `Interfaces/` - Module-specific interfaces
   - `Views/` - Module-specific templates
5. Register module in `services.php`
6. Add tests in `tests/Unit/Modules/{ModuleName}/` and `tests/Integration/Modules/{ModuleName}/`

### Common Patterns

#### Creating a Module
```php
<?php

/**
 * Module for {feature name}.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future\Modules\FeatureName;

use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\ModuleInterface;

defined('ABSPATH') or die('Direct access not allowed.');

final class Module implements ModuleInterface
{
    /**
     * @var \PublishPress\Future\Core\HookableInterface
     */
    private $hooks;

    /**
     * Constructor.
     *
     * @param \PublishPress\Future\Core\HookableInterface $hooks
     *
     * @since 4.9.0
     */
    public function __construct($hooks)
    {
        $this->hooks = $hooks;
    }

    /**
     * Initialize the module.
     *
     * @since 4.9.0
     */
    public function initialize(): void
    {
        // Register hooks and initialize module
    }
}
```

#### Creating a Controller
```php
<?php

/**
 * Controller for {feature} in the admin area.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future\Modules\FeatureName\Controllers;

use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;

defined('ABSPATH') or die('Direct access not allowed.');

class FeatureController implements InitializableInterface
{
    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * Constructor.
     *
     * @param HooksFacade $hooks
     *
     * @since 4.9.0
     */
    public function __construct(HooksFacade $hooks)
    {
        $this->hooks = $hooks;
    }

    /**
     * Initialize the controller.
     *
     * @since 4.9.0
     */
    public function initialize(): void
    {
        $this->hooks->addAction('admin_init', [$this, 'handleAdminInit']);
    }

    /**
     * Handle admin initialization.
     *
     * @since 4.9.0
     */
    public function handleAdminInit(): void
    {
        // Implementation
    }
}
```

#### Adding a REST Endpoint
- Create controller in module's `Controllers/` or `Rest/` directory
- Register routes using WordPress REST API
- Use namespace pattern: `/publishpress-future/v1/endpoint`
- Implement permission callbacks
- Validate and sanitize request parameters
- Return `WP_REST_Response` or `WP_Error`

#### Adding an Expiration Action
```php
<?php

/**
 * Expiration action for {action description}.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future\Modules\Expirator\ExpirationActions;

use PublishPress\Future\Modules\Expirator\Interfaces\ExpirationActionInterface;

defined('ABSPATH') or die('Direct access not allowed.');

class MyExpirationAction implements ExpirationActionInterface
{
    /**
     * Execute the action.
     *
     * @param int $postId
     * @param array $args
     *
     * @return bool
     *
     * @since 4.9.0
     */
    public function execute(int $postId, array $args = []): bool
    {
        // Implementation
        return true;
    }

    /**
     * Get the action label.
     *
     * @return string
     *
     * @since 4.9.0
     */
    public function getLabel(): string
    {
        return __('My Action', 'post-expirator');
    }
}
```

#### Creating a Database Schema
```php
<?php

/**
 * Database schema for {table name}.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future\Modules\ModuleName\DBTableSchemas;

use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;

defined('ABSPATH') or die('Direct access not allowed.');

class MyTableSchema implements DBTableSchemaInterface
{
    /**
     * Get the table name.
     *
     * @return string
     *
     * @since 4.9.0
     */
    public function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'my_table';
    }

    /**
     * Get the table schema.
     *
     * @return string
     *
     * @since 4.9.0
     */
    public function getSchema(): string
    {
        global $wpdb;
        $tableName = $this->getTableName();
        $charsetCollate = $wpdb->get_charset_collate();

        return "CREATE TABLE $tableName (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charsetCollate;";
    }
}
```

### Testing Commands
```bash
# Run all tests (Unit + Integration)
composer test:all

# Run specific test suite
composer test Unit
composer test Integration
composer test Acceptance
composer test EndToEnd

# Run specific test file
composer test Integration:Modules/Workflows/Domain/Engine/ExecutionContextTest

# Run tests with Xdebug
composer test:debug Integration

# Check all code quality (PHP compatibility, lint, code standards)
composer check

# Check coding standards
composer check:cs

# Fix coding standards
composer fix:cs
composer fix:php

# Check PHP compatibility (all versions)
composer check:php

# Check PHP compatibility for specific version
composer check:php-7.4
composer check:php-8.0
composer check:php-8.1

# Lint PHP files
composer check:lint

# Run static analysis
composer check:stan

# Check for long paths (Windows compatibility)
composer check:longpath

# Build JavaScript (production and development)
composer build:js

# Build JavaScript (production only)
composer build:js-prod

# Build JavaScript (development only)
composer build:js-dev

# Watch JavaScript for changes
composer watch:js

# Build language files
composer build:lang

# Build complete plugin package
composer build

# Build everything (JS + Lang + Package)
composer build:all
```

### Environment Commands
```bash
# Start development environment
composer dev:up

# Start test environment
composer test:up

# Start both environments
composer up

# Stop environments
composer dev:down
composer test:down
composer down

# Clean environments
composer dev:clean
composer test:clean

# Restart environments
composer dev:restart
composer test:restart

# View environment info
composer dev:info
composer test:info
```

### Database Commands
```bash
# Export test database
composer test:db-export

# Import test database
composer test:db-import path/to/dump.sql

# View database logs
composer test:db-logs
```

### WP-CLI Commands
```bash
# Run WP-CLI in development environment
composer wp:dev -- plugin list
composer wp:dev -- post list

# Run WP-CLI in test environment
composer wp:tests -- plugin list
composer wp:tests -- cache flush
```

### Security Checklist
- [ ] Nonce verification on form submissions
- [ ] Capability checks for admin actions
- [ ] Input sanitization using SanitizationFacade or WordPress functions
- [ ] Output escaping (`esc_html()`, `esc_attr()`, `esc_url()`)
- [ ] SQL prepared statements (through DatabaseFacade)
- [ ] No direct file access (`defined('ABSPATH') or die()` at top of file)
- [ ] No direct access to `$_GET`, `$_POST`, `$_REQUEST` (use RequestFacade)
- [ ] Proper permission callbacks on REST endpoints

### Quality Checklist
- [ ] Type hints on all functions (PHP 7.4 compatible)
- [ ] **File-level PHPDoc** on every new PHP file (@package, @author, @copyright, @license)
- [ ] **Method/function PHPDoc** on every method and function (description, @param, @return, @since)
- [ ] Error handling for edge cases
- [ ] Follows DRY principle
- [ ] Single responsibility per class
- [ ] Dependency injection used (through DI container)
- [ ] No hardcoded values (use constants in Abstract classes)
- [ ] WordPress functions accessed through Facades only
- [ ] Hook names defined as constants in HooksAbstract
- [ ] Follows established modular architecture
- [ ] Tests added for new functionality
- [ ] No direct instantiation of dependencies

### Architecture Checklist
- [ ] New classes added to appropriate layer (Core/Framework/Modules)
- [ ] Module implements ModuleInterface
- [ ] Controllers implement InitializableInterface
- [ ] Models follow repository pattern
- [ ] Database schemas implement DBTableSchemaInterface
- [ ] Migrations follow version naming (V{version}{description}.php)
- [ ] Constants grouped in Abstract classes
- [ ] Services registered in `services.php`
- [ ] Dependencies injected through constructor

## Output Format

The agent should:
1. Understand the requirements
2. Read relevant existing code
3. Implement changes following patterns
4. Test implementation
5. Report what was changed and why
6. Provide testing steps

## Examples

### Example 1: Add a New Expiration Action
**User:** `@coding-agent implement a new expiration action to send email notifications`

**Agent should:**
- Read existing expiration actions in `src/Modules/Expirator/ExpirationActions/`
- Create new action class implementing `ExpirationActionInterface`
- Add action constant to `ExpirationActionsAbstract`
- Register action in `ExpirationActionsModel`
- Add proper PHPDoc with @since tag
- Test the action with actual posts
- Add translations for action label
- Document the changes

### Example 2: Fix a Bug
**User:** `@coding-agent fix the issue where scheduled actions don't trigger on multisite`

**Agent should:**
- Investigate the issue in the Action Scheduler integration
- Read `CronToWooActionSchedulerAdapter` and related code
- Identify the multisite-specific problem
- Fix the bug (checking for proper blog switching)
- Test on multisite setup
- Ensure no regressions on single site
- Add integration tests
- Document the fix

### Example 3: Add a REST Endpoint
**User:** `@coding-agent add a REST endpoint to export scheduled actions as CSV`

**Agent should:**
- Read existing REST controllers in `src/Modules/Expirator/Controllers/`
- Create or extend REST controller
- Add endpoint `/publishpress-future/v1/actions/export`
- Implement CSV generation logic
- Add capability checks (manage_options or custom capability)
- Add nonce verification
- Validate query parameters
- Test the endpoint
- Add integration tests
- Document the changes

### Example 4: Refactor Code
**User:** `@coding-agent refactor the ExpirablePostModel to use facades instead of global functions`

**Agent should:**
- Analyze current implementation in `src/Modules/Expirator/Models/ExpirablePostModel.php`
- Identify direct WordPress function calls
- Replace with appropriate Facades (DatabaseFacade, HooksFacade, etc.)
- Update constructor to inject facades
- Update service registration in `services.php`
- Maintain same functionality
- Run existing tests to ensure no regressions
- Add unit tests with mocked facades
- Document the refactoring

### Example 5: Add a New Module
**User:** `@coding-agent create a new module for post analytics tracking`

**Agent should:**
- Create directory structure: `src/Modules/Analytics/`
- Create `Module.php` implementing `ModuleInterface`
- Create `HooksAbstract.php` for hook constants
- Create necessary subdirectories (Controllers, Models, etc.)
- Register module in `services.php`
- Add module initialization code
- Create basic controller for admin page
- Add tests in `tests/Unit/Modules/Analytics/`
- Document the new module
- Update CHANGELOG.md

## Notes

- **Always add proper PHPDoc** to every new class, method, and function (file header + method docblocks with @since)
- Always read existing code first to understand patterns and conventions
- Follow established modular architecture (Core → Framework → Modules → Views)
- Use dependency injection - never instantiate dependencies directly
- Never use global WordPress functions in business logic - always use Facades
- Define all hook names as constants in `HooksAbstract` classes
- Define all capabilities in `CapabilitiesAbstract` classes
- Define all post meta keys in `PostMetaAbstract` classes
- Use Action Scheduler for background tasks, not WP-Cron
- Test changes thoroughly with Codeception tests (Unit + Integration + Acceptance)
- Run code quality checks before finishing (`composer check`)
- Build JavaScript if JSX files changed (`composer build:js`)
- Build language files if translations added (`composer build:lang`)
- Update CHANGELOG.md following the established pattern
- Text domain is always `post-expirator`
- Namespace is always `PublishPress\Future`

## Related Files

- **Project rules**: `.cursor/rules/`
  - `commit-messages.mdc` - Commit message guidelines
  - `tests.mdc` - Testing guidelines
  - `use-coder-agent.mdc` - When to use the coder agent
- **Coding standards**: `.phpcs.xml`, `.phpcs-php-compatibility.xml`
- **Tests**: `tests/` directory
  - `tests/Unit/` - Unit tests
  - `tests/Integration/` - Integration tests
  - `tests/Acceptance/` - BDD acceptance tests
  - `tests/EndToEnd/` - End-to-end tests
- **Main plugin file**: `post-expirator.php`
- **Service definitions**: `services.php`
- **Codeception config**: `codeception.yml`
- **Docker environment**: `dev-workspace/docker/compose.yaml`
- **Build scripts**: `dev-workspace/scripts/`
- **Assets**: `assets/` (JSX/JS and CSS files)
- **Views**: `src/Views/` (global templates)
- **Language files**: `languages/`

## Key Facades to Use

Instead of calling WordPress functions directly, use these Facades:

- **HooksFacade**: `add_action()`, `add_filter()`, `do_action()`, `apply_filters()`
- **DatabaseFacade**: `$wpdb` operations, queries
- **OptionsFacade**: `get_option()`, `update_option()`, `delete_option()`
- **CronFacade**: WP-Cron scheduling (note: prefer Action Scheduler)
- **EmailFacade**: `wp_mail()`, email operations
- **DateTimeFacade**: Date/time functions
- **UsersFacade**: User-related functions, capability checks
- **SiteFacade**: Site-related functions
- **NoticeFacade**: Admin notices
- **RequestFacade**: Request parameter handling
- **SanitizationFacade**: Input sanitization
- **ErrorFacade**: Error handling

All facades are located in `src/Framework/WordPress/Facade/`
