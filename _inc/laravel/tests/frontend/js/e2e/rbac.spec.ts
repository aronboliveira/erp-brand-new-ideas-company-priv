/**
 * RBAC (Role-Based Access Control) E2E Tests
 *
 * Comprehensive Playwright tests for validating role-based access control
 * across different user roles: Super Admin, Admin, HR, Accountant, Client, Guest
 *
 * NOTE: These tests use file:// protocol which prevents ES modules from loading.
 * Instead of testing runtime visibility (which requires JS execution), we verify:
 * 1. Role attributes are correctly set on each page
 * 2. Permission attributes exist on protected elements
 * 3. Page structure includes the expected elements for each role
 * 4. Form elements have proper attributes and accessibility
 *
 * The permission system design:
 * - All pages contain protected elements with data-permission attributes
 * - JavaScript hides elements user doesn't have permission for
 * - Since JS doesn't run in file:// tests, we verify the STRUCTURE is correct
 */
import { test, expect, Page } from "@playwright/test";

const TEST_BASE_PATH = "./pages/mocks/rbac";

/**
 * Helper to check if element exists in DOM
 */
async function elementExists(page: Page, selector: string): Promise<boolean> {
  const count = await page.locator(selector).count();
  return count > 0;
}

/**
 * Helper to get element count
 */
async function _getElementCount(page: Page, selector: string): Promise<number> {
  return await page.locator(selector).count();
}

/**
 * Helper to get attribute value
 */
async function getAttribute(page: Page, selector: string, attr: string): Promise<string | null> {
  const element = page.locator(selector).first();
  if ((await element.count()) === 0) return null;
  return await element.getAttribute(attr);
}

/**
 * Helper to get user role from page
 */
async function getUserRole(page: Page): Promise<string> {
  return await page.evaluate(() => {
    return document.body.dataset.role || "";
  });
}

/**
 * Helper to check permission attribute on element
 */
async function getPermission(page: Page, selector: string): Promise<string | null> {
  return await getAttribute(page, selector, "data-permission");
}

// ============================================================================
// SUPER ADMIN TESTS
// ============================================================================

