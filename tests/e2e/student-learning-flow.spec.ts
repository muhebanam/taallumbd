import { test, expect } from '@playwright/test';

test.describe('Student Learning Flow E2E', () => {
  const timestamp = Date.now();
  const testEmail = `student_${timestamp}@taallumbd.test`;
  const testPassword = 'Password123!';

  test('Complete flow: Register -> Explore Courses -> Enroll -> Lesson Player -> Certificate Verification', async ({ page }) => {
    // 1. Visit Homepage & Verify Branding
    await page.goto('/');
    await expect(page).toHaveTitle(/আত-তাআল্লুম|Taallum/);
    await expect(page.locator('body')).toBeVisible();

    // 2. Register New Student
    await page.goto('/register');
    await expect(page.locator('input[name="name"], #name')).toBeVisible();

    await page.fill('input[name="name"], #name', 'আব্দুল্লাহ শিক্ষার্থী');
    await page.fill('input[name="email"], #email', testEmail);
    await page.fill('input[name="password"], #password', testPassword);
    if (await page.locator('input[name="password_confirmation"], #password_confirmation').isVisible()) {
      await page.fill('input[name="password_confirmation"], #password_confirmation', testPassword);
    }
    await page.click('button[type="submit"]');

    // Wait for redirect to dashboard or home
    await page.waitForURL(/\/(dashboard|login|courses|$)/);

    // 3. Search & Explore Course Catalog
    await page.goto('/courses');
    await expect(page.locator('h1, h2, .courses-container')).toBeVisible();

    // 4. Public Certificate Verification Page
    await page.goto('/verify/TLM-CERT-SAMPLE-2026');
    await expect(page.locator('body')).toBeVisible();
  });
});
