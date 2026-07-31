# Testing Guide - Quick Reference

## Quick Commands

```bash
# Run all tests
npm test

# Run specific test suite
npm run test:form
npm run test:api
npm run test:workflow
npm run test:utils

# Debug mode
npm run test:debug

# View report
npm run test:report
```

## Test Summary

| Suite | Tests | Coverage |
|-------|-------|----------|
| Form | 13 | UI, validation, submission |
| API | 14 | Input, response, errors |
| Workflow | 8 | Integration, edge cases |
| Utils | 14 | DOM, fetch, display |
| **Total** | **49** | |

## Writing Tests

```javascript
const { test, expect } = require('@playwright/test');

test('my test', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('#element')).toBeVisible();
});
```

## Debugging

1. `npm run test:debug` - Step through tests
2. `npm run test:ui` - Interactive UI
3. Check `test-results/` for screenshots
4. `npx playwright show-trace <trace-file>`

## CI/CD

Tests run automatically on push/PR. See README for GitHub Actions config.