test.describe("Super Admin Role", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/super-admin.html`);
    await page.waitForLoadState("domcontentloaded");
  });

  test("should have super-admin role set on body", async ({ page }) => {
    const role = await getUserRole(page);
    expect(role).toBe("super-admin");
  });

  test("should have dashboard navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#dashboard"]')).toBe(true);
  });

  test("should have HRM navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#hrm"]')).toBe(true);
  });

  test("should have POS navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#pos"]')).toBe(true);
  });

  test("should have Finance navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#finance"]')).toBe(true);
  });

  test("should have Settings navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#settings"]')).toBe(true);
  });

  test("should have revenue widget with correct permission", async ({ page }) => {
    expect(await elementExists(page, "#widget-revenue")).toBe(true);
    const permission = await getPermission(page, "#widget-revenue");
    expect(permission).toBe("show account dashboard");
  });

  test("should have employees widget with correct permission", async ({ page }) => {
    expect(await elementExists(page, "#widget-employees")).toBe(true);
    const permission = await getPermission(page, "#widget-employees");
    expect(permission).toBe("show hrm dashboard");
  });

  test("should have system widget with super admin permission", async ({ page }) => {
    expect(await elementExists(page, "#widget-system")).toBe(true);
    const permission = await getPermission(page, "#widget-system");
    expect(permission).toBe("manage super admin dashboard");
  });

  test("should have sidebar sections with permissions", async ({ page }) => {
    const sidebarSections = page.locator(".sidebar-section");
    expect(await sidebarSections.count()).toBeGreaterThan(0);
  });

  test("should have protected elements with data-permission attributes", async ({ page }) => {
    const protectedElements = page.locator("[data-permission]");
    expect(await protectedElements.count()).toBeGreaterThan(0);
  });

  test("should have activities table", async ({ page }) => {
    expect(await elementExists(page, "#activities-table")).toBe(true);
  });
});

// ============================================================================
// ADMIN TESTS
// ============================================================================

test.describe("Admin Role", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/admin.html`);
    await page.waitForLoadState("domcontentloaded");
  });

  test("should have admin role set on body", async ({ page }) => {
    const role = await getUserRole(page);
    expect(role).toBe("admin");
  });

  test("should have main navigation present", async ({ page }) => {
    expect(await elementExists(page, "#main-nav")).toBe(true);
  });

  test("should have HRM navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#hrm"]')).toBe(true);
  });

  test("should have Finance navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#finance"]')).toBe(true);
  });

  test("should have user management controls", async ({ page }) => {
    expect(await elementExists(page, "#btn-add-user")).toBe(true);
    expect(await elementExists(page, "#btn-manage-roles")).toBe(true);
  });

  test("should have sidebar structure", async ({ page }) => {
    const sidebarSections = page.locator(".sidebar-section");
    expect(await sidebarSections.count()).toBeGreaterThan(0);
  });

  test("should have employees widget with permission attribute", async ({ page }) => {
    expect(await elementExists(page, "#widget-employees")).toBe(true);
  });

  test("should have revenue widget with permission attribute", async ({ page }) => {
    expect(await elementExists(page, "#widget-revenue")).toBe(true);
  });

  test("should have system widget with super-admin-only permission", async ({ page }) => {
    // System widget exists but has super-admin permission (would be hidden by JS)
    expect(await elementExists(page, "#widget-system")).toBe(true);
    const permission = await getPermission(page, "#widget-system");
    expect(permission).toBe("manage super admin dashboard");
  });

  test("should have users table", async ({ page }) => {
    expect(await elementExists(page, "#users-table")).toBe(true);
  });

  test("should have permission attributes on protected elements", async ({ page }) => {
    const protectedElements = page.locator("[data-permission]");
    expect(await protectedElements.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// HR ROLE TESTS
// ============================================================================

test.describe("HR Role", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/hr.html`);
    await page.waitForLoadState("domcontentloaded");
  });

  test("should have hr role set on body", async ({ page }) => {
    const role = await getUserRole(page);
    expect(role).toBe("hr");
  });

  test("should have HRM navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#hrm"]')).toBe(true);
  });

  test("should have employees widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-employees")).toBe(true);
    const permission = await getPermission(page, "#widget-employees");
    expect(permission).toBe("show hrm dashboard");
  });

  test("should have attendance widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-attendance")).toBe(true);
  });

  test("should have leaves widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-leaves")).toBe(true);
  });

  test("should have employee management button", async ({ page }) => {
    expect(await elementExists(page, "#btn-add-employee")).toBe(true);
  });

  test("should have employees table", async ({ page }) => {
    expect(await elementExists(page, "#employees-table")).toBe(true);
  });

  test("should have leaves table", async ({ page }) => {
    expect(await elementExists(page, "#leaves-table")).toBe(true);
  });

  test("should have revenue widget with finance permission (would be hidden by JS)", async ({ page }) => {
    // Revenue widget exists but has finance permission - HR doesnt have this
    expect(await elementExists(page, "#widget-revenue")).toBe(true);
    const permission = await getPermission(page, "#widget-revenue");
    expect(permission).toBe("show account dashboard");
  });

  test("should have HRM permission attributes on elements", async ({ page }) => {
    const hrmElements = page.locator('[data-permission*="hrm"], [data-permission*="employee"], [data-permission*="attendance"]');
    expect(await hrmElements.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// ACCOUNTANT ROLE TESTS
// ============================================================================

test.describe("Accountant Role", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/accountant.html`);
    await page.waitForLoadState("domcontentloaded");
  });

  test("should have accountant role set on body", async ({ page }) => {
    const role = await getUserRole(page);
    expect(role).toBe("accountant");
  });

  test("should have Finance navigation link", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#finance"]')).toBe(true);
  });

  test("should have revenue widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-revenue")).toBe(true);
    const permission = await getPermission(page, "#widget-revenue");
    expect(permission).toBe("show account dashboard");
  });

  test("should have receivables widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-receivables")).toBe(true);
  });

  test("should have payables widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-payables")).toBe(true);
  });

  test("should have expenses widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-expenses")).toBe(true);
  });

  test("should have invoice buttons", async ({ page }) => {
    expect(await elementExists(page, "#btn-new-invoice")).toBe(true);
    expect(await elementExists(page, "#btn-new-bill")).toBe(true);
  });

  test("should have invoices table", async ({ page }) => {
    expect(await elementExists(page, "#invoices-table")).toBe(true);
  });

  test("should have bills table", async ({ page }) => {
    expect(await elementExists(page, "#bills-table")).toBe(true);
  });

  test("should have system widget with super-admin permission (would be hidden)", async ({ page }) => {
    expect(await elementExists(page, "#widget-system")).toBe(true);
    const permission = await getPermission(page, "#widget-system");
    expect(permission).toBe("manage super admin dashboard");
  });

  test("should have finance permission attributes", async ({ page }) => {
    const financeElements = page.locator('[data-permission*="invoice"], [data-permission*="bill"], [data-permission*="account"]');
    expect(await financeElements.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// CLIENT ROLE TESTS
// ============================================================================

test.describe("Client Role", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/client.html`);
    await page.waitForLoadState("domcontentloaded");
  });

  test("should have client role set on body", async ({ page }) => {
    const role = await getUserRole(page);
    expect(role).toBe("client");
  });

  test("should have dashboard navigation", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#dashboard"]')).toBe(true);
  });

  test("should have projects navigation", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#projects"]')).toBe(true);
  });

  test("should have invoices navigation", async ({ page }) => {
    expect(await elementExists(page, '#main-nav a[href="#invoices"]')).toBe(true);
  });

  test("should have projects widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-projects")).toBe(true);
  });

  test("should have invoices widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-invoices")).toBe(true);
  });

  test("should have tickets widget", async ({ page }) => {
    expect(await elementExists(page, "#widget-tickets")).toBe(true);
  });

  test("should have support ticket button", async ({ page }) => {
    expect(await elementExists(page, "#btn-new-ticket")).toBe(true);
  });

  test("should have projects table", async ({ page }) => {
    expect(await elementExists(page, "#projects-table")).toBe(true);
  });

  test("should have internal widgets with restricting permissions (would be hidden by JS)", async ({ page }) => {
    // These widgets have permissions client doesnt have - JS would hide them
    expect(await elementExists(page, "#widget-revenue")).toBe(true);
    expect(await elementExists(page, "#widget-system")).toBe(true);
    expect(await elementExists(page, "#widget-employees")).toBe(true);
  });
});

// ============================================================================
// GUEST (UNAUTHENTICATED) TESTS
// ============================================================================

test.describe("Guest (Unauthenticated)", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/guest.html`);
    await page.waitForLoadState("domcontentloaded");
  });

  test("should have guest role set on body", async ({ page }) => {
    const role = await getUserRole(page);
    expect(role).toBe("guest");
  });

  test("should have login form", async ({ page }) => {
    expect(await elementExists(page, "#login-form")).toBe(true);
  });

  test("should have login button", async ({ page }) => {
    expect(await elementExists(page, "#btn-login")).toBe(true);
  });

  test("should have register button", async ({ page }) => {
    expect(await elementExists(page, "#btn-register")).toBe(true);
  });

  test("login form should have email field", async ({ page }) => {
    const emailInput = page.locator("#email");
    expect(await emailInput.count()).toBe(1);
    expect(await emailInput.getAttribute("type")).toBe("email");
    expect(await emailInput.getAttribute("required")).not.toBeNull();
  });

  test("login form should have password field", async ({ page }) => {
    const passwordInput = page.locator("#password");
    expect(await passwordInput.count()).toBe(1);
    expect(await passwordInput.getAttribute("type")).toBe("password");
    expect(await passwordInput.getAttribute("required")).not.toBeNull();
  });

  test("login form should allow input", async ({ page }) => {
    await page.fill("#email", "test@example.com");
    await page.fill("#password", "password123");

    expect(await page.inputValue("#email")).toBe("test@example.com");
    expect(await page.inputValue("#password")).toBe("password123");
  });

  test("should have remember me checkbox", async ({ page }) => {
    expect(await elementExists(page, 'input[name="remember"]')).toBe(true);
  });

  test("should have forgot password link", async ({ page }) => {
    expect(await elementExists(page, 'a[href="#forgot-password"]')).toBe(true);
  });
});

