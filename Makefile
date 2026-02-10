.PHONY: dev-up


help:
	@echo "Usage: make <target>"
	@echo "Targets:"
	@echo "  dev-up         # Start the development environment"
	@echo "  dev-stop       # Stop the development environment"
	@echo "  dev-down       # Remove the development environment"
	@echo "  dev-restart    # Restart the development environment"
	@echo "  dev-refresh    # Refresh the development environment"
	@echo "  dev-info       # Show info about the development environment"
	@echo "  dev-logs       # Show logs for the development environment"
	@echo "  dev-clean      # Clean up dev environment, logs, and cache"
	@echo "  test-up        # Start the test environment"
	@echo "  test-stop      # Stop the test environment"
	@echo "  test-down      # Remove the test environment"
	@echo "  test-restart   # Restart the test environment"
	@echo "  test-refresh   # Refresh the test environment"
	@echo "  test-info      # Show info about the test environment"
	@echo "  test-logs      # Show logs for the test environment"
	@echo "  test-clean     # Clean up test environment, logs, and cache"
	@echo "  test-db-export # Export the test database"
	@echo "  test-db-import # Import the test database"
	@echo "  test-db-logs   # Show logs for the test database"
	@echo "  test-snippets  # Generate Gherkin snippets"
	@echo "  test-steps     # Generate Gherkin steps"
	@echo "  test-coverage  # Generate test coverage report"
	@echo "  test-unit      # Run unit tests"
	@echo "  test-integration # Run integration tests"
	@echo "  test-api       # Run API tests"
	@echo "  test-debug     # Run tests in debug mode"
	@echo "  test-driver    # Run test driver"
	@echo "  test           # Run tests"
	@echo "  test-coverage  # Generate test coverage report"
	@echo "  test-unit      # Run unit tests"
	@echo "  test-integration # Run integration tests"
	@echo "  test-api       # Run API tests"
	@echo "  test-debug     # Run tests in debug mode"
	@echo "  docker-cleanup # Clean up Docker"
	@echo "  cli-dev        # Run WP-CLI commands in the development environment"
	@echo "  cli-test       # Run WP-CLI commands in the test environment"
	@echo "  up             # Start the development and test environments"
	@echo "  down           # Stop the development and test environments"
	@echo "  stop           # Stop the development and test environments"
	@echo "  refresh        # Refresh the development and test environments"
	@echo "  clean          # Clean up the development and test environments"
	@echo "  info           # Show info about the development and test environments"
	@echo "  logs           # Show logs for the development and test environments"
	@echo "  sync           # Start watching and syncing plugin files to dev site"
	@echo "  sync-once      # Sync plugin files once to dev site"
	@echo "  sync-stop      # Stop the sync process"
	@echo "  watch          # Watch for changes in the assets and build assets using webpack"


dev-up:
	composer dev:up

dev-stop:
	composer dev:stop

dev-down:
	composer dev:down

dev-restart:
	composer dev:restart

dev-refresh:
	composer dev:refresh

dev-info:
	composer dev:info

dev-logs:
	composer dev:logs

dev-clean:
	composer dev:clean

dev-clean-logs:
	composer dev:clean-logs

dev-clean-cache:
	composer dev:clean-cache

test-up:
	composer test:up

test-stop:
	composer test:stop

test-down:
	composer test:down

test-restart:
	composer test:restart

test-refresh:
	composer test:refresh

test-info:
	composer test:info

test-clean:
	composer test:clean

test-clean-logs:
	composer test:clean-logs

test-clean-cache:
	composer test:clean-cache

test-db-export:
	composer test:db-export $@

test-db-import:
	composer test:db-import $@

test-db-logs:
	composer test:db-logs $@

test-snippets:
	composer test:snippets $@

test-steps:
	composer test:steps $@

test-logs:
	composer test:logs

test:
	composer test $@

test-coverage:
	composer test:coverage

test-unit:
	composer test Unit

test-integration:
	composer test Integration

test-acceptance:
	composer test Acceptance

test-end-to-end:
	composer test EndToEnd

test-debug:
	composer test:debug $@

test-driver:
	composer test:driver

cli-dev:
	composer wp:dev $@

cli-test:
	composer wp:tests $@

docker-cleanup:
	docker system prune -f

composer-update:
	@./dev-workspace/run composer update

composer-update-lib:
	@./dev-workspace/run composer update --working-dir=./lib

up:
	@make dev-up
	@make test-up
	@make dev-info
	@make test-info

info:
	@make dev-info
	@make test-info

stop:
	@make dev-stop
	@make test-stop

down:
	@make dev-down
	@make test-down

refresh:
	@make dev-refresh
	@make test-refresh

clean:
	@make dev-clean
	@make test-clean

logs:
	@make dev-logs
	@make test-logs

# Plugin sync commands
sync:
	@echo "Starting plugin sync (watching for changes)..."
	@echo "Press Ctrl+C to stop"
	@./dev-workspace/scripts/sync-plugin-remote.sh --watch

sync-once:
	@echo "Syncing plugin files once..."
	@./dev-workspace/scripts/sync-plugin-remote.sh --once

sync-stop:
	@echo "Stopping sync process..."
	@pkill -f "sync-plugin-remote.sh" || true

local-sync:
	@echo "Syncing plugin files locally..."
	@./dev-workspace/scripts/local-sync.sh --watch

local-sync-once:
	@echo "Syncing plugin files locally once..."
	@./dev-workspace/scripts/local-sync.sh --once

local-sync-stop:
	@echo "Stopping local sync process..."
	@pkill -f "local-sync.sh" || true

# Dev-workspace commands

watch:
	./dev-workspace/run composer watch:js

build-js:
	./dev-workspace/run composer build:js

build:
	./dev-workspace/run composer build

build-lang:
	./dev-workspace/run composer build:lang

# Code quality checks

check:
	./dev-workspace/run composer check

check-lint:
	./dev-workspace/run composer check:lint

check-php:
	./dev-workspace/run composer check:php

check-cs:
	./dev-workspace/run composer check:cs

# Pre-commit hooks
pre-commit-check:
	@make check
	@make test-unit
	@make test-integration

# Metrics

metrics:
	./dev-workspace/run composer metrics

# Git
git-development-pull:
	git checkout development
	git pull
