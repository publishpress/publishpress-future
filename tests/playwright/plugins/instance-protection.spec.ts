import { test, expect } from '../support/fixtures';

import { adminTags } from '../support/admin';
import { installPluginCopy, pluginSlug, removePluginCopy, wp } from '../support/wp-cli';

const copyFolder = `${pluginSlug()}-2`;
const copyFile = `${copyFolder}/${pluginSlug()}.php`;
const pluginFile = `${pluginSlug()}/${pluginSlug()}.php`;

// The plugin copy lives in wp-content/plugins, which every pool site shares.
test.describe.configure({ mode: 'serial' });

test.afterAll(() => {
  removePluginCopy(copyFolder);
});

test('no warning when the plugin is not duplicated', { tag: adminTags }, async ({ page }) => {
  removePluginCopy(copyFolder);
  await page.goto('/wp-admin/plugins.php');
  await expect(page.locator(`tr[data-plugin="${pluginFile}"]`)).toBeVisible();
  await expect(page.locator('.multiple-instances-warning')).toHaveCount(0);
});

test('warning when a copy is installed in a non-standard folder', { tag: adminTags }, async ({ page }) => {
  installPluginCopy(copyFolder);
  await page.goto('/wp-admin/plugins.php');
  await expect(page.locator('.multiple-instances-warning', {
    hasText: `This plugin is not installed in the standard folder. The current path is ${copyFile} but it is expected to be ${pluginFile}`,
  })).toBeVisible();
});

test('notice when the copy is activated too', { tag: adminTags }, async ({ page }) => {
  installPluginCopy(copyFolder);
  wp(['plugin', 'activate', copyFile]);
  await page.goto('/wp-admin/plugins.php');
  await expect(page.locator('.notice', {
    hasText: 'You have activated multiple instances of PublishPress Future Free. Please keep only one activated and remove the others.',
  })).toBeVisible();
});

test('warning when the copy is outdated', { tag: adminTags }, async ({ page }) => {
  installPluginCopy(copyFolder, '0.0.1');
  await page.goto('/wp-admin/plugins.php');
  await expect(page.locator('.multiple-instances-warning', {
    hasText: 'This plugin is outdated. You already have a more recent version installed. Please remove this version.',
  })).toBeVisible();
});
