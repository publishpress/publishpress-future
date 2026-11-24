import { test as base } from '@playwright/test';

import type { AdminCookie } from '../global-setup';
import type { PoolSite } from './pool';
import { setSiteUrl } from './wp-cli';

export { expect } from '@playwright/test';

function poolUrl(): string {
  const url = process.env.PW_POOL_URL;

  if (!url) {
    throw new Error('PW_POOL_URL is not set. Run the specs through playwright.config.ts so the global setup starts the site pool.');
  }

  return url;
}

async function leaseSite(): Promise<PoolSite> {
  const response = await fetch(`${poolUrl()}/lease`, { method: 'POST' });

  if (!response.ok) {
    throw new Error(`Site pool lease failed: ${response.status} ${await response.text()}`);
  }

  return await response.json() as PoolSite;
}

async function releaseSite(site: PoolSite, keep: boolean): Promise<void> {
  await fetch(`${poolUrl()}/release`, {
    method: 'POST',
    body: JSON.stringify({ name: site.name, keep }),
  });
}

function adminStorageState(site: PoolSite) {
  const cookies = JSON.parse(process.env.PW_POOL_ADMIN_COOKIES ?? '[]') as AdminCookie[];

  return {
    cookies: cookies.map((cookie) => ({
      ...cookie,
      domain: new URL(site.url).hostname,
      path: '/',
      httpOnly: true,
      secure: false,
      sameSite: 'Lax' as const,
    })),
    origins: [],
  };
}

/**
 * Every test runs on its own clean WordPress database, leased from the site pool.
 * baseURL, the admin session and WP-CLI helpers all point at that site.
 */
export const test = base.extend<{ site: PoolSite }>({
  site: [async ({}, use, testInfo) => {
    const site = await leaseSite();
    setSiteUrl(site.url);
    testInfo.annotations.push({ type: 'site', description: site.url });

    try {
      await use(site);
    } finally {
      setSiteUrl(undefined);
      await releaseSite(site, testInfo.status !== testInfo.expectedStatus);
    }
  }, { auto: true }],
  baseURL: async ({ site }, use) => {
    await use(site.url);
  },
  storageState: async ({ site }, use) => {
    await use(adminStorageState(site));
  },
});
