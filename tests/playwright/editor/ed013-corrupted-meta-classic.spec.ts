import { test, expect } from '../support/fixtures';

import { classicCheckbox, editorTags, openClassicEditor } from '../support/admin';
import { createPost, setPostTypeDefaults, uniqueKey, updatePostMeta } from '../support/wp-cli';

test.beforeEach(() => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
});

test('saved status without an expiration date leaves the classic checkbox unchecked', { tag: editorTags }, async ({ page }) => {
  const post = createPost(uniqueKey('pw-corrupt-classic'), 'post', 'draft');
  updatePostMeta(post.id, '_expiration-date-status', 'saved');
  await openClassicEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await expect(classicCheckbox(page)).not.toBeChecked();
});

test('status 0 leaves the classic checkbox unchecked', { tag: editorTags }, async ({ page }) => {
  const post = createPost(uniqueKey('pw-corrupt-classic-zero'), 'post', 'draft');
  updatePostMeta(post.id, '_expiration-date-status', '0');
  await openClassicEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await expect(classicCheckbox(page)).not.toBeChecked();
});
