# EasyStore

A Joomla e-commerce component project.

## Requirements

- PHP 8.1+
- Joomla 5.x

<<<<<<< Updated upstream
## Setup
=======
- PHP 7.4 or higher
- Composer
- Node.js 18+ (for testing)
- Ollama API running locally
- Joomla installation
>>>>>>> Stashed changes

1. Clone the repository
2. Run `composer install`
3. Configure your `.env` file based on `.env.example`

<<<<<<< Updated upstream
## License

=======
- Automated translation of Joomla language files
- Support for site and administrator language files
- Configurable translation settings
- Non-destructive translation process
- Web-based UI for easy configuration
- Comprehensive E2E testing with Playwright

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/siddik-web/ai-content-replacer
   cd ai-content-replacer
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies (for testing):
   ```bash
   npm install
   npx playwright install chromium
   ```

4. Configure environment:
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

## Usage

### Web Interface

Start the PHP development server:
```bash
php -S localhost:8080
```

Open http://localhost:8080 in your browser.

### CLI Usage

```bash
php main.php
```

---

## Testing

This project uses **Playwright** for end-to-end (E2E) testing. Playwright is a modern testing framework that runs tests in a real browser, ensuring your application works correctly from the user's perspective.

### Quick Start

```bash
# Run all tests
npm test

# Run with visible browser
npm run test:headed
```

### Running Tests

#### All Tests
```bash
npm test
```

#### Specific Test Suites
```bash
npm run test:form      # Form UI and validation tests (13 tests)
npm run test:api       # API endpoint tests (14 tests)
npm run test:workflow  # Workflow integration tests (8 tests)
npm run test:utils     # Utility function tests (14 tests)
```

#### Debugging
```bash
npm run test:debug     # Run in debug mode with step-by-step execution
npm run test:ui        # Open Playwright UI for interactive testing
```

#### Viewing Reports
```bash
npm run test:report    # Open HTML test report in browser
```

### Test Structure

```
tests/
├── e2e/                          # Playwright E2E tests
│   ├── form.spec.js              # Form UI and validation tests
│   ├── api.spec.js               # API endpoint tests
│   ├── workflow.spec.js          # End-to-end workflow tests
│   └── utils.spec.js             # Utility function tests
├── OllamaApiTest.php             # PHP unit tests for OllamaApi
├── TranslationServiceTest.php    # PHP unit tests for TranslationService
└── UtilTest.php                  # PHP unit tests for Util
```

### Test Coverage Details

#### Form Tests (`form.spec.js`) - 13 tests

| Test Category | Tests | Description |
|---------------|-------|-------------|
| **UI Elements** | 5 | Verifies form fields, language options, file types, loading spinner, result container |
| **Validation** | 4 | Tests empty form submission, missing fields, error clearing |
| **Submission** | 4 | Loading states, success/error messages, JSON payload verification |

**Key scenarios tested:**
- Form displays all required fields
- All 13 language options are available
- All 3 file types (Site, Admin, Admin Sys) are available
- Validation errors appear for empty fields
- Loading spinner shows during submission
- Success/error messages display correctly
- Correct JSON payload is sent to API

#### API Tests (`api.spec.js`) - 14 tests

| Test Category | Tests | Description |
|---------------|-------|-------------|
| **Input Validation** | 6 | Invalid JSON, empty body, missing required fields |
| **Response Format** | 3 | Content-Type header, status/message fields |
| **HTTP Methods** | 1 | POST request handling |
| **Input Trimming** | 2 | Whitespace trimming for code and file parameters |
| **Error Handling** | 2 | Invalid project paths, malformed JSON |

**Key scenarios tested:**
- Returns 400 for invalid JSON
- Returns 400 for empty body
- Returns 400 when `code` parameter is missing
- Returns 400 when `file` parameter is missing
- Returns proper JSON response format
- Trims whitespace from input parameters

#### Workflow Tests (`workflow.spec.js`) - 8 tests

| Test Category | Tests | Description |
|---------------|-------|-------------|
| **Form to API Integration** | 3 | Full submission flow, multiple submissions, error recovery |
| **Language Selection** | 2 | All languages, all file types |
| **Edge Cases** | 3 | Special characters, long names, double submission prevention |

**Key scenarios tested:**
- Complete form submission with mocked API
- Multiple sequential submissions
- Error recovery and retry
- All 13 languages can be selected
- Special characters in project path
- Long component names
- Double submission prevention

#### Utils Tests (`utils.spec.js`) - 14 tests

