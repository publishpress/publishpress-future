import { test, expect } from '../support/fixtures';

import {
  blockCheckbox,
  editorTags,
  enableFutureAction,
  openBlockEditor,
  openFuturePanel,
  saveBlockPost,
  setBlockTitle,
} from '../support/admin';
import { createPost, setPostTypeDefaults, uniqueKey } from '../support/wp-cli';

test('panel is visible when the post type is active', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
  await openBlockEditor(page, '/wp-admin/post-new.php');
  await expect(page.locator('.components-panel .post-expirator-panel')).toContainText('Future');
});

test('panel is hidden when the post type is inactive', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'inactive', autoEnable: '0' });
  await openBlockEditor(page, '/wp-admin/post-new.php');
  await expect(page.locator('.components-panel .post-expirator-panel')).toHaveCount(0);
});

test('Enable Future Action is unchecked when auto-enable is off', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
  await openBlockEditor(page, '/wp-admin/post-new.php');
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).not.toBeChecked();
});

test('enabling Future Action on a new post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
  await openBlockEditor(page, '/wp-admin/post-new.php');
  await setBlockTitle(page, uniqueKey('pw-block-new'));
  await openFuturePanel(page);
  await enableFutureAction(page, '.post-expirator-panel');
  await saveBlockPost(page);
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).toBeChecked();
});

test('enabling Future Action on an existing post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
  const post = createPost(uniqueKey('pw-block-existing'), 'post', 'draft');
  await openBlockEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await openFuturePanel(page);
  await enableFutureAction(page, '.post-expirator-panel');
  await saveBlockPost(page);
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).toBeChecked();
});

test('Enable Future Action is checked when auto-enable is on', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '1', expireType: 'draft' });
  await openBlockEditor(page, '/wp-admin/post-new.php');
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).toBeChecked();
});

test('disabling Future Action on a new post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '1', expireType: 'draft' });
  await openBlockEditor(page, '/wp-admin/post-new.php');
  await setBlockTitle(page, uniqueKey('pw-block-off'));
  await openFuturePanel(page);
  await blockCheckbox(page).uncheck();
  await saveBlockPost(page);
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).not.toBeChecked();
});

test('disabling Future Action on an existing post persists', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '1', expireType: 'draft' });
  const post = createPost(uniqueKey('pw-block-existing-off'), 'post', 'draft');
  await openBlockEditor(page, `/wp-admin/post.php?post=${post.id}&action=edit`);
  await openFuturePanel(page);
  if (!await blockCheckbox(page).isChecked()) {
    await enableFutureAction(page, '.post-expirator-panel');
    await saveBlockPost(page);
    await openFuturePanel(page);
  }
  await blockCheckbox(page).uncheck();
  await saveBlockPost(page);
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).not.toBeChecked();
});

test('Enable Future Action is checked for a new music post when auto-enable is on', { tag: editorTags }, async ({ page }) => {
  setPostTypeDefaults('music', { activeMetaBox: 'active', autoEnable: '1', expireType: 'draft' });
  await openBlockEditor(page, '/wp-admin/post-new.php?post_type=music');
  await openFuturePanel(page);
  await expect(blockCheckbox(page)).toBeChecked();
});