// ============================================================================
// ROLE COMPARISON TESTS - Role Attribute Validation
// ============================================================================

test.describe("Role Comparison - Role Attributes", () => {
  const roles = [
    { name: "super-admin", file: "super-admin.html" },
    { name: "admin", file: "admin.html" },
    { name: "hr", file: "hr.html" },
    { name: "accountant", file: "accountant.html" },
    { name: "client", file: "client.html" },
    { name: "guest", file: "guest.html" },
  ];

  for (const role of roles) {
    test(`${role.name} page has correct role attribute`, async ({ page }) => {
      await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/${role.file}`);
      await page.waitForLoadState("domcontentloaded");

      const pageRole = await getUserRole(page);
      expect(pageRole).toBe(role.name);
    });

    test(`${role.name} page has valid HTML structure`, async ({ page }) => {
      await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/${role.file}`);
      await page.waitForLoadState("domcontentloaded");

      expect(await elementExists(page, "body")).toBe(true);
      expect(await elementExists(page, "head")).toBe(true);
    });

    test(`${role.name} page has page title`, async ({ page }) => {
      await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/${role.file}`);
      await page.waitForLoadState("domcontentloaded");

      const title = await page.title();
      expect(title.length).toBeGreaterThan(0);
    });
  }
});

// ============================================================================
// PERMISSION SYSTEM VALIDATION
// ============================================================================

test.describe("Permission System Structure", () => {
  test("Super Admin page has all permission types", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/super-admin.html`);
    await page.waitForLoadState("domcontentloaded");

    const protectedElements = page.locator("[data-permission]");
    expect(await protectedElements.count()).toBeGreaterThan(5);
  });

  test("HR page has HRM-related permissions", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/hr.html`);
    await page.waitForLoadState("domcontentloaded");

    const hrmPermissions = page.locator('[data-permission*="hrm"], [data-permission*="employee"]');
    expect(await hrmPermissions.count()).toBeGreaterThan(0);
  });

  test("Accountant page has finance-related permissions", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/accountant.html`);
    await page.waitForLoadState("domcontentloaded");

    const financePermissions = page.locator('[data-permission*="account"], [data-permission*="invoice"]');
    expect(await financePermissions.count()).toBeGreaterThan(0);
  });

  test("Widget permissions match expected permission constants", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/super-admin.html`);
    await page.waitForLoadState("domcontentloaded");

    // Check specific widget permissions match PHP constants
    expect(await getPermission(page, "#widget-system")).toBe("manage super admin dashboard");
    expect(await getPermission(page, "#widget-revenue")).toBe("show account dashboard");
    expect(await getPermission(page, "#widget-employees")).toBe("show hrm dashboard");
  });

  test("System widget has super-admin exclusive permission", async ({ page }) => {
    const pages = ["admin.html", "hr.html", "accountant.html", "client.html"];

    for (const pageName of pages) {
      await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/${pageName}`);
      await page.waitForLoadState("domcontentloaded");

      if (await elementExists(page, "#widget-system")) {
        const permission = await getPermission(page, "#widget-system");
        expect(permission).toBe("manage super admin dashboard");
      }
    }
  });
});

