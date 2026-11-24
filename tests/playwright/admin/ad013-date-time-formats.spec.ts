import { test, expect } from '../support/fixtures';

import {
  adminTags,
  contentLocator,
  openDisplaySettings,
  saveDisplaySettings,
} from '../support/admin';
import { createPost, formatSiteDate, scheduleDraftExpiration, uniqueKey, updateOption } from '../support/wp-cli';

const dateFormats = ['l F jS, Y', 'Y-m-d', 'l \\d. j F Y'];
const timeFormats = ['g:ia', 'g:i a', 'H:i', 'H:i:s'];

function seedFooterPost(): { slug: string; unix: number } {
  const post = createPost(uniqueKey('pw-format'));
  const scheduled = scheduleDraftExpiration(post.id, 7);
  updateOption('expirationdateDisplayFooter', '1');
  updateOption('expirationdateFooterContents', 'Post expires at EXPIRATIONTIME on ACTIONDATE');

  return { slug: post.slug, unix: scheduled.unix };
}

for (const dateFormat of dateFormats) {
  test(`post footer uses date format ${dateFormat}`, { tag: adminTags }, async ({ page }) => {
    const seeded = seedFooterPost();
    await openDisplaySettings(page);
    await page.locator('#expired-default-date-format').fill(dateFormat);
    await saveDisplaySettings(page);
    await page.goto(`/${seeded.slug}/`);
    await expect(contentLocator(page)).toContainText(formatSiteDate(dateFormat, seeded.unix));
  });
}

for (const timeFormat of timeFormats) {
  test(`post footer uses time format ${timeFormat}`, { tag: adminTags }, async ({ page }) => {
    const seeded = seedFooterPost();
    await openDisplaySettings(page);
    await page.locator('#expired-default-time-format').fill(timeFormat);
    await saveDisplaySettings(page);
    await page.goto(`/${seeded.slug}/`);
    await expect(contentLocator(page)).toContainText(formatSiteDate(timeFormat, seeded.unix));
  });
}
