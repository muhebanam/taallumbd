import { test, expect } from '@playwright/test';

test.describe('Admin Payment Verification E2E', () => {
  test('Admin security gate & payment moderation workflow', async ({ page }) => {
    // 1. Visit Login
    await page.goto('/login');
    await expect(page.locator('input[name="email"], #email')).toBeVisible();

    // 2. Unauthenticated request to /admin redirects to login
    await page.goto('/admin');
    await expect(page).toHaveURL(/\/login|\/two-factor/);
  });
});
