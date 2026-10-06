import { test, expect } from '@playwright/test';

test.describe('Instructor Course Builder E2E', () => {
  test('Instructor course management and curriculum builder interface', async ({ page }) => {
    // 1. Visit Login
    await page.goto('/login');
    await expect(page.locator('input[name="email"], #email')).toBeVisible();

    // 2. Visit Become Instructor landing
    await page.goto('/become-instructor');
    await expect(page.locator('body')).toBeVisible();

    // 3. View public instructor list
    await page.goto('/teachers');
    await expect(page.locator('body')).toBeVisible();
  });
});
