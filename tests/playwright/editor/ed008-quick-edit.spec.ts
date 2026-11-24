import { test, expect } from '../support/fixtures';

import { editorTags } from '../support/admin';
import { createPost, setPostTypeDefaults, uniqueKey } from '../support/wp-cli';

test.beforeEach(() => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
});

test('quick edit shows Enable Future Action', { tag: editorTags }, async ({ page }) => {
  const post = createPost(uniqueKey('pw-quick'), 'post', 'draft');
  await page.goto(`/wp-admin/edit.php?s=${encodeURIComponent(post.slug)}`);
  const row = page.locator(`#post-${post.id}`);
  await row.hover();
  await row.locator('button.editinline').click();
  const quickEdit = page.locator(`#edit-${post.id} fieldset.post-expirator-quickedit`);
  await expect(quickEdit).toBeVisible();
  await expect(quickEdit).toContainText('Enable Future Action');
});
