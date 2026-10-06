import { test, expect } from '@playwright/test';

test.describe('Fatwa Q&A Flow E2E', () => {
  test('Browse Fatwa archive and view Ask Fatwa interface', async ({ page }) => {
    // 1. Browse Public Fatwa Library
    await page.goto('/fatawa');
    await expect(page.locator('body')).toBeVisible();

    // 2. Open Ask Fatwa submission form
    await page.goto('/fatawa/ask');
    await expect(page.locator('body')).toBeVisible();

    // Verify form elements exist
    const hasForm = await page.locator('form').count();
    expect(hasForm).toBeGreaterThan(0);
  });
});
