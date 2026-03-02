# Frontend Playwright Test Suite

Comprehensive client-side testing for the ERP Prestech application using Playwright.

## Overview

This test suite validates the client-side behavior through mock pages that simulate:

- **RBAC (Role-Based Access Control)** - Testing access restrictions per user role
- **API Response Handling** - Testing success, error, and edge case responses
- **Form Validation** - Testing input validation and form interactions
- **Navigation & Routing** - Testing route protection and navigation behavior

## Directory Structure

```
tests/frontend/js/
├── e2e/                          # Playwright test specifications
│   ├── rbac.spec.ts              # RBAC tests for all 6 roles
│   ├── api-responses.spec.ts     # API response handling tests
│   ├── forms.spec.ts             # Form validation tests
│   └── navigation.spec.ts        # Navigation and routing tests
├── pages/mocks/                  # Mock HTML pages for testing
│   ├── rbac/                     # Role-specific mock pages
│   │   ├── index.html            # Test scenario index
│   │   ├── super-admin.html      # Super Admin role
│   │   ├── admin.html            # Admin role
│   │   ├── hr.html               # HR role
│   │   ├── accountant.html       # Accountant role
│   │   ├── client.html           # Client role
│   │   ├── guest.html            # Unauthenticated guest
│   │   └── api-scenarios.html    # API response scenarios
│   └── common/
│       └── rbac-styles.css       # Shared CSS styles
└── utils/
    └── rbac-test-utils.ts        # Test utilities and helpers
```

## Running Tests

### Prerequisites

Ensure Playwright is installed:

```bash
cd _inc/laravel
npm install
npx playwright install
```

### Run All Frontend Tests

```bash
npm run test:frontend
```

### Run Individual Test Suites

```bash
# RBAC tests only
npm run test:frontend:rbac

# API response tests only
npm run test:frontend:api

# Form validation tests only
npm run test:frontend:forms

# Navigation tests only
npm run test:frontend:nav
```

### Interactive/Debug Modes

```bash
# Run with browser visible
npm run test:frontend:headed

# Run in debug mode
npm run test:frontend:debug

# Open Playwright UI
npm run test:frontend:ui
```

### Shell Script Runner

```bash
# Make executable
chmod +x tests/frontend/run-frontend-tests.sh

# Run all
./tests/frontend/run-frontend-tests.sh

# Run specific suite
./tests/frontend/run-frontend-tests.sh rbac
./tests/frontend/run-frontend-tests.sh api
./tests/frontend/run-frontend-tests.sh forms
./tests/frontend/run-frontend-tests.sh nav

# With options
./tests/frontend/run-frontend-tests.sh --headed
./tests/frontend/run-frontend-tests.sh rbac --debug
```

### View Test Report

```bash
npm run test:frontend:report
```

## Test Coverage

### RBAC Tests (`rbac.spec.ts`)

- **Super Admin**: Full access to all modules, settings, admin panel
- **Admin**: Limited system settings, no super admin features
- **HR**: HRM module only (employees, attendance, payroll)
- **Accountant**: Finance module only (invoices, bills, expenses)
- **Client**: Portal access only (own projects, invoices, support)
- **Guest**: Login/register only, protected content hidden

Tests verify:

- Navigation link visibility
- Sidebar section visibility
- Widget visibility
- Action button accessibility
- Permission hierarchy enforcement
- Inline `window.runRbacTests()` execution

### API Response Tests (`api-responses.spec.ts`)

- Success responses (200 OK, 201 Created)
- Client errors (400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 422 Validation Error)
- Server errors (500 Internal Server Error, 503 Service Unavailable)
- Network errors (timeout, connection refused)
- CRUD operations lifecycle
- Form submission workflows
- Response time validation
- Error recovery mechanisms

### Form Validation Tests (`forms.spec.ts`)

- Form structure validation
- Required field validation
- Email format validation
- Form reset functionality
- Tab order and keyboard navigation
- Submit button behavior
- Error state management
- Accessibility compliance
- Edge cases (special characters, international input, emoji, long text)

### Navigation Tests (`navigation.spec.ts`)

- Navigation structure per role
- Sidebar visibility per role
- Link click behavior and hash updates
- Route protection enforcement
- Active state management
- Keyboard navigation accessibility
- User menu display
- Page headers per role

## Mock Page Features

### Permission-Based Visibility

All mock pages use `data-permission` attributes to control element visibility:

```html
<a href="#settings" data-permission="manage system settings">Settings</a>
<button data-permission="create user">+ New User</button>
<div class="widget" data-permission="show hrm dashboard">...</div>
```

The `hideElementsWithoutPermission()` utility automatically hides elements the user cannot access.

### Role Templates

Pre-configured role permissions in `rbac-test-utils.ts`:

```typescript
const RoleTemplates = {
  superAdmin: [
    /* all 100+ permissions */
  ],
  admin: [
    /* admin-level permissions */
  ],
  hr: [
    /* HRM permissions only */
  ],
  accountant: [
    /* finance permissions only */
  ],
  client: [
    /* client portal permissions */
  ],
  guest: [
    /* empty - no permissions */
  ],
};
```

### Mock Fetch API

Each page includes `createMockFetch()` for simulating API responses:

```typescript
const mockFetch = createMockFetch({
  delay: 500,
  requireAuth: true,
  defaultResponse: { status: 200, data: { users: [] } },
  routes: {
    '/api/users': { status: 200, data: [...] },
    '/api/admin': { status: 403, error: 'Forbidden' },
  }
});
```

### Inline Test Suite

Each mock page includes a `window.runRbacTests()` function that can be executed by Playwright to validate page-specific behavior.

## Configuration

### `playwright-frontend.config.cjs`

The frontend test configuration includes:

- Test directory: `./tests/frontend/js/e2e`
- Browsers: Chromium, Firefox, WebKit, Mobile Chrome, Mobile Safari
- Parallel execution enabled
- HTML and JSON reporters
- Screenshot/video on failure

## Adding New Tests

### Create a New Mock Page

1. Add HTML file to `pages/mocks/rbac/` or relevant folder
2. Include `<link rel="stylesheet" href="../common/rbac-styles.css" />`
3. Set `data-role` on body element
4. Add `data-permission` attributes to access-controlled elements
5. Implement `window.runRbacTests()` for inline tests
6. Call `hideElementsWithoutPermission(user.permissions)` on load

### Create a New Test Spec

1. Add `.spec.ts` file to `e2e/`
2. Import from `@playwright/test`
3. Use `file://` protocol to load mock pages:
   ```typescript
   await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/page.html`);
   ```
4. Add npm script to `package.json` if desired

## Notes

- Tests use `file://` protocol and do not require a running server
- All tests are isolated and self-contained
- Mock pages simulate real application structure and behavior
- Permission system mirrors PHP `PermissionsConstants.php` exactly
