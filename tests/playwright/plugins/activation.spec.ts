import { test, expect, type Page } from '../support/fixtures';

import { adminTags } from '../support/admin';
import { pluginSlug, wp } from '../support/wp-cli';

const pluginFile = `${pluginSlug()}/${pluginSlug()}.php`;

function pluginRow(page: Page) {
  return page.locator(`tr[data-plugin="${pluginFile}"]`);
}

test('plugin activates from the plugins list', { tag: adminTags }, async ({ page }) => {
  wp(['plugin', 'deactivate', pluginSlug()]);
  await page.goto('/wp-admin/plugins.php');
  await expect(pluginRow(page)).toHaveClass(/\binactive\b/);
  await pluginRow(page).locator('.activate a').click();
  await page.waitForLoadState('domcontentloaded');
  await page.goto('/wp-admin/plugins.php');
  await expect(pluginRow(page)).toHaveClass(/\bactive\b/);
  await expect(pluginRow(page)).not.toHaveClass(/\binactive\b/);
});

test('plugin deactivates from the plugins list', { tag: adminTags }, async ({ page }) => {
  await page.goto('/wp-admin/plugins.php');
  await expect(pluginRow(page)).not.toHaveClass(/\binactive\b/);
  await pluginRow(page).locator('.deactivate a').click();
  await page.waitForLoadState('domcontentloaded');
  await expect(pluginRow(page)).toHaveClass(/\binactive\b/);
});

test('activation redirects to Action Workflows', { tag: adminTags }, async ({ page }) => {
  wp(['plugin', 'deactivate', pluginSlug()]);
  await page.goto('/wp-admin/plugins.php');
  await pluginRow(page).locator('.activate a').click();
  await expect(page).toHaveURL(/\/wp-admin\/edit\.php\?post_type=ppfuture_workflow$/);
});
