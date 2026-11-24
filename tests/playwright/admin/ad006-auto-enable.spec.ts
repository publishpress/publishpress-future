import { test, expect } from '../support/fixtures';

import {
  adminTags,
  blockCheckbox,
  openBlockEditor,
  openFuturePanel,
  openPostTypeSettings,
  savePostTypeSettings,
} from '../support/admin';
import { createPost, setPostTypeDefaults, uniqueKey } from '../support/wp-cli';

const postTypes = ['post', 'page', 'music'];

function editorPath(postType: string): string {
  return postType === 'post'
    ? '/wp-admin/post-new.php'
    : `/wp-admin/post-new.php?post_type=${postType}`;
}

for (const postType of postTypes) {
  test(`auto-enable is saved when activated for ${postType}`, { tag: adminTags }, async ({ page }) => {
    setPostTypeDefaults(postType, { activeMetaBox: 'active', autoEnable: '0' });
    await openPostTypeSettings(page, postType);
    await page.locator(`#expirationdate_autoenable-${postType}`).check();
    await savePostTypeSettings(page);
    await openPostTypeSettings(page, postType);
    await expect(page.locator(`#expirationdate_autoenable-${postType}`)).toBeChecked();
  });

  test(`auto-enable is saved when deactivated for ${postType}`, { tag: adminTags }, async ({ page }) => {
    setPostTypeDefaults(postType, { activeMetaBox: 'active', autoEnable: '1' });
    await openPostTypeSettings(page, postType);
    await page.locator(`#expirationdate_autoenable-${postType}`).uncheck();
    await savePostTypeSettings(page);
    await openPostTypeSettings(page, postType);
    await expect(page.locator(`#expirationdate_autoenable-${postType}`)).not.toBeChecked();
  });

  test(`auto-enable checks Enable Future Action for a new ${postType}`, { tag: adminTags }, async ({ page }) => {
    createPost(uniqueKey('pw-auto-on'), postType, 'draft');
    setPostTypeDefaults(postType, { activeMetaBox: 'active', autoEnable: '0' });
    await openPostTypeSettings(page, postType);
    await page.locator(`#expirationdate_autoenable-${postType}`).check();
    await savePostTypeSettings(page);
    await openBlockEditor(page, editorPath(postType));
    await openFuturePanel(page);
    await expect(blockCheckbox(page)).toBeChecked();
  });

  test(`auto-enable leaves Enable Future Action unchecked for a new ${postType}`, { tag: adminTags }, async ({ page }) => {
    createPost(uniqueKey('pw-auto-off'), postType, 'draft');
    setPostTypeDefaults(postType, { activeMetaBox: 'active', autoEnable: '1' });
    await openPostTypeSettings(page, postType);
    await page.locator(`#expirationdate_autoenable-${postType}`).uncheck();
    await savePostTypeSettings(page);
    await openBlockEditor(page, editorPath(postType));
    await openFuturePanel(page);
    await expect(blockCheckbox(page)).not.toBeChecked();
  });
}
