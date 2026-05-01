# TypeScript Test Harness Guidelines

## Overview

The ERP Brand New Ideas Company TypeScript migration includes a comprehensive test harness infrastructure for validating compiled JavaScript artifacts against production-like HTML pages.

## Directory Structure

```
ts/
├── src/                          # TypeScript source files
├── dist/                         # Compiled JavaScript output
├── tests/
│   ├── harness/                  # Test harness HTML pages
│   │   ├── index.html           # Index of all test pages
│   │   ├── base-layout.html     # Template for test pages
│   │   └── pages/               # Generated mock HTML pages
│   │       ├── _manifest.json   # Manifest of generated pages
│   │       └── *.html           # Individual test pages
│   ├── harness-specs/           # Playwright e2e test specs
│   │   └── *.spec.ts            # Auto-generated test files
│   ├── unit/                    # Jest unit tests
│   │   └── *.test.ts            # Auto-generated unit tests
│   └── serve-harness.cjs        # HTTP server for test harness
├── playwright.harness.config.ts  # Playwright configuration
└── jest.config.cjs               # Jest configuration
```

## Test Categories

### 1. E2E Tests (Playwright)

End-to-end tests that run compiled JavaScript in real browsers.

**Run commands:**

```bash
npm run test:harness              # Run all browsers
npm run test:harness:chromium     # Chromium only
npm run test:harness:firefox      # Firefox only
npm run test:harness:headed       # With visible browser
npm run test:harness:ui           # Playwright UI mode
```

**Test structure:**

- Each resource has a dedicated `.spec.ts` file
- Tests verify:
  - Page loads without JavaScript errors
  - Status indicator shows "pass"
  - Expected DOM elements exist
  - Form validation works
  - Actions trigger expected behaviors

### 2. Unit Tests (Jest + testing-library)

DOM-based unit tests for TypeScript route handlers.

**Run commands:**

```bash
npm run test:unit                 # Run unit tests
npm run test:unit -- --watch      # Watch mode
npm run test:coverage             # With coverage
```

**Test structure:**

- Each resource has a dedicated `.test.ts` file
- Tests verify:
  - DOM manipulation logic
  - Form handling
  - Event listeners
  - Data validation

### 3. Type Checking

TypeScript type validation without emission.

```bash
npm run typecheck                 # One-time check
npm run typecheck:watch           # Watch mode
```

## Test Harness Generation

### Scripts Location

Scripts are stored in `.tmp/copilot/scripts/`:

| Script                          | Purpose                                          |
| ------------------------------- | ------------------------------------------------ |
| `scan-views.php`                | Extract JS dependencies from Laravel blade views |
| `generate-harness.cjs`          | Generate mock HTML pages from view mapping       |
| `generate-playwright-tests.cjs` | Generate Playwright specs from manifest          |
| `generate-jest-tests.cjs`       | Generate Jest tests from manifest                |
| `update-harness-index.cjs`      | Update harness index with all pages              |

### Regenerating Tests

```bash
# 1. Scan views for JS dependencies
php .tmp/copilot/scripts/scan-views.php .tmp/copilot/view-js-map.json

# 2. Generate mock HTML pages
node .tmp/copilot/scripts/generate-harness.cjs

# 3. Generate Playwright tests
node .tmp/copilot/scripts/generate-playwright-tests.cjs

# 4. Generate Jest tests
node .tmp/copilot/scripts/generate-jest-tests.cjs

# 5. Update index
node .tmp/copilot/scripts/update-harness-index.cjs
```

## Test Coverage

| Category             | Count                   |
| -------------------- | ----------------------- |
| Blade views with JS  | 495                     |
| Generated HTML pages | 470                     |
| Playwright specs     | 119 files (~2018 tests) |
| Jest specs           | 115 files (~1194 tests) |
| Resources covered    | 119                     |

## Adding New Tests

### Manual Test Addition

1. Create HTML page in `tests/harness/pages/`:

   ```html
   <!DOCTYPE html>
   <html>
     <head>
       <title>my-route - Test Harness</title>
       <!-- Bootstrap CSS -->
     </head>
     <body>
       <!-- Mock DOM matching production -->
       <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
       <script
         type="module"
         src="/dist/public/assets/js/routes/my/route.js"
       ></script>
     </body>
   </html>
   ```

2. Create spec in `tests/harness-specs/`:

   ```typescript
   import { test, expect } from "@playwright/test";

   test.describe("my-route", () => {
     test("should load without errors", async ({ page }) => {
       const errors: string[] = [];
       page.on("pageerror", err => errors.push(err.message));
       await page.goto("/harness/pages/my-route.html");
       await page.waitForLoadState("networkidle");
       expect(errors).toHaveLength(0);
     });
   });
   ```

## Cache Clearing

After running PHP scripts, clear Laravel caches:

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
composer run clear-logs
```

## CI Integration

Tests can be run in CI pipelines:

```yaml
# GitHub Actions example
- name: Build TypeScript
  run: npm run build
  working-directory: _inc/laravel/ts

- name: Run Unit Tests
  run: npm run test:unit
  working-directory: _inc/laravel/ts

- name: Install Playwright Browsers
  run: npx playwright install --with-deps
  working-directory: _inc/laravel/ts

- name: Run E2E Tests
  run: npm run test:harness
  working-directory: _inc/laravel/ts
```

## Troubleshooting

### Test artifacts (gitignored)

The following are generated at runtime and excluded from version control:

- `playwright-report/` — HTML report from Playwright runs
- `test-results/` — Playwright test result metadata
- `test-results.json` — JSON summary of last Playwright run

### "Cannot find module" errors

```bash
npm run build:clean  # Rebuild from scratch
```

### Playwright browser issues

```bash
npx playwright install --with-deps
```

### Test harness server issues

```bash
# Check if port 3333 is in use
lsof -i :3333
# Kill existing processes
pkill -f serve-harness
```

### Script loading errors

Ensure scripts use `type="module"` since compiled JS includes ES module exports:

```html
<script type="module" src="/dist/..."></script>
```
