/**
 * API Response Handling E2E Tests
 *
 * Tests for validating how the frontend handles various API response scenarios:
 * - Success responses (2xx)
 * - Client errors (4xx)
 * - Server errors (5xx)
 * - Network failures
 * - Timeout scenarios
 */
import { test, expect, Page } from "@playwright/test";

const TEST_BASE_PATH = "./tests/frontend/js/pages/mocks/rbac";

interface ApiTestResult {
  passed: number;
  failed: number;
  total: number;
  results: Array<{
    name: string;
    passed: boolean;
    error?: string;
  }>;
}

/**
 * Helper to wait for response panel update
 */
async function waitForResponseUpdate(page: Page): Promise<void> {
  await page.waitForSelector(".response-panel .response-meta", {
    state: "visible",
    timeout: 5000,
  });
}

/**
 * Helper to get response status from panel
 */
async function getResponseStatus(page: Page): Promise<number | null> {
  const metaText = await page.locator(".response-meta").textContent();
  const match = metaText?.match(/Status:\s*(\d+)/);
  return match ? parseInt(match[1], 10) : null;
}

/**
 * Helper to check if response indicates success
 */
async function isResponseSuccess(page: Page): Promise<boolean> {
  const successIndicator = page.locator(".response-success");
  return (await successIndicator.count()) > 0;
}

/**
 * Helper to get response body as JSON
 */
async function getResponseBody(page: Page): Promise<any> {
  const bodyText = await page.locator(".response-body").textContent();
  try {
    return JSON.parse(bodyText || "{}");
  } catch {
    return null;
  }
}

// ============================================================================
// SUCCESS RESPONSE TESTS (2xx)
// ============================================================================

