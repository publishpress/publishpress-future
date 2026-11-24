import { test, expect } from '../support/fixtures';

import {
  classicCheckbox,
  editorTags,
  enableFutureAction,
  openClassicEditor,
  saveClassicPost,
  setNoonDaysFromNow,
} from '../support/admin';
import { createPost, setPostTypeDefaults, uniqueKey } from '../support/wp-cli';

test('Enable Future Action is unchecked when auto-enable is off', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
  await openClassicEditor(page, '/wp-admin/post-new.php');
  await expect(classicCheckbox(page)).not.toBeChecked();
});

test('enabling Future Action on a new classic post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
  await openClassicEditor(page, '/wp-admin/post-new.php');
  await page.locator('#title').fill(uniqueKey('pw-classic-new'));
  await enableFutureAction(page, '#expirationdatediv');
  await setNoonDaysFromNow(page, '#expirationdatediv', 1);
  await saveClassicPost(page);
  await expect(classicCheckbox(page)).toBeChecked();
});

test('enabling Future Action on an existing classic post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
  const post = createPost(uniqueKey('pw-classic-existing'), 'post', 'draft');
  await openClassicEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await enableFutureAction(page, '#expirationdatediv');
  await setNoonDaysFromNow(page, '#expirationdatediv', 1);
  await saveClassicPost(page);
  await expect(classicCheckbox(page)).toBeChecked();
});

test('Enable Future Action is checked when auto-enable is on', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '1', expireType: 'draft' });
  await openClassicEditor(page, '/wp-admin/post-new.php');
  await expect(classicCheckbox(page)).toBeChecked();
});

test('disabling Future Action on a new classic post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '1', expireType: 'draft' });
  await openClassicEditor(page, '/wp-admin/post-new.php');
  await page.locator('#title').fill(uniqueKey('pw-classic-off'));
  await classicCheckbox(page).uncheck();
  await saveClassicPost(page);
  await expect(classicCheckbox(page)).not.toBeChecked();
});

test('disabling Future Action on an existing classic post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '1', expireType: 'draft' });
  const post = createPost(uniqueKey('pw-classic-existing-off'), 'post', 'draft');
  await openClassicEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await classicCheckbox(page).uncheck();
  await saveClassicPost(page);
  await expect(classicCheckbox(page)).not.toBeChecked();
});
