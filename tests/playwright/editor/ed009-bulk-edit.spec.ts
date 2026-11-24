import { test, expect, type Page } from '../support/fixtures';

import { editorTags, setNoonDaysFromNow } from '../support/admin';
import {
  createPost,
  scheduleDraftExpiration,
  setPostTypeDefaults,
  uniqueKey,
  type CreatedPost,
} from '../support/wp-cli';

test.beforeEach(() => {
  setPostTypeDefaults('post', { activeMetaBox: 'active', autoEnable: '0' });
});

function makePosts(prefix: string, count: number): { batch: string; posts: CreatedPost[] } {
  const batch = uniqueKey(prefix);

  return {
    batch,
    posts: Array.from({ length: count }, (_, index) => createPost(`${batch}-${index}`, 'post', 'draft')),
  };
}

async function openBulkEdit(page: Page, batch: string, posts: CreatedPost[]): Promise<void> {
  await page.goto(`/wp-admin/edit.php?s=${encodeURIComponent(batch)}`);
  for (const post of posts) {
    await page.locator(`#cb-select-${post.id}`).check();
  }
  await page.locator('#bulk-action-selector-top').selectOption('edit');
  await page.locator('#doaction').click();
  await expect(page.locator('#bulk-edit fieldset.post-expirator-quickedit')).toBeVisible();
}

async function chooseStrategy(page: Page, value: string): Promise<void> {
  await page.locator('#bulk-edit select[name="future_action_bulk_change_action"]').selectOption(value);
}

async function updateBulk(page: Page): Promise<void> {
  await page.locator('#bulk_edit').click();
  await expect(page.locator('#bulk-edit')).toBeHidden();
}

async function expectNotScheduled(page: Page, postId: number): Promise<void> {
  await expect(page.locator(`#post-${postId} .post-expire-col`)).toHaveAttribute('data-action-enabled', '0');
}

test('bulk edit shows future action fields', { tag: editorTags }, async ({ page }) => {
  const { batch, posts } = makePosts('pw-bulk-see', 3);
  await openBulkEdit(page, batch, posts);
  await expect(page.locator('#bulk-edit select[name="future_action_bulk_change_action"]')).toBeVisible();
});

test('change on posts does not add expiration to posts without one', { tag: editorTags }, async ({ page }) => {
  const { batch, posts } = makePosts('pw-bulk-empty', 3);
  await openBulkEdit(page, batch, posts);
  await chooseStrategy(page, 'change-only');
  await setNoonDaysFromNow(page, '#bulk-edit', 1);
  await updateBulk(page);
  for (const post of posts) {
    await expectNotScheduled(page, post.id);
  }
});

test('change on posts updates posts that already expire', { tag: editorTags }, async ({ page }) => {
  const { batch, posts } = makePosts('pw-bulk-change', 3);
  scheduleDraftExpiration(posts[0].id, 7);
  scheduleDraftExpiration(posts[1].id, 7);
  await openBulkEdit(page, batch, posts);
  await chooseStrategy(page, 'change-only');
  const tomorrow = await setNoonDaysFromNow(page, '#bulk-edit', 1);
  await updateBulk(page);
  await expect(page.locator(`#post-${posts[0].id} .post-expire-col`)).toContainText(tomorrow.formatted);
  await expect(page.locator(`#post-${posts[1].id} .post-expire-col`)).toContainText(tomorrow.formatted);
  await expectNotScheduled(page, posts[2].id);
});

test('add to posts only expires posts that had no expiration', { tag: editorTags }, async ({ page }) => {
  const { batch, posts } = makePosts('pw-bulk-add', 3);
  const first = scheduleDraftExpiration(posts[0].id, 7);
  const second = scheduleDraftExpiration(posts[1].id, 7);
  await openBulkEdit(page, batch, posts);
  await chooseStrategy(page, 'add-only');
  const tomorrow = await setNoonDaysFromNow(page, '#bulk-edit', 1);
  await updateBulk(page);
  await expect(page.locator(`#post-${posts[0].id} .post-expire-col`)).toContainText(first.formatted);
  await expect(page.locator(`#post-${posts[1].id} .post-expire-col`)).toContainText(second.formatted);
  await expect(page.locator(`#post-${posts[2].id} .post-expire-col`)).toContainText(tomorrow.formatted);
});

test('change and add updates every selected post', { tag: editorTags }, async ({ page }) => {
  const { batch, posts } = makePosts('pw-bulk-both', 3);
  scheduleDraftExpiration(posts[0].id, 7);
  scheduleDraftExpiration(posts[1].id, 7);
  await openBulkEdit(page, batch, posts);
  await chooseStrategy(page, 'change-add');
  const tomorrow = await setNoonDaysFromNow(page, '#bulk-edit', 1);
  await updateBulk(page);
  for (const post of posts) {
    await expect(page.locator(`#post-${post.id} .post-expire-col`)).toContainText(tomorrow.formatted);
  }
});

test('remove from posts clears expiration', { tag: editorTags }, async ({ page }) => {
  const { batch, posts } = makePosts('pw-bulk-remove', 3);
  scheduleDraftExpiration(posts[0].id, 7);
  scheduleDraftExpiration(posts[1].id, 7);
  await openBulkEdit(page, batch, posts);
  await chooseStrategy(page, 'remove-only');
  await updateBulk(page);
  for (const post of posts) {
    await expectNotScheduled(page, post.id);
  }
});
