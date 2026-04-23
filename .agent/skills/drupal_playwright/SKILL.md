---
name: drupal_playwright
description: Skill to setup and manage Playwright testing for Drupal projects using JavaScript.
---

# Drupal Playwright Testing Skill

Use this skill to set up Playwright for Drupal projects and generate test scenarios.

## 1. Directory Structure Setup

The Playwright test directory MUST be placed at the same level as the Drupal `web` or `docroot` directory.

### Recommended Structure
```
[project_root]/
├── web/ (or docroot/)
├── playwright/
│   ├── tests/
│   │   ├── e2e/                  # End-to-End specific tests
│   │   │   ├── [module_name]/    # Group tests by module
│   │   │   │   ├── [feature].spec.js
│   │   │   │   └── ...
│   │   │   └── global-setup.js   # Global setup (e.g., login state)
│   │   └── fixtures/             # Reusable test data
│   │       ├── users.json
│   │       └── content.json
│   ├── utils/                    # Helper functions & Page Object Models
│   │   ├── auth.js               # Authentication helpers
│   │   ├── drupal-api.js         # API interaction helpers
│   │   └── ui-helpers.js         # Common UI interactions
│   ├── playwright.config.js      # Main configuration
│   └── package.json              # Dependencies
```

## 2. Setup Instructions

When initializing Playwright for a project, follow these steps:

1.  **Identify Root**: Locate the directory containing `web` or `docroot`.
2.  **Create Directory**: Create a `playwright` directory at that root.
3.  **Initialize Project**:
    - Create `package.json` with `playwright` and `@playwright/test`.
    - Create `playwright.config.js`.
    - Create the folder structure (`tests/e2e`, `utils`, etc.).
4.  **Install Dependencies**: Run `npm install` and `npx playwright install`.

### Boilerplate: package.json
```json
{
  "name": "drupal-playwright-tests",
  "version": "1.0.0",
  "scripts": {
    "test": "playwright test",
    "test:ui": "playwright test --ui",
    "test:debug": "playwright test --debug",
    "report": "playwright show-report"
  },
  "devDependencies": {
    "@playwright/test": "^1.40.0"
  }
}
```

### Boilerplate: playwright.config.js
```javascript
// @ts-check
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost', // CHANGE THIS TO SITE URL
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
```

## 3. Environment Management

To manage different testing environments (e.g., local, dev, staging), use a configuration file named `environment.js`.

### 1. Create `environment.js`
Place this file in `playwright/environment.js` or `playwright/utils/environment.js`.

```javascript
// playwright/environment.js

const environments = {
  local: {
    baseUrl: 'http://localhost',
    auth: {
      user: 'admin',
      pass: 'admin',
    },
    basicAuth: null,
  },
  dev: {
    baseUrl: 'https://dev.example.com',
    auth: {
      user: process.env.TEST_USER || 'admin',
      pass: process.env.TEST_PASS || 'password',
    },
    basicAuth: {
      username: 'basicuser',
      password: 'basicpassword',
    },
  },
};

// Default to 'local' if TEST_ENV is not set
const currentEnv = process.env.TEST_ENV || 'local';

module.exports = environments[currentEnv];
```

### 2. Update `playwright.config.js`
Modify your config to use the environment settings.

```javascript
// playwright.config.js
const { defineConfig, devices } = require('@playwright/test');
const env = require('./environment'); // Import the environment config

module.exports = defineConfig({
  // ... other config ...
  use: {
    baseURL: env.baseUrl, // Use dynamic URL
    httpCredentials: env.basicAuth || undefined, // Set basic auth if present
    trace: 'on-first-retry',
  },
  // ...
});
```

### 3. Usage in Tests
Access environment-specific variables (like credentials) directly from the module.

```javascript
const { test, expect } = require('@playwright/test');
const env = require('../environment'); // Import relative to test file

test('Login test', async ({ page }) => {
  await page.goto('/user/login');
  await page.fill('input[name="name"]', env.auth.user);
  await page.fill('input[name="pass"]', env.auth.pass);
  await page.click('button:has-text("Log in")');
});
```

### 4. Running Tests
Run tests against different environments by setting the `TEST_ENV` variable.

```bash
# Run on local (default)
npx playwright test

# Run on dev
TEST_ENV=dev npx playwright test
```

## 4. Writing Tests (Agent Instructions)

When asked to write a test for a Drupal module:

1.  **Locate Target**: Determine which module/feature is being tested (e.g., `my_module`).
2.  **Create Test File**: Create `playwright/tests/e2e/my_module/my_module_feature.spec.js`.
3.  **Use JS**: Write tests in standard JavaScript (ES6+).
4.  **Drupal Context**:
    - Assume a standard Drupal login flow usually requires visiting `/user/login`.
    - Use data-drupal-selector attributes where possible for stability.
    - Handle standard Drupal form submissions (fill fields, click 'Save', wait for message).

### Example Test Template (`my_module.spec.js`)

```javascript
const { test, expect } = require('@playwright/test');

test.describe('My Module Feature', () => {

  test.beforeEach(async ({ page }) => {
    // Login or setup before each test
    await page.goto('/user/login');
    await page.fill('input[name="name"]', 'admin');
    await page.fill('input[name="pass"]', 'password'); // Use ENV vars in real usage
    await page.click('button:has-text("Log in")');
    await expect(page.locator('.messages--status')).toContainText('Member for');
  });

  test('should perform specific action', async ({ page }) => {
    await page.goto('/admin/structure/my-module/add');
    await page.fill('input[name="label[0][value]"]', 'Test Entity');
    await page.click('input#edit-submit');
    await expect(page.locator('.messages--status')).toBeVisible();
  });
});
```

## 5. Coding Standards

To maintain consistency with Drupal's coding standards, apply the following rules to all Playwright JavaScript code:

| Category | Rule |
| :--- | :--- |
| **Indentation** | **2 spaces**, NO tabs. |
| **Semicolons** | **Always** use semicolons at the end of statements. |
| **Quotes** | Use **single quotes** (`'`) by default. Use backticks (`` ` ``) for template literals. |
| **Variables** | `const` by default. Use `let` only when reassignment is needed. Avoid `var`. |
| **Naming** | **camelCase** for variables and functions (e.g., `userLogin`). **PascalCase** for Classes (Page Objects). |
| **Async** | Always use `async`/`await`. Avoid `.then()` chains. |
| **Trailing Commas** | Use trailing commas in multi-line objects and arrays. |
| **Comments** | Use JSDoc `/** ... */` for functions/classes. Use `//` for inline explanations. |
| **Selectors** | Prefer `data-drupal-selector` or specific IDs. Avoid brittle XPath or generic CSS classes if possible. |