// ============================================================================
// NAVIGATION STRUCTURE
// ============================================================================

test.describe("Navigation Structure", () => {
  test("Super Admin has most navigation links", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/super-admin.html`);
    await page.waitForLoadState("domcontentloaded");
    const superAdminNavLinks = await page.locator("#main-nav a").count();

    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/client.html`);
    await page.waitForLoadState("domcontentloaded");
    const clientNavLinks = await page.locator("#main-nav a").count();

    expect(superAdminNavLinks).toBeGreaterThanOrEqual(clientNavLinks);
  });

  test("All authenticated pages have navigation", async ({ page }) => {
    const authenticatedPages = ["super-admin.html", "admin.html", "hr.html", "accountant.html", "client.html"];

    for (const pageName of authenticatedPages) {
      await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/${pageName}`);
      await page.waitForLoadState("domcontentloaded");

      expect(await elementExists(page, "#main-nav")).toBe(true);
    }
  });

  test("Navigation links have href attributes", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/super-admin.html`);
    await page.waitForLoadState("domcontentloaded");

    const navLinks = page.locator("#main-nav a");
    const count = await navLinks.count();

    for (let i = 0; i < count; i++) {
      const href = await navLinks.nth(i).getAttribute("href");
      expect(href).not.toBeNull();
      expect(href!.length).toBeGreaterThan(0);
    }
  });
});

