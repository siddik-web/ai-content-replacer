// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Utility Functions', () => {
  test.describe('JSON Parsing', () => {
    test('should parse valid JSON', async ({ page }) => {
      await page.goto('/');

      const result = await page.evaluate(() => {
        return JSON.parse('{"key": "value"}');
      });

      expect(result).toEqual({ key: 'value' });
    });

    test('should throw on invalid JSON', async ({ page }) => {
      await page.goto('/');

      const result = await page.evaluate(() => {
        try {
          JSON.parse('invalid');
          return { success: true };
        } catch (e) {
          return { success: false, error: e.message };
        }
      });

      expect(result.success).toBe(false);
    });
  });

  test.describe('String Trimming', () => {
    test('should trim whitespace from strings', async ({ page }) => {
      await page.goto('/');

      const result = await page.evaluate(() => {
        return '  hello  '.trim();
      });

      expect(result).toBe('hello');
    });

    test('should handle empty strings', async ({ page }) => {
      await page.goto('/');

      const result = await page.evaluate(() => {
        return ''.trim();
      });

      expect(result).toBe('');
    });
  });

  test.describe('DOM Manipulation', () => {
    test('should set text content', async ({ page }) => {
      await page.goto('/');

      await page.evaluate(() => {
        document.getElementById('result').innerHTML = '<p class="success">Test message</p>';
      });

      await expect(page.locator('#result .success')).toContainText('Test message');
    });

    test('should clear text content', async ({ page }) => {
      await page.goto('/');

      await page.evaluate(() => {
        document.getElementById('result').innerHTML = '<p>Old content</p>';
      });

      await expect(page.locator('#result')).toContainText('Old content');

      await page.evaluate(() => {
        document.getElementById('result').innerHTML = '';
      });

      await expect(page.locator('#result')).toBeEmpty();
    });

    test('should toggle element visibility', async ({ page }) => {
      await page.goto('/');

      // Spinner should be hidden by default
      await expect(page.locator('#loadingSpinner')).toBeHidden();

      // Show spinner
      await page.evaluate(() => {
        document.getElementById('loadingSpinner').style.display = 'block';
      });

      await expect(page.locator('#loadingSpinner')).toBeVisible();

      // Hide spinner
      await page.evaluate(() => {
        document.getElementById('loadingSpinner').style.display = 'none';
      });

      await expect(page.locator('#loadingSpinner')).toBeHidden();
    });

    test('should disable/enable button', async ({ page }) => {
      await page.goto('/');

      // Button should be enabled by default
      await expect(page.locator('#submitButton')).toBeEnabled();

      // Disable button
      await page.evaluate(() => {
        document.getElementById('submitButton').disabled = true;
      });

      await expect(page.locator('#submitButton')).toBeDisabled();

      // Enable button
      await page.evaluate(() => {
        document.getElementById('submitButton').disabled = false;
      });

      await expect(page.locator('#submitButton')).toBeEnabled();
    });
  });

  test.describe('Fetch API', () => {
    test('should make POST request with JSON', async ({ page }) => {
      await page.goto('/');

      let receivedBody = null;
      await page.route('**/main.php', async (route) => {
        receivedBody = route.request().postDataJSON();
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ status: 'success' }),
        });
      });

      await page.evaluate(async () => {
        await fetch('main.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ test: 'data' }),
        });
      });

      expect(receivedBody).toEqual({ test: 'data' });
    });

    test('should handle fetch errors', async ({ page }) => {
      await page.goto('/');

      await page.route('**/main.php', async (route) => {
        await route.abort('failed');
      });

      const result = await page.evaluate(async () => {
        try {
          await fetch('main.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ test: 'data' }),
          });
          return { success: true };
        } catch (e) {
          return { success: false, error: e.message };
        }
      });

      expect(result.success).toBe(false);
    });

    test('should parse JSON response', async ({ page }) => {
      await page.goto('/');

      await page.route('**/main.php', async (route) => {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ status: 'success', message: 'Done' }),
        });
      });

      const result = await page.evaluate(async () => {
        const response = await fetch('main.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({}),
        });
        return await response.json();
      });

      expect(result).toEqual({ status: 'success', message: 'Done' });
    });
  });

  test.describe('Error Display', () => {
    test('should display error message with correct class', async ({ page }) => {
      await page.goto('/');

      await page.evaluate(() => {
        document.getElementById('result').innerHTML = '<p class="error-message">Error occurred</p>';
      });

      await expect(page.locator('#result .error-message')).toContainText('Error occurred');
    });

    test('should display success message with correct class', async ({ page }) => {
      await page.goto('/');

      await page.evaluate(() => {
        document.getElementById('result').innerHTML = '<p class="success">Operation successful</p>';
      });

      await expect(page.locator('#result .success')).toContainText('Operation successful');
    });

    test('should clear previous messages', async ({ page }) => {
      await page.goto('/');

      // Set initial message
      await page.evaluate(() => {
        document.getElementById('result').innerHTML = '<p class="error">Old error</p>';
      });

      await expect(page.locator('#result .error')).toContainText('Old error');

      // Clear and set new message
      await page.evaluate(() => {
        document.getElementById('result').innerHTML = '';
        document.getElementById('result').innerHTML = '<p class="success">New success</p>';
      });

      await expect(page.locator('#result')).not.toContainText('Old error');
      await expect(page.locator('#result .success')).toContainText('New success');
    });
  });
});