test.describe("Success Responses (2xx)", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("200 OK response should indicate success", async ({ page }) => {
    await page.click("#test-200");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const isSuccess = await isResponseSuccess(page);
    const body = await getResponseBody(page);

    expect(status).toBe(200);
    expect(isSuccess).toBe(true);
    expect(body.success).toBe(true);
  });

  test("201 Created response should include created resource data", async ({
    page,
  }) => {
    await page.click("#test-201");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const isSuccess = await isResponseSuccess(page);
    const body = await getResponseBody(page);

    expect(status).toBe(201);
    expect(isSuccess).toBe(true);
    expect(body.success).toBe(true);
    expect(body.data).toBeDefined();
  });

  test("successful fetch shows success notification", async ({ page }) => {
    await page.click("#test-200");
    await page.waitForSelector(".notification.success", {
      state: "visible",
      timeout: 3000,
    });

    const notification = page.locator(".notification.success");
    expect(await notification.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// CLIENT ERROR TESTS (4xx)
// ============================================================================

test.describe("Client Errors (4xx)", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("400 Bad Request should indicate error", async ({ page }) => {
    await page.click("#test-400");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const isSuccess = await isResponseSuccess(page);
    const body = await getResponseBody(page);

    expect(status).toBe(400);
    expect(isSuccess).toBe(false);
    expect(body.success).toBe(false);
    expect(body.message).toBeDefined();
  });

  test("401 Unauthorized should indicate authentication required", async ({
    page,
  }) => {
    await page.click("#test-401");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const body = await getResponseBody(page);

    expect(status).toBe(401);
    expect(body.success).toBe(false);
    expect(body.message.toLowerCase()).toContain("unauthenticated");
  });

  test("403 Forbidden should indicate permission denied", async ({ page }) => {
    await page.click("#test-403");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const body = await getResponseBody(page);

    expect(status).toBe(403);
    expect(body.success).toBe(false);
    expect(body.message.toLowerCase()).toContain("permission");
  });

  test("404 Not Found should indicate resource missing", async ({ page }) => {
    await page.click("#test-404");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const body = await getResponseBody(page);

    expect(status).toBe(404);
    expect(body.success).toBe(false);
    expect(body.message.toLowerCase()).toContain("not found");
  });

  test("422 Validation Error should include field-level errors", async ({
    page,
  }) => {
    await page.click("#test-422");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const body = await getResponseBody(page);

    expect(status).toBe(422);
    expect(body.success).toBe(false);
    expect(body.errors).toBeDefined();
    expect(typeof body.errors).toBe("object");

    // Should have error messages as arrays
    const firstErrorKey = Object.keys(body.errors)[0];
    expect(Array.isArray(body.errors[firstErrorKey])).toBe(true);
  });

  test("client error shows error notification", async ({ page }) => {
    await page.click("#test-401");
    await page.waitForSelector(".notification.error", {
      state: "visible",
      timeout: 3000,
    });

    const notification = page.locator(".notification.error");
    expect(await notification.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// SERVER ERROR TESTS (5xx)
// ============================================================================

test.describe("Server Errors (5xx)", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("500 Internal Server Error should indicate server failure", async ({
    page,
  }) => {
    await page.click("#test-500");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const body = await getResponseBody(page);

    expect(status).toBe(500);
    expect(body.success).toBe(false);
    expect(body.message).toBeDefined();
  });

  test("503 Service Unavailable should include retry information", async ({
    page,
  }) => {
    await page.click("#test-503");
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    const body = await getResponseBody(page);

    expect(status).toBe(503);
    expect(body.success).toBe(false);
    expect(body.retryAfter).toBeDefined();
    expect(typeof body.retryAfter).toBe("number");
  });

  test("server error shows error notification", async ({ page }) => {
    await page.click("#test-500");
    await page.waitForSelector(".notification.error", {
      state: "visible",
      timeout: 3000,
    });

    const notification = page.locator(".notification.error");
    expect(await notification.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// NETWORK ERROR TESTS
// ============================================================================

test.describe("Network Errors", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("network failure should be identifiable", async ({ page }) => {
    await page.click("#test-network");
    await waitForResponseUpdate(page);

    const body = await getResponseBody(page);

    expect(body.success).toBe(false);
    expect(body.error).toBe("NETWORK_ERROR");
  });

  test("timeout should be identifiable", async ({ page }) => {
    await page.click("#test-timeout");
    await waitForResponseUpdate(page);

    const body = await getResponseBody(page);

    expect(body.success).toBe(false);
    expect(body.error).toBe("TIMEOUT");
  });

  test("network error shows error notification", async ({ page }) => {
    await page.click("#test-network");
    await page.waitForSelector(".notification.error", {
      state: "visible",
      timeout: 3000,
    });

    const notification = page.locator(".notification.error");
    expect(await notification.count()).toBeGreaterThan(0);
  });
});

// ============================================================================
// CRUD OPERATIONS TESTS
// ============================================================================

test.describe("CRUD Operations", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("fetch users should show loading state", async ({ page }) => {
    const fetchPromise = page.click("#btn-fetch-users");

    // Loading should appear
    await page.waitForSelector("#data-loading", {
      state: "visible",
      timeout: 2000,
    });
    expect(await page.locator("#data-loading").isVisible()).toBe(true);

    // Wait for completion
    await fetchPromise;
    await page.waitForSelector("#data-loading", {
      state: "hidden",
      timeout: 3000,
    });
  });

  test("fetch users should populate table", async ({ page }) => {
    await page.click("#btn-fetch-users");
    await page.waitForSelector("#data-loading", {
      state: "hidden",
      timeout: 5000,
    });

    const rows = page.locator("#data-tbody tr");
    expect(await rows.count()).toBeGreaterThan(0);
  });

  test("create user should add to table", async ({ page }) => {
    // First fetch users
    await page.click("#btn-fetch-users");
    await page.waitForSelector("#data-loading", {
      state: "hidden",
      timeout: 5000,
    });

    const initialCount = await page.locator("#data-tbody tr").count();

    // Create new user
    await page.click("#btn-create-user");
    await waitForResponseUpdate(page);

    const newCount = await page.locator("#data-tbody tr").count();
    expect(newCount).toBe(initialCount + 1);
  });

  test("delete user should remove from table", async ({ page }) => {
    // First fetch users
    await page.click("#btn-fetch-users");
    await page.waitForSelector("#data-loading", {
      state: "hidden",
      timeout: 5000,
    });

    const initialCount = await page.locator("#data-tbody tr").count();

    // Delete user
    await page.click("#btn-delete-user");
    await waitForResponseUpdate(page);

    const newCount = await page.locator("#data-tbody tr").count();
    expect(newCount).toBe(initialCount - 1);
  });

  test("update shows warning when no users loaded", async ({ page }) => {
    await page.click("#btn-update-user");

    // Should show warning notification
    await page.waitForSelector(".notification.warning", {
      state: "visible",
      timeout: 3000,
    });
    const notification = page.locator(".notification.warning");
    expect(await notification.count()).toBeGreaterThan(0);
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

  test("form should have all required fields", async ({ page }) => {
    expect(await page.locator("#form-name").isVisible()).toBe(true);
    expect(await page.locator("#form-email").isVisible()).toBe(true);
    expect(await page.locator("#form-role").isVisible()).toBe(true);
    expect(await page.locator("#btn-submit").isVisible()).toBe(true);
  });

  test("valid form submission should succeed", async ({ page }) => {
    await page.fill("#form-name", "John Doe");
    await page.fill("#form-email", "john@example.com");
    await page.selectOption("#form-role", "admin");

    await page.locator("#btn-submit").click({ force: true });
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    expect(status).toBe(201);

    // Should show success message
    await page.waitForSelector(".notification.success", {
      state: "visible",
      timeout: 3000,
    });
  });

  test('form with "error" as name triggers validation error', async ({
    page,
  }) => {
    await page.fill("#form-name", "error");
    await page.fill("#form-email", "test@example.com");

    await page.locator("#btn-submit").click({ force: true });
    await waitForResponseUpdate(page);

    const status = await getResponseStatus(page);
    expect(status).toBe(422);

    // Should show field errors
    const emailError = page.locator("#error-email");
    const nameError = page.locator("#error-name");

    // At least one should have error text
    const emailErrorText = await emailError.textContent();
    const nameErrorText = await nameError.textContent();

    expect(emailErrorText || nameErrorText).toBeTruthy();
  });

  test("form reset clears all fields", async ({ page }) => {
    await page.fill("#form-name", "John Doe");
    await page.fill("#form-email", "john@example.com");

    await page.locator('button[type="reset"]').click({ force: true });

    const nameValue = await page.locator("#form-name").inputValue();
    const emailValue = await page.locator("#form-email").inputValue();

    expect(nameValue).toBe("");
    expect(emailValue).toBe("");
  });
});

// ============================================================================
// AUTOMATED API TESTS
// ============================================================================

test.describe("Inline API Tests", () => {
  test("all inline API tests should pass", async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html?autorun=true`,
    );
    await page.waitForLoadState("domcontentloaded");

    // Wait for tests to complete
    await page.waitForFunction(
      () => {
        // @ts-ignore
        return window.__testResults !== undefined;
      },
      { timeout: 10000 },
    );

    const results: ApiTestResult = await page.evaluate(() => {
      // @ts-ignore
      return window.__testResults;
    });

    expect(results.failed).toBe(0);
    expect(results.passed).toBeGreaterThan(0);

    // Log any failures for debugging
    if (results.failed > 0) {
      const failures = results.results.filter(r => !r.passed);
      console.log("Failed API tests:", failures);
    }
  });
});

// ============================================================================
// RESPONSE TIME TESTS
// ============================================================================

test.describe("Response Time", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("response panel should show duration", async ({ page }) => {
    await page.click("#test-200");
    await waitForResponseUpdate(page);

    const metaText = await page.locator(".response-meta").textContent();
    expect(metaText).toContain("Duration");
  });

  test("response should complete within reasonable time", async ({ page }) => {
    const startTime = Date.now();
    await page.click("#test-200");
    await waitForResponseUpdate(page);
    const endTime = Date.now();

    const duration = endTime - startTime;
    // Should complete within 2 seconds (including simulated delay)
    expect(duration).toBeLessThan(2000);
  });
});

// ============================================================================
// ERROR RECOVERY TESTS
// ============================================================================

test.describe("Error Recovery", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(
      `file://${process.cwd()}/${TEST_BASE_PATH}/api-scenarios.html`,
    );
    await page.waitForLoadState("domcontentloaded");
  });

  test("can retry after error", async ({ page }) => {
    // First, trigger an error
    await page.click("#test-500");
    await waitForResponseUpdate(page);

    let status = await getResponseStatus(page);
    expect(status).toBe(500);

    // Then trigger success — wait for the status text to actually change
    await page.click("#test-200");
    await page.waitForFunction(
      () => {
        const meta = document.querySelector(".response-meta");
        return meta && /Status:\s*200/.test(meta.textContent || "");
      },
      { timeout: 5000 },
    );

    status = await getResponseStatus(page);
    expect(status).toBe(200);
  });

  test("notifications auto-dismiss", async ({ page }) => {
    await page.click("#test-200");

    // Wait for notification to appear
    await page.waitForSelector(".notification", {
      state: "visible",
      timeout: 3000,
    });

    // Wait for it to disappear (they auto-dismiss after 5 seconds)
    await page.waitForSelector(".notification", {
      state: "detached",
      timeout: 7000,
    });

    const notifications = page.locator(".notification");
    expect(await notifications.count()).toBe(0);
  });
});
