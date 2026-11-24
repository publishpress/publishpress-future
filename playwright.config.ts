import { existsSync } from 'fs';
import { availableParallelism } from 'os';

import { defineConfig, devices } from '@playwright/test';

import { loadDotEnv } from './tests/playwright/support/load-dotenv';

loadDotEnv();

const baseURL = (process.env.WP_TESTS_URL ?? 'http://localhost:8011').replace(/\/$/, '');
const chromiumExecutableCandidates = [
  process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH,
  '/usr/bin/chromium',
  '/usr/bin/chromium-browser',
].filter(Boolean) as string[];
const chromiumExecutablePath = chromiumExecutableCandidates.find((candidate) => existsSync(candidate));
const chromiumLaunchOptions = chromiumExecutablePath ? { executablePath: chromiumExecutablePath } : {};
const hasChromiumLaunchOptions = Object.keys(chromiumLaunchOptions).length > 0;
const chromiumUse = {
  ...devices['Desktop Chrome'],
  ...(hasChromiumLaunchOptions ? { launchOptions: chromiumLaunchOptions } : {}),
};

function resolveWorkers(): number {
  const raw = process.env.PW_WORKERS;

  if (raw !== undefined && raw !== '') {
    const parsed = Number.parseInt(raw, 10);

    if (Number.isFinite(parsed) && parsed > 0) {
      return parsed;
    }
  }

  // Each test gets its own database from the site pool, so parallelism is bound by CPU only.
  return Math.min(10, Math.max(1, Math.floor(availableParallelism() / 2)));
}

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: './tests/playwright',
  globalSetup: './tests/playwright/global-setup.ts',
  fullyParallel: true,
  workers: resolveWorkers(),
  reporter: process.env.CI
    ? [['list'], ['html', { open: 'never' }]]
    : [['list'], ['html', { open: 'on-failure' }]],
  timeout: 60_000,
  expect: {
    timeout: 30_000,
  },
  use: {
    baseURL,
    ignoreHTTPSErrors: true,
    headless: process.env.PW_HEADED === '1' ? false : true,
    screenshot: 'only-on-failure',
    viewport: { width: 1366, height: 900 },
  },
  projects: [
    {
      name: 'acceptance',
      testMatch: /.*\.spec\.ts/,
      use: chromiumUse,
    },
  ],
});
