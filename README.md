# PublishPress-Future

[![VIP Scans and other code checks](https://github.com/publishpress/PublishPress-Future/actions/workflows/code-check.yml/badge.svg)](https://github.com/publishpress/PublishPress-Future/actions/workflows/code-check.yml) [![Unit and Integration Tests](https://github.com/publishpress/PublishPress-Future/actions/workflows/code-test.yml/badge.svg?branch=develop)](https://github.com/publishpress/PublishPress-Future/actions/workflows/code-test.yml)

## How to install?

You can download a built package from [releases page](/releases/) and install it on your WordPress sites by uploading the zip file.

## How to build a package?

### Requirements

 - Docker

### Building from inside the dev-workspace

To enter the development workspace, run the following command from the root of the repository:

```bash
dev-workspace/run
```

From inside the dev-workspace you can run the build or any other composer script. Check `composer help` for a list of available commands.

```bash
composer update
composer build
```

### Building JS scripts

```bash
composer build:js
```

### Building Language files

```bash
composer build:lang
```

### Building without entering the dev-workspace

```bash
dev-workspace/run composer build
```

## Testing

### Requirements

 - Docker
 - PHP
 - Composer

Tests will run inside docker containers, but all the commands below should be executed outside the dev-workspace.

### Starting the test containers

Before running any test you need to start the test container:

```bash
composer test:up
```

For stopping, you can use:

```bash
composer test:down
```
Test files will be cached inside the `dev-workspace-cache` directory.

### Running all tests

Only Unit and Integration tests are healthy for now. You might find other types of tests but they need refactoring.

```bash
composer test:all
```

### Running Unit tests

```bash
composer test:unit
```

Unit tests run serially by default. To split them across parallel workers (Codeception `--shard`), set `UNIT_WORKERS` in `.env` or inline:

```bash
UNIT_WORKERS=4 composer test:unit
```

Passing a test path, `--filter`, `--group`, or coverage flags forces a serial run. You can also run the suite directly with `composer test Unit`.

### Running Integration tests

```bash
composer test:integration
```

On Linux this first fixes ownership on the test Docker volumes, then runs the suite. Parallel workers use `INTEGRATION_WORKERS`, but all shards share one WordPress and database, so keep it at 1 unless you are experimenting. You can also run the suite directly with `composer test Integration`.

### Browser acceptance (Playwright)

After `composer test:up`, install the browser once:

```bash
node ./node_modules/@playwright/test/cli.js install chromium
composer test:e2e
```

Headless by default. Set `PW_HEADED=1` in `.env` to show the browser, or run `composer test:e2e:headed`.

Every test runs on its own WordPress database, so tests run in parallel. `PW_WORKERS` defaults to half the CPU cores (at most 10). See [Site pool](docs/dev/testing/e2e-specs.md#site-pool).

Plugin activation and instance protection are Playwright specs too (`tests/playwright/plugins/`). Codeception runs only the Unit and Integration suites.

You also can run specific test file:

```bash
composer test:unit tests/codeception/Unit/Core/PathsTest.php
```

And for running only a specific test:

```bash
composer test:unit tests/codeception/Unit/Core/PathsTest.php:testGetBaseDirPathReturnsNoTrailingSlash
```

You can run any codeception command by executing:

```bash
composer codecept <commands>
```

## License

License: [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)
