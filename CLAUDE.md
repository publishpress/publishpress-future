# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Building and Development
- `composer build` - Build the plugin (requires dev-workspace)
- `composer build:js` - Build JavaScript files (both prod and dev)
- `composer build:lang` - Generate language files (POT, MO, JSON, PHP)
- `composer watch:js` - Watch JS files for changes during development

### Development Environment
- `dev-workspace/run` - Enter the Docker development workspace
- `composer dev:up` - Start development environment
- `composer dev:down` - Stop development environment
- `composer dev:info` - Show development environment info

### Testing
- `composer test:up` - Start test containers
- `composer test:down` - Stop test containers
- `composer test Unit` - Run unit tests
- `composer test Integration` - Run integration tests
- `composer test:all` - Run all healthy tests (Unit + Integration)

### Code Quality
- `composer check:cs` - Check coding standards with PHPCS
- `composer check:stan` - Run PHPStan static analysis
- `composer check:lint` - Check PHP syntax
- `composer fix:cs` - Fix coding standards with PHPCBF
- `composer fix:php` - Fix PHP files (CS Fixer + PHPCBF)

## Architecture Overview

### Core Structure
- **Plugin Entry**: `post-expirator.php` - Main plugin file with namespace `PublishPress\Future`
- **Source Code**: `src/` directory contains all PHP classes
- **Assets**: `assets/jsx/` contains React/JSX components, `assets/js/` contains compiled JS

### Key Modules
- **Expirator**: Core expiration functionality (`src/Modules/Expirator/`)
- **Workflows**: Advanced workflow system (`src/Modules/Workflows/`)
- **Backup**: Backup and import/export functionality (`src/Modules/Backup/`)
- **Settings**: Configuration management (`src/Modules/Settings/`)

### Frontend Architecture
- React-based admin interface using WordPress components
- Webpack build system with separate entry points for different admin pages
- Uses WordPress externals (React, wp.components, wp.data, etc.)

### Data Layer
- Custom database tables with schema handlers
- WordPress post meta integration
- Action Scheduler for background processing
- Models in `src/Modules/*/Models/` directories

### Framework Components
- **DI Container**: Dependency injection system in `src/Core/DI/`
- **WordPress Facades**: Abstraction layer for WordPress functions in `src/Framework/WordPress/Facade/`
- **Logger**: Debug logging system with database storage
- **Migrations**: Database schema migration system

### Workflows System
The workflows module implements a visual node-based editor:
- **Triggers**: Events that start workflows (post publish, schedule, etc.)
- **Actions**: Operations to perform (change status, send email, etc.)
- **Data Types**: Strongly typed data flow between nodes
- **Engine**: Execution engine with safeguards and variable resolution

## Development Notes

### Docker Workflow
All development and testing happens inside Docker containers. Use `dev-workspace/run` to enter the development environment before running build commands.

### JavaScript Development
- JSX files in `assets/jsx/` are compiled to `assets/js/`
- Uses Babel for JSX compilation
- WordPress components are externalized to reduce bundle size
- Run `composer watch:js` during development

### Code Standards
- Follows WordPress coding standards via PHPCS
- PHPStan level 8 static analysis
- PHP 7.4+ compatibility required
- WordPress 6.7+ compatibility required

### Testing Environment
- Codeception for testing framework
- Separate test containers managed via composer scripts
- Unit and Integration tests are the primary test suites