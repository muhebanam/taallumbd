import { test, expect } from '@playwright/test';

test.describe('Mobile Viewport & Responsive Design E2E', () => {
  const mobileViewports = [
    { name: 'iPhone SE (375x667)', width: 375, height: 667 },
    { name: 'iPhone 13 (390x844)', width: 390, height: 844 },
  ];

  for (const vp of mobileViewports) {
    test(`Verify responsive layout on ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });

      // 1. Mobile Home Page
      await page.goto('/');
      await expect(page.locator('body')).toBeVisible();

      // Ensure no horizontal scrollbar overflow
      const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
      const clientWidth = await page.evaluate(() => document.documentElement.clientWidth);
      expect(scrollWidth).toBeLessThanOrEqual(clientWidth + 2);

      // 2. Mobile Course Catalog
      await page.goto('/courses');
      await expect(page.locator('body')).toBeVisible();

      // 3. Mobile Quran Reader
      await page.goto('/quran');
      await expect(page.locator('body')).toBeVisible();
    });
  }
});
