/**
 * Form Validation E2E Tests
 *
 * Tests for validating form behavior, input validation, and error handling
 * across various form scenarios.
 */
import { test, expect, Page } from "@playwright/test";

const TEST_BASE_PATH = "./pages/mocks/rbac";

/**
 * Helper to check if element is visible
 */
async function isVisible(page: Page, selector: string): Promise<boolean> {
  const element = page.locator(selector).first();
  const count = await element.count();
  if (count === 0) return false;
  return await element.isVisible();
}

// ============================================================================
// FORM STRUCTURE TESTS
// ============================================================================

test.describe("Form Structure", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("form should exist and be visible", async ({ page }) => {
    expect(await isVisible(page, "#test-form")).toBe(true);
  });

  test("form should have name input with correct type", async ({ page }) => {
    const nameInput = page.locator("#form-name");
    expect(await nameInput.count()).toBe(1);
    expect(await nameInput.getAttribute("type")).toBe("text");
  });

  test("form should have email input with correct type", async ({ page }) => {
    const emailInput = page.locator("#form-email");
    expect(await emailInput.count()).toBe(1);
    expect(await emailInput.getAttribute("type")).toBe("email");
  });

  test("form should have role select dropdown", async ({ page }) => {
    const roleSelect = page.locator("#form-role");
    expect(await roleSelect.count()).toBe(1);

    const options = page.locator("#form-role option");
    expect(await options.count()).toBeGreaterThan(1);
  });

  test("form should have submit button", async ({ page }) => {
    const submitBtn = page.locator("#btn-submit");
    expect(await submitBtn.count()).toBe(1);
    expect(await submitBtn.getAttribute("type")).toBe("submit");
  });

  test("form should have reset button", async ({ page }) => {
    const resetBtn = page.locator('button[type="reset"]');
    expect(await resetBtn.count()).toBe(1);
  });

  test("form should have error message containers", async ({ page }) => {
    const errorElements = page.locator(".field-error");
    expect(await errorElements.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// INPUT VALIDATION TESTS
// ============================================================================

test.describe("Input Validation", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("name field should be required", async ({ page }) => {
    const nameInput = page.locator("#form-name");
    expect(await nameInput.getAttribute("required")).not.toBeNull();
  });

  test("email field should be required", async ({ page }) => {
    const emailInput = page.locator("#form-email");
    expect(await emailInput.getAttribute("required")).not.toBeNull();
  });

  test("email field validates email format", async ({ page }) => {
    const emailInput = page.locator("#form-email");

    // Enter invalid email
    await emailInput.fill("notanemail");

    // Check HTML5 validation
    const isValid = await emailInput.evaluate(
      (el: HTMLInputElement) => el.validity.valid,
    );
    expect(isValid).toBe(false);
  });

  test("valid email passes validation", async ({ page }) => {
    const emailInput = page.locator("#form-email");

    await emailInput.fill("test@example.com");

    const isValid = await emailInput.evaluate(
      (el: HTMLInputElement) => el.validity.valid,
    );
    expect(isValid).toBe(true);
  });

  test("empty required fields prevent submission", async ({ page }) => {
    // Clear fields just in case
    await page.fill("#form-name", "");
    await page.fill("#form-email", "");

    // Try to submit
    await page.locator("#btn-submit").click({ force: true });

    // Form should not have submitted (no response panel update)
    const responsePanel = page.locator(".response-panel .response-empty");
    const hasEmptyMessage =
      (await responsePanel.count()) > 0 && (await responsePanel.isVisible());

    // If empty message is still visible, form didn't submit
    expect(hasEmptyMessage).toBe(true);
  });
});

// ============================================================================
// FORM INTERACTION TESTS
// ============================================================================

test.describe("Form Interactions", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("can fill name field", async ({ page }) => {
    await page.fill("#form-name", "Test User");
    const value = await page.locator("#form-name").inputValue();
    expect(value).toBe("Test User");
  });

  test("can fill email field", async ({ page }) => {
    await page.fill("#form-email", "test@example.com");
    const value = await page.locator("#form-email").inputValue();
    expect(value).toBe("test@example.com");
  });

  test("can select role from dropdown", async ({ page }) => {
    await page.selectOption("#form-role", "admin");
    const value = await page.locator("#form-role").inputValue();
    expect(value).toBe("admin");
  });

  test("can select different roles", async ({ page }) => {
    const roles = ["admin", "hr", "accountant"];

    for (const role of roles) {
      await page.selectOption("#form-role", role);
      const value = await page.locator("#form-role").inputValue();
      expect(value).toBe(role);
    }
  });

  test("reset button clears all fields", async ({ page }) => {
    // Fill form
    await page.fill("#form-name", "Test User");
    await page.fill("#form-email", "test@example.com");
    await page.selectOption("#form-role", "admin");

    // Reset
    await page.locator('button[type="reset"]').click({ force: true });

    // Check all fields are empty
    expect(await page.locator("#form-name").inputValue()).toBe("");
    expect(await page.locator("#form-email").inputValue()).toBe("");
    expect(await page.locator("#form-role").inputValue()).toBe("");
  });

  test("can tab through form fields", async ({ page }) => {
    // Focus on name
    await page.locator("#form-name").focus();

    // Tab to email
    await page.keyboard.press("Tab");
    const emailFocused = await page.evaluate(() => document.activeElement?.id);
    expect(emailFocused).toBe("form-email");

    // Tab to role
    await page.keyboard.press("Tab");
    const roleFocused = await page.evaluate(() => document.activeElement?.id);
    expect(roleFocused).toBe("form-role");
  });
});

// ============================================================================
// FORM SUBMISSION TESTS
// ============================================================================

test.describe("Form Submission", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("valid form submission shows success", async ({ page }) => {
    await page.fill("#form-name", "John Doe");
    await page.fill("#form-email", "john@example.com");
    await page.selectOption("#form-role", "admin");

    await page.locator("#btn-submit").click({ force: true });

    // Wait for success notification
    await page.waitForSelector(".notification.success", {
      state: "visible",
      timeout: 5000,
    });
    const notification = page.locator(".notification.success");
    expect(await notification.count()).toBeGreaterThan(0);
  });

  test("form result area updates on success", async ({ page }) => {
    await page.fill("#form-name", "John Doe");
    await page.fill("#form-email", "john@example.com");

    await page.locator("#btn-submit").click({ force: true });

    // Wait for result
    await page.waitForSelector("#form-result .notification.success", {
      state: "visible",
      timeout: 5000,
    });
    const resultText = await page.locator("#form-result").textContent();
    expect(resultText?.toLowerCase()).toContain("success");
  });

  test("validation error shows field-specific messages", async ({ page }) => {
    // Using "error" as name triggers validation error
    await page.fill("#form-name", "error");
    await page.fill("#form-email", "test@example.com");

    await page.locator("#btn-submit").click({ force: true });

    // Wait for response
    await page.waitForSelector(".response-meta", {
      state: "visible",
      timeout: 5000,
    });

    // Should show field errors
    const errorElements = page.locator(".field-error");
    let hasError = false;

    const count = await errorElements.count();
    for (let i = 0; i < count; i++) {
      const text = await errorElements.nth(i).textContent();
      if (text && text.trim().length > 0) {
        hasError = true;
        break;
      }
    }

    expect(hasError).toBe(true);
  });

  test("submit button text is correct", async ({ page }) => {
    const submitText = await page.locator("#btn-submit").textContent();
    expect(submitText?.toLowerCase()).toContain("submit");
  });
});

// ============================================================================
// ERROR STATE TESTS
// ============================================================================

test.describe("Error States", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("error messages are initially empty", async ({ page }) => {
    const errorElements = page.locator(".field-error");
    const count = await errorElements.count();

    for (let i = 0; i < count; i++) {
      const text = await errorElements.nth(i).textContent();
      expect(text?.trim()).toBe("");
    }
  });

  test("error container styling exists", async ({ page }) => {
    const errorElement = page.locator(".field-error").first();
    expect(await errorElement.count()).toBe(1);
  });

  test("clearing errors after fix allows resubmission", async ({ page }) => {
    // First submission with error trigger
    await page.fill("#form-name", "error");
    await page.fill("#form-email", "test@example.com");
    await page.locator("#btn-submit").click({ force: true });
    await page.waitForSelector(".response-meta", {
      state: "visible",
      timeout: 5000,
    });

    // Fix the form
    await page.fill("#form-name", "Valid Name");
    await page.locator("#btn-submit").click({ force: true });
    await page.waitForSelector(".notification.success", {
      state: "visible",
      timeout: 5000,
    });

    // Should succeed now
    const notification = page.locator(".notification.success");
    expect(await notification.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// ACCESSIBILITY TESTS
// ============================================================================

test.describe("Form Accessibility", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("form fields have associated labels", async ({ page }) => {
    const nameLabel = page.locator('label[for="form-name"]');
    const emailLabel = page.locator('label[for="form-email"]');
    const roleLabel = page.locator('label[for="form-role"]');

    expect(await nameLabel.count()).toBe(1);
    expect(await emailLabel.count()).toBe(1);
    expect(await roleLabel.count()).toBe(1);
  });

  test("labels have visible text", async ({ page }) => {
    const nameLabel = await page
      .locator('label[for="form-name"]')
      .textContent();
    const emailLabel = await page
      .locator('label[for="form-email"]')
      .textContent();
    const roleLabel = await page
      .locator('label[for="form-role"]')
      .textContent();

    expect(nameLabel?.toLowerCase()).toContain("name");
    expect(emailLabel?.toLowerCase()).toContain("email");
    expect(roleLabel?.toLowerCase()).toContain("role");
  });

  test("form can be submitted with Enter key", async ({ page }) => {
    await page.fill("#form-name", "John Doe");
    await page.fill("#form-email", "john@example.com");

    // Press Enter in the email field
    await page.locator("#form-email").press("Enter");

    // Should trigger submission
    await page.waitForSelector(".notification", {
      state: "visible",
      timeout: 5000,
    });
  });

  test("form preserves focus order", async ({ page }) => {
    const form = page.locator("#test-form");
    const focusableElements = await form.locator("input, select, button").all();

    expect(focusableElements.length).toBeGreaterThan(0);

    // Verify tabindex is not negative (which would remove from tab order)
    for (const element of focusableElements) {
      const tabindex = await element.getAttribute("tabindex");
      if (tabindex !== null) {
        expect(parseInt(tabindex, 10)).toBeGreaterThanOrEqual(0);
      }
    }
  });
});

// ============================================================================
// SPECIAL CHARACTERS AND EDGE CASES
// ============================================================================

test.describe("Edge Cases", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("handles special characters in name", async ({ page }) => {
    const specialName = "O'Brien-Smith";
    await page.fill("#form-name", specialName);
    await page.fill("#form-email", "test@example.com");

    await page.locator("#btn-submit").click({ force: true });
    await page.waitForSelector(".notification.success", {
      state: "visible",
      timeout: 5000,
    });

    // Should succeed
    const notification = page.locator(".notification.success");
    expect(await notification.count()).toBeGreaterThan(0);
  });

  test("handles international characters", async ({ page }) => {
    const internationalName = "José García";
    await page.fill("#form-name", internationalName);
    await page.fill("#form-email", "jose@ejemplo.com");

    const value = await page.locator("#form-name").inputValue();
    expect(value).toBe(internationalName);
  });

  test("handles emoji in input (if supported)", async ({ page }) => {
    const emojiName = "Test 😀";
    await page.fill("#form-name", emojiName);

    const value = await page.locator("#form-name").inputValue();
    expect(value).toBe(emojiName);
  });

  test("handles very long input", async ({ page }) => {
    const longName = "A".repeat(500);
    await page.fill("#form-name", longName);

    const value = await page.locator("#form-name").inputValue();
    expect(value.length).toBe(500);
  });

  test("handles whitespace-only input", async ({ page }) => {
    await page.fill("#form-name", "   ");
    await page.fill("#form-email", "test@example.com");

    // Form may or may not accept this depending on validation
    const nameValue = await page.locator("#form-name").inputValue();
    expect(nameValue).toBe("   ");
  });

  test("handles email with plus sign", async ({ page }) => {
    await page.fill("#form-name", "Test User");
    await page.fill("#form-email", "test+tag@example.com");

    const isValid = await page
      .locator("#form-email")
      .evaluate((el: HTMLInputElement) => el.validity.valid);
    expect(isValid).toBe(true);
  });

  test("handles email with subdomain", async ({ page }) => {
    await page.fill("#form-email", "test@mail.example.com");

    const isValid = await page
      .locator("#form-email")
      .evaluate((el: HTMLInputElement) => el.validity.valid);
    expect(isValid).toBe(true);
  });
});

// ============================================================================
// GUEST PAGE LOGIN FORM TESTS
// ============================================================================

test.describe("Guest Login Form", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`file://${process.cwd()}/${TEST_BASE_PATH}/guest.html`);
    await page.waitForLoadState("domcontentloaded");
  });

  test("login form is visible", async ({ page }) => {
    expect(await isVisible(page, "#login-form")).toBe(true);
  });

  test("login form has email and password fields", async ({ page }) => {
    expect(await isVisible(page, "#email")).toBe(true);
    expect(await isVisible(page, "#password")).toBe(true);
  });

  test("email field has correct type", async ({ page }) => {
    const type = await page.locator("#email").getAttribute("type");
    expect(type).toBe("email");
  });

  test("password field has correct type", async ({ page }) => {
    const type = await page.locator("#password").getAttribute("type");
    expect(type).toBe("password");
  });

  test("both fields are required", async ({ page }) => {
    expect(
      await page.locator("#email").getAttribute("required"),
    ).not.toBeNull();
    expect(
      await page.locator("#password").getAttribute("required"),
    ).not.toBeNull();
  });

  test("login form has remember me checkbox", async ({ page }) => {
    const checkbox = page.locator('input[type="checkbox"][name="remember"]');
    expect(await checkbox.count()).toBe(1);
  });

  test("login form has submit button", async ({ page }) => {
    expect(await isVisible(page, "#btn-submit-login")).toBe(true);
  });

  test("can fill login form", async ({ page }) => {
    await page.fill("#email", "user@example.com");
    await page.fill("#password", "password123");

    expect(await page.locator("#email").inputValue()).toBe("user@example.com");
    expect(await page.locator("#password").inputValue()).toBe("password123");
  });

  test("login form submission triggers action", async ({ page }) => {
    await page.fill("#email", "user@example.com");
    await page.fill("#password", "password123");

    await page.click("#btn-submit-login");

    // Should show notification (success or error)
    await page.waitForSelector(".notification", {
      state: "visible",
      timeout: 5000,
    });
    const notification = page.locator(".notification");
    expect(await notification.count()).toBeGreaterThan(0);
  });
});
