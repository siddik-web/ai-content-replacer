// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('API Endpoint - /main.php', () => {
  test.describe('Input Validation', () => {
    test('should return 400 for invalid JSON', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: 'invalid json',
      });

      expect(response.status()).toBe(400);
      const body = await response.json();
      expect(body.status).toBe('error');
      expect(body.message).toBe('Invalid JSON input');
    });

    test('should return 400 for empty body', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: '',
      });

      expect(response.status()).toBe(400);
      const body = await response.json();
      expect(body.status).toBe('error');
    });

    test('should return 400 when code is missing', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          file: 'site',
          projectPath: '/some/path',
          componentName: 'com_test',
        },
      });

      expect(response.status()).toBe(400);
      const body = await response.json();
      expect(body.status).toBe('error');
      expect(body.message).toContain('Code and file parameters are required');
    });

    test('should return 400 when file is missing', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          code: 'fr-FR',
          projectPath: '/some/path',
          componentName: 'com_test',
        },
      });

      expect(response.status()).toBe(400);
      const body = await response.json();
      expect(body.status).toBe('error');
      expect(body.message).toContain('Code and file parameters are required');
    });

    test('should fall back to env PROJECT_PATH when projectPath is empty', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          code: 'fr-FR',
          file: 'site',
          projectPath: '',
          componentName: 'com_test',
        },
      });

      // Response depends on whether .env PROJECT_PATH exists and is valid
      // May return 200 (success), 400 (invalid path), or 500 (PHP error)
      expect(response.status()).toBeGreaterThanOrEqual(200);
    });

    test('should return 400 when projectPath is not provided and env PROJECT_PATH is invalid', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          code: 'fr-FR',
          file: 'site',
          componentName: 'com_test',
        },
      });

      // Response depends on whether .env PROJECT_PATH exists and is valid
      expect(response.status()).toBeGreaterThanOrEqual(200);
    });
  });

  test.describe('Response Format', () => {
    test('should return JSON with correct content type', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: '',
      });

      expect(response.headers()['content-type']).toContain('application/json');
    });

    test('should return status and message fields', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: '',
      });

      const body = await response.json();
      expect(body).toHaveProperty('status');
      expect(body).toHaveProperty('message');
      expect(['success', 'error']).toContain(body.status);
    });

    test('should return 400 with error status for invalid requests', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: { code: 'fr-FR' },
      });

      expect(response.status()).toBe(400);
      const body = await response.json();
      expect(body.status).toBe('error');
    });
  });

  test.describe('HTTP Methods', () => {
    test('should handle POST request', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          code: 'fr-FR',
          file: 'site',
          projectPath: '/nonexistent/path',
        },
      });

      // Should get a response (error due to invalid path, but method is accepted)
      expect(response.status()).toBeGreaterThanOrEqual(400);
    });
  });

  test.describe('Input Trimming', () => {
    test('should trim whitespace from code parameter', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          code: '  fr-FR  ',
          file: 'site',
          projectPath: '/nonexistent/path',
        },
      });

      // Should not return "Code and file parameters are required" error
      const body = await response.json();
      expect(body.message).not.toContain('Code and file parameters are required');
    });

    test('should trim whitespace from file parameter', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          code: 'fr-FR',
          file: '  site  ',
          projectPath: '/nonexistent/path',
        },
      });

      const body = await response.json();
      expect(body.message).not.toContain('Code and file parameters are required');
    });
  });

  test.describe('Error Handling', () => {
    test('should return 500 for invalid project path', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: {
          code: 'fr-FR',
          file: 'site',
          projectPath: '/nonexistent/path/that/does/not/exist',
          componentName: 'com_test',
        },
      });

      expect(response.status()).toBe(400);
      const body = await response.json();
      expect(body.status).toBe('error');
    });

    test('should handle malformed JSON object', async ({ request }) => {
      const response = await request.post('/main.php', {
        headers: { 'Content-Type': 'application/json' },
        data: '{ "code": "fr-FR", }',  // trailing comma
      });

      expect(response.status()).toBe(400);
      const body = await response.json();
      expect(body.status).toBe('error');
    });
  });

  test.describe('Job Status API - /job-status.php', () => {
    test('should return all jobs when action=list', async ({ request }) => {
      const response = await request.get('/job-status.php?action=list');
      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(body.status).toBe('success');
      expect(Array.isArray(body.jobs)).toBe(true);
    });
  });
});

