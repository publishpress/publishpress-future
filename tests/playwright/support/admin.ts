import { expect, type Locator, type Page } from '@playwright/test';

import {
  ensureBlockEditorPreferred,
  ensureClassicEditor,
  noonPartsDaysFromNow,
  type NoonParts,
} from './wp-cli';

const acceptanceTags = ['@acceptance'] as const;

export const adminTags = [...acceptanceTags, '@admin'];
export const editorTags = [...acceptanceTags, '@editor'];
export const publicTags = [...acceptanceTags, '@public'];

export async function openPostTypeSettings(page: Page, postType: string): Promise<void> {
  await page.goto(`/wp-admin/admin.php?page=publishpress-future&tab=defaults&post_type=${encodeURIComponent(postType)}`);
  await expect(page.locator(`#expirationdate_activemeta-${postType}`)).toBeVisible();
}

export async function savePostTypeSettings(page: Page): Promise<void> {
  const button = page.locator('input[name="expirationdateSaveDefaults"]');
  await expect(button).toBeEnabled();
  await button.click();
  await page.waitForLoadState('domcontentloaded');
  await expect(page.locator('input[name="expirationdateSaveDefaults"]')).toBeVisible();
}

export async function openDisplaySettings(page: Page): Promise<void> {
  await page.goto('/wp-admin/admin.php?page=publishpress-future&tab=display');
  await expect(page.locator('#expired-default-date-format')).toBeVisible();
}

export async function saveDisplaySettings(page: Page): Promise<void> {
  await page.locator('input[name="expirationdateSaveDisplay"]').click();
  await page.waitForLoadState('domcontentloaded');
  await expect(page.locator('#expired-default-date-format')).toBeVisible();
}

export function contentLocator(page: Page): Locator {
  return page.locator('.entry-content, .wp-block-post-content');
}

export async function dismissEditorWelcome(page: Page): Promise<void> {
  const close = page.locator('.components-modal__header button[aria-label="Close"], .components-guide button:has-text("Close")').first();

  if (await close.isVisible().catch(() => false)) {
    await close.click();
  }
}

export async function openBlockEditor(page: Page, path: string): Promise<void> {
  ensureBlockEditorPreferred();
  await page.goto(path);
  await page.waitForFunction(() => {
    const wpWindow = window as Window & { wp?: { data?: { select?: (store: string) => unknown } } };
    return Boolean(wpWindow.wp?.data?.select?.('core/editor'));
  });
  await dismissEditorWelcome(page);
}

export async function openClassicEditor(page: Page, path: string): Promise<void> {
  ensureClassicEditor();
  await page.goto(path);
  await expect(page.locator('#title')).toBeVisible();
}

export async function openFuturePanel(page: Page): Promise<void> {
  const panel = page.locator('.components-panel .post-expirator-panel');
  await expect(panel).toBeVisible();
  const toggle = panel.getByRole('button').first();
  if (await toggle.getAttribute('aria-expanded') === 'false') {
    await toggle.click();
  }
}

export function blockCheckbox(page: Page): Locator {
  return page.locator('.components-panel .post-expirator-panel .future-action-enable-checkbox input[type="checkbox"]');
}

export function classicCheckbox(page: Page): Locator {
  return page.locator('#expirationdatediv .future-action-enable-checkbox input[type="checkbox"]');
}

interface EditorDataApi {
  dispatch: (store: string) => {
    editPost: (edits: { title: string }) => void;
    savePost: () => Promise<void>;
  };
  select: (store: string) => {
    isSavingPost: () => boolean;
    isPostSavingLocked: () => boolean;
  };
}

export async function setBlockTitle(page: Page, title: string): Promise<void> {
  await page.evaluate((nextTitle) => {
    const wpWindow = window as Window & { wp: { data: EditorDataApi } };
    wpWindow.wp.data.dispatch('core/editor').editPost({ title: nextTitle });
  }, title);
}

export async function saveBlockPost(page: Page): Promise<void> {
  await page.evaluate(async () => {
    const wpWindow = window as Window & { wp: { data: EditorDataApi } };
    const editor = wpWindow.wp.data;
    const locked = editor.select('core/editor').isPostSavingLocked();
    if (locked) {
      throw new Error('Block editor save is locked. Future action data is incomplete.');
    }
    await editor.dispatch('core/editor').savePost();
    const started = Date.now();
    while (editor.select('core/editor').isSavingPost() && Date.now() - started < 20000) {
      await new Promise((resolve) => {
        setTimeout(resolve, 100);
      });
    }
  });
  await page.reload({ waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => {
    const wpWindow = window as Window & { wp?: { data?: { select?: (store: string) => unknown } } };
    return Boolean(wpWindow.wp?.data?.select?.('core/editor'));
  });
  await dismissEditorWelcome(page);
}

export async function setNoonDaysFromNow(page: Page, root: string, days: number): Promise<NoonParts> {
  const parts = noonPartsDaysFromNow(days);
  const scope = page.locator(root);
  await scope.locator('.components-datetime__time-field-month select').selectOption(parts.month);
  const day = scope.locator('.components-datetime__time-field-day input');
  await day.fill(parts.day);
  await day.press('Enter');
  const year = scope.locator('.components-datetime__time-field-year input');
  await year.fill(parts.year);
  await year.press('Enter');
  const hours = scope.locator('.components-datetime__time-field-hours-input input');
  await hours.fill('12');
  await hours.press('Enter');
  const minutes = scope.locator('.components-datetime__time-field-minutes-input input');
  await minutes.fill('00');
  await minutes.press('Enter');
  const pm = scope.locator('.components-datetime__time-pm-button');
  if (await pm.count()) {
    await pm.click();
  }

  return parts;
}

export async function enableFutureAction(page: Page, root: string): Promise<void> {
  const checkbox = page.locator(`${root} .future-action-enable-checkbox input[type="checkbox"]`);
  if (!await checkbox.isChecked()) {
    await checkbox.check();
  }
  const action = page.locator(`${root} .future-action-select-action select`);
  await expect(action).toBeVisible();
  await action.selectOption('change-status');
  const newStatus = page.locator(`${root} .future-action-select-new-status select`);
  await expect(newStatus).toBeVisible();
  await newStatus.selectOption('draft');
  await setNoonDaysFromNow(page, root, 1);
}

export async function saveClassicPost(page: Page): Promise<void> {
  await page.locator('#publish').click();
  await page.waitForLoadState('domcontentloaded');
  await expect(page.locator('#expirationdatediv')).toBeVisible();
}