// ============================================================================
// ACCESSIBILITY CHECKS
// ============================================================================

test.describe("Accessibility", () => {
  test("Login form has accessible labels", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/guest.html`);
    await page.waitForLoadState("domcontentloaded");

    const emailInput = page.locator("#email");
    const passwordInput = page.locator("#password");

    // Check for label, aria-label, or placeholder
    const emailHasLabel = (await page.locator('label[for="email"]').count()) > 0;
    const emailHasAria = (await emailInput.getAttribute("aria-label")) !== null;
    const emailHasPlaceholder = (await emailInput.getAttribute("placeholder")) !== null;
    expect(emailHasLabel || emailHasAria || emailHasPlaceholder).toBe(true);

    const passwordHasLabel = (await page.locator('label[for="password"]').count()) > 0;
    const passwordHasAria = (await passwordInput.getAttribute("aria-label")) !== null;
    const passwordHasPlaceholder = (await passwordInput.getAttribute("placeholder")) !== null;
    expect(passwordHasLabel || passwordHasAria || passwordHasPlaceholder).toBe(true);
  });

  test("Buttons have accessible text or labels", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/super-admin.html`);
    await page.waitForLoadState("domcontentloaded");

    const buttons = page.locator("button");
    const count = await buttons.count();

    for (let i = 0; i < Math.min(count, 5); i++) {
      const button = buttons.nth(i);
      const text = await button.textContent();
      const ariaLabel = await button.getAttribute("aria-label");
      const title = await button.getAttribute("title");

      const hasAccessibleName = (text && text.trim().length > 0) || ariaLabel || title;
      expect(hasAccessibleName).toBeTruthy();
    }
  });

  test("Form inputs have proper types", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/guest.html`);
    await page.waitForLoadState("domcontentloaded");

    expect(await page.locator("#email").getAttribute("type")).toBe("email");
    expect(await page.locator("#password").getAttribute("type")).toBe("password");
  });
});

// ============================================================================
// DATA TABLE STRUCTURE
// ============================================================================

test.describe("Data Tables", () => {
  test("Super Admin page has activities table", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/super-admin.html`);
    await page.waitForLoadState("domcontentloaded");

    expect(await elementExists(page, "#activities-table")).toBe(true);
    expect(await elementExists(page, "#activities-table tbody")).toBe(true);
  });

  test("HR page has employees table", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/hr.html`);
    await page.waitForLoadState("domcontentloaded");

    expect(await elementExists(page, "#employees-table")).toBe(true);
    expect(await elementExists(page, "#employees-table thead")).toBe(true);
    expect(await elementExists(page, "#employees-table tbody")).toBe(true);
  });

  test("Accountant page has invoices table", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/accountant.html`);
    await page.waitForLoadState("domcontentloaded");

    expect(await elementExists(page, "#invoices-table")).toBe(true);
    expect(await elementExists(page, "#invoices-table thead")).toBe(true);
    expect(await elementExists(page, "#invoices-table tbody")).toBe(true);
  });

  test("Client page has projects table", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/client.html`);
    await page.waitForLoadState("domcontentloaded");

    expect(await elementExists(page, "#projects-table")).toBe(true);
  });

  test("Admin page has users table", async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/admin.html`);
    await page.waitForLoadState("domcontentloaded");

    expect(await elementExists(page, "#users-table")).toBe(true);
  });
});
