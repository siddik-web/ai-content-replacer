// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Translation Form - UI Elements', () => {
  test('should display the form with all fields', async ({ page }) => {
    await page.goto('/');

    // Check page title
    await expect(page).toHaveTitle('Translate Language - SP Page Builder');

    // Check heading
    await expect(page.locator('h1')).toContainText('Translate Language - SP Page Builder');

    // Check all form fields exist
    await expect(page.locator('#projectPath')).toBeVisible();
    await expect(page.locator('#componentName')).toBeVisible();
    await expect(page.locator('#code')).toBeVisible();
    await expect(page.locator('#file')).toBeVisible();
    await expect(page.locator('#submitButton')).toBeVisible();
  });

  test('should have correct language options', async ({ page }) => {
    await page.goto('/');

    const languageSelect = page.locator('#code');
    const options = await languageSelect.locator('option').allTextContents();

    expect(options).toContain('Bulgarian (bg-BG)');
    expect(options).toContain('Czech (cs-CZ)');
    expect(options).toContain('Spanish (es-ES)');
    expect(options).toContain('Finnish (fi-FI)');
    expect(options).toContain('French (fr-FR)');
    expect(options).toContain('Italian (it-IT)');
    expect(options).toContain('Dutch (nl-NL)');
    expect(options).toContain('Portuguese Brazil (pt-BR)');
    expect(options).toContain('Portuguese (pt-PT)');
    expect(options).toContain('Russian (ru-RU)');
    expect(options).toContain('Thai (th-TH)');
    expect(options).toContain('Ukrainian (uk-UA)');
    expect(options).toContain('German (de-DE)');
  });

  test('should have correct file type options', async ({ page }) => {
    await page.goto('/');

    const fileSelect = page.locator('#file');
    const options = await fileSelect.locator('option').allTextContents();

    expect(options).toContain('Site');
    expect(options).toContain('Admin');
    expect(options).toContain('Admin Sys');
  });

  test('should have loading spinner hidden by default', async ({ page }) => {
    await page.goto('/');

    const spinner = page.locator('#loadingSpinner');
    await expect(spinner).toBeHidden();

    const loadingText = page.locator('#loadingText');
    await expect(loadingText).toBeHidden();
  });

  test('should have result container', async ({ page }) => {
    await page.goto('/');

    const resultContainer = page.locator('#result');
    await expect(resultContainer).toBeVisible();
    await expect(resultContainer).toBeEmpty();
  });
});

test.describe('Translation Form - Validation', () => {
  test('should show errors when submitting empty form', async ({ page }) => {
    await page.goto('/');

    // Clear any pre-filled values
    await page.locator('#projectPath').clear();
    await page.locator('#componentName').clear();

    // Submit the form
    await page.locator('#submitButton').click();

    // Check error messages
    await expect(page.locator('#projectPath-error')).toContainText('Please enter a project path.');
    await expect(page.locator('#componentName-error')).toContainText('Please enter a component name.');
    await expect(page.locator('#code-error')).toContainText('Please enter a valid language code.');
    await expect(page.locator('#file-error')).toContainText('Please select a file type.');
  });

  test('should show error for empty language code', async ({ page }) => {
    await page.goto('/');

    await page.locator('#projectPath').fill('/some/path');
    await page.locator('#componentName').fill('com_test');
    await page.locator('#file').selectOption('site');

    // Don't select language code
    await page.locator('#submitButton').click();

    await expect(page.locator('#code-error')).toContainText('Please enter a valid language code.');
  });

  test('should show error for empty file type', async ({ page }) => {
    await page.goto('/');

    await page.locator('#projectPath').fill('/some/path');
    await page.locator('#componentName').fill('com_test');
    await page.locator('#code').selectOption('fr-FR');

    // Don't select file type
    await page.locator('#submitButton').click();

    await expect(page.locator('#file-error')).toContainText('Please select a file type.');
  });

  test('should clear errors when form is resubmitted', async ({ page }) => {
    await page.goto('/');

    // First submit with errors
    await page.locator('#submitButton').click();
    await expect(page.locator('#code-error')).toContainText('Please enter a valid language code.');

    // Fill valid data and submit
    await page.locator('#projectPath').fill('/some/path');
    await page.locator('#componentName').fill('com_test');
    await page.locator('#code').selectOption('fr-FR');
    await page.locator('#file').selectOption('site');

    // Mock the API response
    await page.route('**/main.php', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'success', message: 'Success' }),
      });
    });

    await page.locator('#submitButton').click();

    // Wait for the request to complete
    await page.waitForResponse('**/main.php');

    // Errors should be cleared
    await expect(page.locator('#code-error')).toBeEmpty();
    await expect(page.locator('#file-error')).toBeEmpty();
    await expect(page.locator('#projectPath-error')).toBeEmpty();
    await expect(page.locator('#componentName-error')).toBeEmpty();
  });
});

test.describe('Translation Form - Submission', () => {
  test('should show loading state during submission', async ({ page }) => {
    await page.goto('/');

    await page.locator('#projectPath').fill('/some/path');
    await page.locator('#componentName').fill('com_test');
    await page.locator('#code').selectOption('fr-FR');
    await page.locator('#file').selectOption('site');

    // Mock API with delay
    await page.route('**/main.php', async (route) => {
      await new Promise((resolve) => setTimeout(resolve, 1000));
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'success', message: 'Success' }),
      });
    });

    await page.locator('#submitButton').click();

    // Button should be disabled
    await expect(page.locator('#submitButton')).toBeDisabled();

    // Loading spinner should be visible
    await expect(page.locator('#loadingSpinner')).toBeVisible();
    await expect(page.locator('#loadingText')).toBeVisible();

    // Wait for request to complete
    await page.waitForResponse('**/main.php');

    // Button should be re-enabled
    await expect(page.locator('#submitButton')).toBeEnabled();

    // Loading spinner should be hidden
    await expect(page.locator('#loadingSpinner')).toBeHidden();
    await expect(page.locator('#loadingText')).toBeHidden();
  });

  test('should display success message on successful submission', async ({ page }) => {
    await page.goto('/');

    await page.locator('#projectPath').fill('/some/path');
    await page.locator('#componentName').fill('com_test');
    await page.locator('#code').selectOption('fr-FR');
    await page.locator('#file').selectOption('site');

    await page.route('**/main.php', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'success', message: 'Translations updated successfully.' }),
      });
    });

    await page.locator('#submitButton').click();
    await page.waitForResponse('**/main.php');

    await expect(page.locator('#result .success')).toContainText('Translations updated successfully.');
  });

  test('should display error message on failed submission', async ({ page }) => {
    await page.goto('/');

    await page.locator('#projectPath').fill('/some/path');
    await page.locator('#componentName').fill('com_test');
    await page.locator('#code').selectOption('fr-FR');
    await page.locator('#file').selectOption('site');

    await page.route('**/main.php', async (route) => {
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'error', message: 'Failed to update translations.' }),
      });
    });

    await page.locator('#submitButton').click();
    await page.waitForResponse('**/main.php');

    await expect(page.locator('#result .error-message')).toContainText('Failed to update translations.');
  });

  test('should send correct JSON payload', async ({ page }) => {
    await page.goto('/');

    await page.locator('#projectPath').fill('/test/project');
    await page.locator('#componentName').fill('com_test');
    await page.locator('#code').selectOption('de-DE');
    await page.locator('#file').selectOption('admin');

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

    expect(requestBody).toEqual(expect.objectContaining({
      code: 'de-DE',
      file: 'admin',
      projectPath: '/test/project',
      componentName: 'com_test',
    }));
  });
});