| Test Category | Tests | Description |
|---------------|-------|-------------|
| **JSON Parsing** | 2 | Valid/invalid JSON handling |
| **String Trimming** | 2 | Whitespace trimming, empty strings |
| **DOM Manipulation** | 4 | Text content, visibility, button states |
| **Fetch API** | 3 | POST requests, error handling, JSON parsing |
| **Error Display** | 3 | Error/success messages, message clearing |

**Key scenarios tested:**
- JSON parsing works correctly
- String trimming functions
- DOM elements can be manipulated
- Fetch API handles requests and errors
- Error messages display with correct CSS classes

### Writing New Tests

#### Basic Test Structure

```javascript
// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Feature Name', () => {
  test('should do something', async ({ page }) => {
    await page.goto('/');
    
    // Your test logic here
    await expect(page.locator('#element')).toBeVisible();
  });
});
```

#### Mocking API Responses

```javascript
test('should handle API response', async ({ page }) => {
  // Mock the API response
  await page.route('**/main.php', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ status: 'success', message: 'Done' }),
    });
  });

  await page.goto('/');
  await page.locator('#submitButton').click();
  
  await expect(page.locator('#result .success')).toContainText('Done');
});
```

#### Testing API Endpoints Directly

```javascript
test('should validate API input', async ({ request }) => {
  const response = await request.post('/main.php', {
    headers: { 'Content-Type': 'application/json' },
    data: {
      code: 'fr-FR',
      file: 'site',
      projectPath: '/test',
    },
  });

  expect(response.status()).toBe(200);
  const body = await response.json();
  expect(body.status).toBe('success');
});
```

### Debugging Tests

#### Using Debug Mode
```bash
npm run test:debug
```
This opens a browser window and pauses at each step, allowing you to inspect the page.

#### Using Playwright UI
```bash
npm run test:ui
```
This opens the Playwright Test Generator UI where you can:
- Record new tests by interacting with the browser
- Inspect selectors
- View test traces

#### Viewing Test Reports
```bash
npm run test:report
```
Opens an HTML report with:
- Test results summary
- Screenshots for failed tests
- Video recordings (if enabled)
- Test traces for debugging

### CI/CD Integration

#### GitHub Actions Example

```yaml
name: E2E Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      - name: Install dependencies
        run: |
          composer install
          npm install
          npx playwright install --with-deps chromium
      - name: Run tests
        run: npm test
      - name: Upload test results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: playwright-report
          path: playwright-report/
```

### Configuration

#### Playwright Configuration (`playwright.config.js`)

| Option | Value | Description |
|--------|-------|-------------|
| `testDir` | `./tests/e2e` | Directory containing test files |
| `timeout` | `30000` | Test timeout in milliseconds |
| `retries` | `0` (local) / `2` (CI) | Number of retries on failure |
| `workers` | `1` | Number of parallel workers |
| `headless` | `true` | Run browser in headless mode |
| `baseURL` | `http://localhost:8080` | Base URL for tests |

#### Package Scripts

| Script | Command | Description |
|--------|---------|-------------|
| `test` | `npx playwright test` | Run all tests |
| `test:form` | `npx playwright test form.spec.js` | Run form tests |
| `test:api` | `npx playwright test api.spec.js` | Run API tests |
| `test:workflow` | `npx playwright test workflow.spec.js` | Run workflow tests |
| `test:utils` | `npx playwright test utils.spec.js` | Run utils tests |
| `test:headed` | `npx playwright test --headed` | Run with visible browser |
| `test:debug` | `npx playwright test --debug` | Run in debug mode |
| `test:ui` | `npx playwright test --ui` | Open Playwright UI |
| `test:report` | `npx playwright show-report` | View HTML report |

### Troubleshooting

#### Common Issues

1. **Tests fail with "element not found"**
   - Ensure the PHP server is running: `php -S localhost:8080`
   - Check if the page loads correctly in browser

2. **Tests timeout**
   - Increase timeout in `playwright.config.js`
   - Check for slow API responses

3. **Browser not found**
   - Run: `npx playwright install chromium`

4. **Port already in use**
   - Kill existing server: `pkill -f "php -S localhost:8080"`
   - Or change port in `playwright.config.js`

#### Viewing Failed Tests

When a test fails, Playwright saves:
- Screenshot in `test-results/` directory
- Trace file for debugging
- Error context in `error-context.md`

View traces with:
```bash
npx playwright show-trace test-results/trace.zip
```

---

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `OLLAMA_HOST` | Ollama API URL | `http://localhost:11434` |
| `OLLAMA_MODEL` | Model to use | `gemma3` |
| `LOG_LEVEL` | Logging level | `info` |
| `PROJECT_PATH` | Default Joomla project path | - |
| `COMPONENT_NAME` | Default component name | `com_sppagebuilder` |

## License

>>>>>>> Stashed changes
MIT
