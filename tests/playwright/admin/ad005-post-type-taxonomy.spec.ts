import { test, expect } from '../support/fixtures';

import { adminTags, openPostTypeSettings, savePostTypeSettings } from '../support/admin';
import { setPostTypeDefaults } from '../support/wp-cli';

const rows = [
  { postType: 'post', taxonomy: 'tax1' },
  { postType: 'page', taxonomy: 'tax2' },
  { postType: 'music', taxonomy: 'tax3' },
];

for (const row of rows) {
  test(`default taxonomy ${row.taxonomy} persists for ${row.postType}`, { tag: adminTags }, async ({ page }) => {
    setPostTypeDefaults(row.postType, {
      activeMetaBox: 'active',
      autoEnable: '1',
      taxonomy: 'tax4',
    });
    await openPostTypeSettings(page, row.postType);
    await page.locator(`#expirationdate_taxonomy-${row.postType}`).selectOption(row.taxonomy);
    await savePostTypeSettings(page);
    await openPostTypeSettings(page, row.postType);
    await expect(page.locator(`#expirationdate_taxonomy-${row.postType}`)).toHaveValue(row.taxonomy);
  });
}
