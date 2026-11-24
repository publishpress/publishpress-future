import { test, expect } from '../support/fixtures';

import { blockCheckbox, editorTags, openBlockEditor, openFuturePanel } from '../support/admin';
import { createPost, setPostTypeDefaults, uniqueKey, updatePostMeta } from '../support/wp-cli';

test.beforeEach(() => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
});

test('saved status without an expiration date leaves the checkbox unchecked', { tag: editorTags }, async ({ page }) => {
  const post = createPost(uniqueKey('pw-corrupt-block'), 'post', 'draft');
  updatePostMeta(post.id, '_expiration-date-status', 'saved');
  await openBlockEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).not.toBeChecked();
});

test('status 0 leaves the checkbox unchecked', { tag: editorTags }, async ({ page }) => {
  const post = createPost(uniqueKey('pw-corrupt-block-zero'), 'post', 'draft');
  updatePostMeta(post.id, '_expiration-date-status', '0');
  await openBlockEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).not.toBeChecked();
});
