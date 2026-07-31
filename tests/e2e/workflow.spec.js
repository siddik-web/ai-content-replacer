// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Translation Workflow', () => {
  test.describe('Form to API Integration', () => {
    test('should complete full form submission flow', async ({ page }) => {
      await page.goto('/');

      // Fill in all fields
      await page.locator('#projectPath').fill('/test/project');
      await page.locator('#componentName').fill('com_test');
      await page.locator('#code').selectOption('fr-FR');
      await page.locator('#file').selectOption('site');

      // Mock API response
      await page.route('**/main.php', async (route) => {
        const body = route.request().postDataJSON();
        
        // Validate received data
        expect(body.code).toBe('fr-FR');
        expect(body.file).toBe('site');
        expect(body.projectPath).toBe('/test/project');
        expect(body.componentName).toBe('com_test');

        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ 
            status: 'success', 
            message: 'Translations updated successfully.' 
          }),
        });
      });

      // Submit form
      await page.locator('#submitButton').click();

      // Wait for response
      await page.waitForResponse('**/main.php');

      // Verify success message
      await expect(page.locator('#result .success')).toContainText('Translations updated successfully.');
    });

    test('should handle multiple sequential submissions', async ({ page }) => {
      await page.goto('/');

      let requestCount = 0;
      await page.route('**/main.php', async (route) => {
        requestCount++;
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ 
            status: 'success', 
            message: `Success ${requestCount}` 
          }),
        });
      });

      // First submission
      await page.locator('#projectPath').fill('/test/project');
      await page.locator('#componentName').fill('com_test');
      await page.locator('#code').selectOption('fr-FR');
      await page.locator('#file').selectOption('site');
      await page.locator('#submitButton').click();
      await page.waitForResponse('**/main.php');
      await expect(page.locator('#result .success')).toContainText('Success 1');

      // Second submission
      await page.locator('#code').selectOption('de-DE');
      await page.locator('#submitButton').click();
      await page.waitForResponse('**/main.php');
      await expect(page.locator('#result .success')).toContainText('Success 2');

      expect(requestCount).toBe(2);
    });

    test('should handle error recovery', async ({ page }) => {
      await page.goto('/');

      let callCount = 0;
      await page.route('**/main.php', async (route) => {
        callCount++;
        if (callCount === 1) {
          // First call fails
          await route.fulfill({
            status: 500,
            contentType: 'application/json',
            body: JSON.stringify({ 
              status: 'error', 
              message: 'Server error' 
            }),
          });
        } else {
          // Second call succeeds
          await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ 
              status: 'success', 
              message: 'Success after retry' 
            }),
          });
        }
      });

      await page.locator('#projectPath').fill('/test/project');
      await page.locator('#componentName').fill('com_test');
      await page.locator('#code').selectOption('fr-FR');
      await page.locator('#file').selectOption('site');

      // First submission - should fail
      await page.locator('#submitButton').click();
      await page.waitForResponse('**/main.php');
      await expect(page.locator('#result .error-message')).toContainText('Server error');

      // Second submission - should succeed
      await page.locator('#submitButton').click();
      await page.waitForResponse('**/main.php');
      await expect(page.locator('#result .success')).toContainText('Success after retry');
    });
  });

  test.describe('Language Selection', () => {
    test('should allow selecting all available languages', async ({ page }) => {
      await page.goto('/');

      const languages = [
        'bg-BG', 'cs-CZ', 'es-ES', 'fi-FI', 'fr-FR', 
        'it-IT', 'nl-NL', 'pt-BR', 'pt-PT', 'ru-RU', 
        'th-TH', 'uk-UA', 'de-DE'
      ];

      for (const lang of languages) {
        await page.locator('#code').selectOption(lang);
        await expect(page.locator('#code')).toHaveValue(lang);
      }
    });

    test('should allow selecting all file types', async ({ page }) => {
      await page.goto('/');

      const fileTypes = ['site', 'admin', 'sys'];

      for (const type of fileTypes) {
        await page.locator('#file').selectOption(type);
        await expect(page.locator('#file')).toHaveValue(type);
      }
    });
  });

  test.describe('Edge Cases', () => {
    test('should handle special characters in project path', async ({ page }) => {
      await page.goto('/');

      const specialPath = '/path/with spaces/and-dashes/and_underscores';
      await page.locator('#projectPath').fill(specialPath);
      await page.locator('#componentName').fill('com_test');
      await page.locator('#code').selectOption('fr-FR');
      await page.locator('#file').selectOption('site');

      let requestBody = null;
      await page.route('**/main.php', async (route) => {
        requestBody = route.request().postDataJSON();
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ status: 'success', message: 'Success' }),
        });
      });

      await page.locator('#submitButton').click();
      await page.waitForResponse('**/main.php');

      expect(requestBody.projectPath).toBe(specialPath);
    });

    test('should handle long component names', async ({ page }) => {
      await page.goto('/');

      const longName = 'com_very_long_component_name_that_exceeds_normal_length';
      await page.locator('#projectPath').fill('/test');
      await page.locator('#componentName').fill(longName);
      await page.locator('#code').selectOption('fr-FR');
      await page.locator('#file').selectOption('site');

      let requestBody = null;
      await page.route('**/main.php', async (route) => {
        requestBody = route.request().postDataJSON();
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ status: 'success', message: 'Success' }),
        });
      });

      await page.locator('#submitButton').click();
      await page.waitForResponse('**/main.php');

      expect(requestBody.componentName).toBe(longName);
    });

    test('should prevent double submission', async ({ page }) => {
      await page.goto('/');

      let requestCount = 0;
      await page.route('**/main.php', async (route) => {
        requestCount++;
        // Simulate slow response
        await new Promise(resolve => setTimeout(resolve, 500));
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ status: 'success', message: 'Success' }),
        });
      });

      await page.locator('#projectPath').fill('/test');
      await page.locator('#componentName').fill('com_test');
      await page.locator('#code').selectOption('fr-FR');
      await page.locator('#file').selectOption('site');

      // Click submit
      await page.locator('#submitButton').click();

      // Button should be disabled immediately
      await expect(page.locator('#submitButton')).toBeDisabled();

      // Try clicking again (should not work)
      await page.locator('#submitButton').click({ force: true });

      // Wait for response
      await page.waitForResponse('**/main.php');

      // Only one request should have been made
      expect(requestCount).toBe(1);
    });
  });
});
