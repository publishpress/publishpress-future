import { test, expect } from '../support/fixtures';

import { adminTags, openBlockEditor, openPostTypeSettings, savePostTypeSettings } from '../support/admin';
import { setPostTypeDefaults } from '../support/wp-cli';

const postTypes = ['post', 'page', 'music'];

for (const postType of postTypes) {
  test(`deactivate metabox for ${postType}`, { tag: adminTags }, async ({ page }) => {
    setPostTypeDefaults(postType, { activeMetaBox: 'active' });
    await openPostTypeSettings(page, postType);
    await page.locator(`#expirationdate_activemeta-${postType}`).uncheck();
    await savePostTypeSettings(page);
    await openPostTypeSettings(page, postType);
    await expect(page.locator(`#expirationdate_activemeta-${postType}`)).not.toBeChecked();

    const editorPath = postType === 'post'
      ? '/wp-admin/post-new.php'
      : `/wp-admin/post-new.php?post_type=${postType}`;
    await openBlockEditor(page, editorPath);
    await expect(page.locator('.components-panel .post-expirator-panel')).toHaveCount(0);
  });

  test(`activate metabox for ${postType}`, { tag: adminTags }, async ({ page }) => {
    setPostTypeDefaults(postType, { activeMetaBox: 'inactive' });
    await openPostTypeSettings(page, postType);
    await page.locator(`#expirationdate_activemeta-${postType}`).check();
    await savePostTypeSettings(page);
    await openPostTypeSettings(page, postType);
    await expect(page.locator(`#expirationdate_activemeta-${postType}`)).toBeChecked();

    const editorPath = postType === 'post'
      ? '/wp-admin/post-new.php'
      : `/wp-admin/post-new.php?post_type=${postType}`;
    await openBlockEditor(page, editorPath);
    await expect(page.locator('.components-panel .post-expirator-panel')).toContainText('Future');
  });
}
