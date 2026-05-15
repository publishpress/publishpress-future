---
name: coding-agent
description: Launch coding agent for PublishPress Future — features, bugs, refactors.
---
# Coding Agent Skill

**Communication:** Apply `/caveman` mode (full) to all responses and status updates when this skill is active. Drop articles/filler. Fragments OK. Technical terms exact. Code/commits/PR bodies stay normal unless user says otherwise.

## Purpose
Launch coding agent for PublishPress Future — conventions + best practices.

## When to Use
Features, bugs, refactors, functionality changes, standards updates, new/extended modules.

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

Guidelines when invoked:

### Project Context
PublishPress Future (Post Expirator) | text domain `post-expirator` | namespace `PublishPress\Future` | PHP 7.4+ / JSX | WP ≥6.7 | WPCS + PSR-12 | layered modules + DI | Action Scheduler (not WP-Cron).

### Coding Standards

#### PHP
PSR-12; `namespace PublishPress\Future\{Module}\{SubFolder};` | DI + type hints | PHPDoc required | **Never use** global WP functions in business logic — Facades only | escape/sanitize/nonces/caps | constants in `HooksAbstract`, `CapabilitiesAbstract`, `PostMetaAbstract`.

#### PHPDoc requirements (mandatory for all new code)
File: description, `@package`, `@author`, `@copyright`, `@license`. Methods: description, `@param`, `@return`, `@throws`, `@since`.

#### JavaScript/React
Functional components + JSX | `__('text', 'post-expirator')` | `formatNumber()` / `number_format_i18n()` | no emojis unless asked.

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
Read patterns → `composer check:cs` / `fix:cs` / `fix:php` → `check:stan` → `check:lint` → `composer test` → `build:js` if JSX → `build:lang` if i18n → docs if needed.

### Container Registration
`ServicesAbstract` constant → `services.php` → constructor injection → `InitializableInterface` auto-init.

### Module Creation
`src/Modules/{Name}/` + `Module.php` + `HooksAbstract.php` + standard subdirs → `services.php` + Unit/Integration tests.

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
Controller in `Controllers/` or `Rest/` | `/publishpress-future/v1/endpoint` | permissions + sanitize | `WP_REST_Response` / `WP_Error`.

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
- [ ] Nonces | caps | sanitize (SanitizationFacade/WP) | escape output | prepared SQL (DatabaseFacade) | `ABSPATH` guard | no raw `$_GET`/`$_POST`/`$_REQUEST` (RequestFacade) | REST permissions

### Quality Checklist
- [ ] PHP 7.4 type hints | file + method PHPDoc | edge-case errors | DRY | SRP | DI | Abstract constants | Facades only | HooksAbstract hooks | modular layout | tests | no `new` dependencies

### Architecture Checklist
- [ ] Correct layer (Core/Framework/Modules) | `ModuleInterface` | `InitializableInterface` controllers | repository models | `DBTableSchemaInterface` | `V{version}{description}` migrations | Abstract constants | `services.php` | constructor injection

## Output Format
Understand → read code → implement → test → report changes + testing steps.

## Examples

### Example 1: Add a New Expiration Action
**User:** `@coding-agent implement a new expiration action to send email notifications`

**Agent should:** read `ExpirationActions/` → new `ExpirationActionInterface` class → `ExpirationActionsAbstract` constant → `ExpirationActionsModel` register → PHPDoc `@since` → test on posts → i18n label → document.

## Notes
PHPDoc always | read code first | Core→Framework→Modules→Views | DI only | **Never use** WP globals in business logic — Facades | Abstract constants for hooks/caps/meta | Action Scheduler not WP-Cron | Codeception tests | `composer check` + build js/lang | CHANGELOG | `post-expirator` / `PublishPress\Future`.

## Related Files
`.cursor/rules/` | `.phpcs.xml` | `tests/` | `post-expirator.php` | `services.php` | `codeception.yml` | `dev-workspace/docker/compose.yaml` | `assets/` | `src/Views/` | `languages/`

## Key Facades to Use
`src/Framework/WordPress/Facade/`: HooksFacade, DatabaseFacade, OptionsFacade, CronFacade (prefer Action Scheduler), EmailFacade, DateTimeFacade, UsersFacade, SiteFacade, NoticeFacade, RequestFacade, SanitizationFacade, ErrorFacade.
