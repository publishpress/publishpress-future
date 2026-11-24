import { test, expect } from '../support/fixtures';

import { adminTags } from '../support/admin';
import { ensureUser, uniqueKey } from '../support/wp-cli';

test('administrator sees the Future admin menu', { tag: adminTags }, async ({ page }) => {
  await page.goto('/wp-admin/');
  await expect(page.locator('#toplevel_page_publishpress-future')).toContainText('Future');
});

test('subscriber does not see the Future admin menu', { tag: adminTags }, async ({ browser }) => {
  const login = uniqueKey('pw-sub');
  ensureUser(login, 'subscriber', login);

  const context = await browser.newContext({ storageState: { cookies: [], origins: [] } });
  const page = await context.newPage();

  try {
    await page.goto('/wp-login.php');
    await page.locator('#user_login').fill(login);
    await page.locator('#user_pass').fill(login);
    await page.locator('#wp-submit').click();
    await page.waitForLoadState('domcontentloaded');

    if (/\/wp-login\.php(?:[?#]|$)/.test(page.url())) {
      throw new Error('Subscriber login did not complete.');
    }

    await page.goto('/wp-admin/');
    await expect(page.locator('#toplevel_page_publishpress-future')).toHaveCount(0);
  } finally {
    await context.close();
  }
});
