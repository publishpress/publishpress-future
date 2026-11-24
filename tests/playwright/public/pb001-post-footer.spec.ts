import { test, expect, type Page } from '../support/fixtures';

import { contentLocator, openDisplaySettings, publicTags, saveDisplaySettings } from '../support/admin';
import { createPost, formatSiteDate, scheduleDraftExpiration, uniqueKey, wp } from '../support/wp-cli';

function seedExpiringPost(): { slug: string; unix: number } {
  const post = createPost(uniqueKey('pw-footer'));
  const scheduled = scheduleDraftExpiration(post.id, 7);

  return { slug: post.slug, unix: scheduled.unix };
}

async function setFooter(page: Page, enabled: boolean, contents: string, style = ''): Promise<void> {
  await openDisplaySettings(page);
  await page.locator(enabled ? '#expired-display-footer-true' : '#expired-display-footer-false').check();
  await page.locator('#expired-footer-contents').fill(contents);
  if (style !== '') {
    await page.locator('#expired-footer-style').fill(style);
  }
  await saveDisplaySettings(page);
}

function optionValue(key: string): string {
  return wp(['option', 'get', key]);
}

for (const contents of [
  'Post expires at EXPIRATIONTIME on EXPIRATIONDATE',
  'Post expires at ACTIONTIME on ACTIONDATE',
]) {
  test(`shows footer for ${contents}`, { tag: publicTags }, async ({ page }) => {
    const seeded = seedExpiringPost();
    await setFooter(page, true, contents);
    await page.goto(`/${seeded.slug}/`);
    await expect(contentLocator(page)).toContainText('Post expires at ');
  });

  test(`hides footer for ${contents}`, { tag: publicTags }, async ({ page }) => {
    const seeded = seedExpiringPost();
    await setFooter(page, false, contents);
    await page.goto(`/${seeded.slug}/`);
    await expect(contentLocator(page)).not.toContainText('Post expires at ');
  });
}

test('custom footer content is shown', { tag: publicTags }, async ({ page }) => {
  const seeded = seedExpiringPost();
  const contents = 'This post will expire! Hurry to read it!';
  await setFooter(page, true, contents);
  await page.goto(`/${seeded.slug}/`);
  await expect(contentLocator(page)).toContainText(contents);
});

for (const placeholder of ['EXPIRATIONFULL', 'ACTIONFULL']) {
  test(`${placeholder} is replaced with the full date`, { tag: publicTags }, async ({ page }) => {
    const seeded = seedExpiringPost();
    await setFooter(page, true, `This post will expire at ${placeholder}`);
    await page.goto(`/${seeded.slug}/`);
    const full = formatSiteDate(
      `${optionValue('expirationdateDefaultDateFormat')} ${optionValue('expirationdateDefaultTimeFormat')}`,
      seeded.unix,
    );
    await expect(contentLocator(page)).toContainText('This post will expire at');
    await expect(contentLocator(page)).toContainText(full);
  });
}

for (const placeholder of ['EXPIRATIONDATE', 'ACTIONDATE']) {
  test(`${placeholder} is replaced with the date`, { tag: publicTags }, async ({ page }) => {
    const seeded = seedExpiringPost();
    await setFooter(page, true, `This post will expire at ${placeholder}`);
    await page.goto(`/${seeded.slug}/`);
    await expect(contentLocator(page)).toContainText('This post will expire at');
    await expect(contentLocator(page)).toContainText(
      formatSiteDate(optionValue('expirationdateDefaultDateFormat'), seeded.unix),
    );
  });
}

for (const placeholder of ['EXPIRATIONTIME', 'ACTIONTIME']) {
  test(`${placeholder} is replaced with the time`, { tag: publicTags }, async ({ page }) => {
    const seeded = seedExpiringPost();
    await setFooter(page, true, `This post will expire at ${placeholder}`);
    await page.goto(`/${seeded.slug}/`);
    await expect(contentLocator(page)).toContainText('This post will expire at');
    await expect(contentLocator(page)).toContainText(
      formatSiteDate(optionValue('expirationdateDefaultTimeFormat'), seeded.unix),
    );
  });
}

test('footer style is shown in the preview', { tag: publicTags }, async ({ page }) => {
  seedExpiringPost();
  const style = 'font-style: italic; color: green;';
  await setFooter(page, true, 'This post will expire!', style);
  await expect(page.locator('#expired-footer-style ~ div div[style]').last()).toHaveAttribute(
    'style',
    /font-style: italic; color: green;/,
  );
});

test('footer style is shown on the frontend', { tag: publicTags }, async ({ page }) => {
  const seeded = seedExpiringPost();
  const style = 'font-style: italic; color: orange;';
  const contents = 'This post will expire!';
  await setFooter(page, true, contents, style);
  await page.goto(`/${seeded.slug}/`);
  await expect(page.locator('p').filter({ hasText: contents })).toHaveAttribute('style', style);
});
