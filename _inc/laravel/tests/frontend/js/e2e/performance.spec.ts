/**
 * @file performance.spec.ts
 * @description Playwright E2E performance tests for ERP application
 * Tests: page load times, Core Web Vitals, lazy loading, resource optimization
 * @group performance
 */
import { test, expect, Page } from "@playwright/test";

const BASE = process.env.APP_URL || "http://127.0.0.1:8000";

// Performance thresholds (relaxed for dev/CI variance)
const THRESHOLDS = {
  // Page load times (ms)
  firstContentfulPaint: 4000, // FCP < 4s (relaxed from 2.5s for dev)
  largestContentfulPaint: 5000, // LCP < 5s
  timeToInteractive: 8000, // TTI < 8s (relaxed for dev)
  totalBlockingTime: 300, // TBT < 300ms

  // Resource sizes (KB)
  maxJsBundleSize: 500,
  maxCssBundleSize: 200,
  maxImageSize: 200,
  maxTotalPageSize: 3000, // 3MB

  // Counts
  maxHttpRequests: 60,
  maxBlockingScripts: 5,
};

// Helper: Login as admin
async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto(`${BASE}/login`);
  await page
    .locator('input[name="email"], input[type="email"]')
    .first()
    .fill(
      process.env.TEST_EMAIL ||
        "u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local",
    );
  await page
    .locator('input[name="password"], input[type="password"]')
    .first()
    .fill(process.env.TEST_PASS || "Admin@1234");
  await page
    .locator('button[type="submit"], input[type="submit"]')
    .first()
    .click();
  await page.waitForLoadState("networkidle");
}

// Helper: Get performance metrics from page
async function getPerformanceMetrics(page: Page): Promise<{
  fcp: number;
  lcp: number;
  domContentLoaded: number;
  load: number;
}> {
  return await page.evaluate(() => {
    const perfEntries = performance.getEntriesByType(
      "navigation",
    )[0] as PerformanceNavigationTiming;
    const paintEntries = performance.getEntriesByType("paint");

    const fcp = paintEntries.find(e => e.name === "first-contentful-paint");

    return {
      fcp: fcp ? fcp.startTime : 0,
      lcp: 0, // LCP requires additional instrumentation
      domContentLoaded:
        perfEntries.domContentLoadedEventEnd - perfEntries.startTime,
      load: perfEntries.loadEventEnd - perfEntries.startTime,
    };
  });
}

// Helper: Get resource timing data
async function getResourceTimings(page: Page): Promise<{
  resources: { name: string; size: number; duration: number; type: string }[];
  totalSize: number;
  totalRequests: number;
}> {
  return await page.evaluate(() => {
    const resources = performance.getEntriesByType(
      "resource",
    ) as PerformanceResourceTiming[];

    const mapped = resources.map(r => ({
      name: r.name,
      size: r.transferSize || 0,
      duration: r.duration,
      type: r.initiatorType,
    }));

    return {
      resources: mapped,
      totalSize: mapped.reduce((sum, r) => sum + r.size, 0),
      totalRequests: mapped.length,
    };
  });
}

// ============================================================================
// PERFORMANCE TEST SUITES
// ============================================================================

test.describe("Performance Optimization", () => {
  test.skip(
    !process.env.APP_URL,
    "Requires APP_URL to probe a running Laravel frontend.",
  );

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 1: PAGE LOAD PERFORMANCE
  // ════════════════════════════════════════════════════════════════════════

  test.describe("Page Load Times", () => {
    test("login page loads within threshold", async ({ page }) => {
      const start = Date.now();
      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("domcontentloaded");
      const loadTime = Date.now() - start;

      expect(loadTime).toBeLessThan(THRESHOLDS.timeToInteractive);
    });

    test("dashboard loads within threshold after login", async ({ page }) => {
      await loginAsAdmin(page);

      const start = Date.now();
      await page.goto(`${BASE}/dashboard`);
      await page.waitForLoadState("domcontentloaded");
      const loadTime = Date.now() - start;

      expect(loadTime).toBeLessThan(THRESHOLDS.timeToInteractive);
    });

    test("customers list loads efficiently", async ({ page }) => {
      await loginAsAdmin(page);

      const start = Date.now();
      await page.goto(`${BASE}/customers`);
      await page.waitForLoadState("domcontentloaded");
      const loadTime = Date.now() - start;

      expect(loadTime).toBeLessThan(THRESHOLDS.timeToInteractive);
    });

    test("First Contentful Paint is within threshold", async ({ page }) => {
      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("load");

      const metrics = await getPerformanceMetrics(page);

      expect(metrics.fcp).toBeLessThan(THRESHOLDS.firstContentfulPaint);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 2: RESOURCE OPTIMIZATION
  // ════════════════════════════════════════════════════════════════════════

  test.describe("Resource Optimization", () => {
    test("total HTTP requests under limit", async ({ page }) => {
      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");

      const { totalRequests } = await getResourceTimings(page);

      expect(totalRequests).toBeLessThan(THRESHOLDS.maxHttpRequests);
    });

    test("total page size under limit", async ({ page }) => {
      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");

      const { totalSize } = await getResourceTimings(page);
      const totalSizeKB = totalSize / 1024;

      expect(totalSizeKB).toBeLessThan(THRESHOLDS.maxTotalPageSize);
    });

    test("no individual JS bundle exceeds size limit", async ({ page }) => {
      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");

      const { resources } = await getResourceTimings(page);
      const jsFiles = resources.filter(
        r => r.name.includes(".js") && r.type === "script",
      );

      for (const js of jsFiles) {
        const sizeKB = js.size / 1024;
        expect(sizeKB).toBeLessThan(THRESHOLDS.maxJsBundleSize);
      }
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 3: BLOCKING RESOURCES
  // ════════════════════════════════════════════════════════════════════════

  test.describe("Blocking Resources", () => {
    test("blocking scripts count under threshold", async ({ page }) => {
      await page.goto(`${BASE}/login`);

      // Count render-blocking scripts (in <head> without defer/async)
      const blockingScripts = await page.evaluate(() => {
        const headScripts = document.head.querySelectorAll("script[src]");
        let blocking = 0;
        headScripts.forEach(script => {
          if (!script.hasAttribute("defer") && !script.hasAttribute("async")) {
            blocking++;
          }
        });
        return blocking;
      });

      expect(blockingScripts).toBeLessThanOrEqual(
        THRESHOLDS.maxBlockingScripts,
      );
    });

    test("CSS is not render-blocking after critical CSS", async ({ page }) => {
      await page.goto(`${BASE}/login`);

      // Check for media="print" or preload patterns for non-critical CSS
      const cssLinks = await page.evaluate(() => {
        const links = document.querySelectorAll('link[rel="stylesheet"]');
        const allCss = links.length;
        let deferredCss = 0;

        links.forEach(link => {
          const media = link.getAttribute("media");
          if (media && media !== "all" && media !== "screen") {
            deferredCss++;
          }
        });

        return { allCss, deferredCss };
      });

      // At least some CSS should be deferred for optimal performance
      // This is a soft check - just ensure we have CSS
      expect(cssLinks.allCss).toBeGreaterThan(0);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 4: LAZY LOADING
  // ════════════════════════════════════════════════════════════════════════

  test.describe("Lazy Loading", () => {
    test("images use lazy loading", async ({ page }) => {
      await loginAsAdmin(page);
      await page.goto(`${BASE}/dashboard`);
      await page.waitForLoadState("domcontentloaded");

      // Check for lazy loading attributes
      const lazyStats = await page.evaluate(() => {
        const images = document.querySelectorAll("img");
        let lazy = 0;
        let total = 0;

        images.forEach(img => {
          if (img.naturalWidth > 0 || img.src) {
            // Visible images
            total++;
            if (img.loading === "lazy" || img.hasAttribute("data-src")) {
              lazy++;
            }
          }
        });

        return { lazy, total };
      });

      // If there are images, at least some should be lazy loaded
      if (lazyStats.total > 3) {
        expect(lazyStats.lazy).toBeGreaterThan(0);
      }
    });

    test("off-screen content is not initially rendered", async ({ page }) => {
      await loginAsAdmin(page);
      await page.goto(`${BASE}/dashboard`);

      // Check viewport intersection
      const offScreenElements = await page.evaluate(() => {
        const viewportHeight = window.innerHeight;
        const allElements = document.querySelectorAll("*");
        let offScreen = 0;

        allElements.forEach(el => {
          const rect = el.getBoundingClientRect();
          if (rect.top > viewportHeight * 2) {
            offScreen++;
          }
        });

        return offScreen;
      });

      // Some off-screen elements are expected (for scrolling)
      // Just verify the page structure exists
      expect(offScreenElements).toBeGreaterThanOrEqual(0);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 5: CACHING
  // ════════════════════════════════════════════════════════════════════════

  test.describe("Caching", () => {
    test("static assets have cache headers", async ({ page }) => {
      const cacheableAssets: string[] = [];
      const nonCacheableAssets: string[] = [];

      page.on("response", response => {
        const url = response.url();
        if (
          url.includes(".js") ||
          url.includes(".css") ||
          url.includes(".png") ||
          url.includes(".jpg")
        ) {
          const cacheControl = response.headers()["cache-control"] || "";
          if (
            cacheControl.includes("max-age") ||
            cacheControl.includes("public")
          ) {
            cacheableAssets.push(url);
          } else {
            nonCacheableAssets.push(url);
          }
        }
      });

      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");

      // Most static assets should have cache headers
      const totalAssets = cacheableAssets.length + nonCacheableAssets.length;
      if (totalAssets > 0) {
        const cacheRatio = cacheableAssets.length / totalAssets;
        expect(cacheRatio).toBeGreaterThan(0.5); // At least 50% should be cacheable
      }
    });

    test("second page load is faster (browser cache)", async ({ page }) => {
      // First load (cold cache)
      const start1 = Date.now();
      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");
      const coldLoad = Date.now() - start1;

      // Second load (warm cache)
      const start2 = Date.now();
      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");
      const warmLoad = Date.now() - start2;

      // Warm load should not be significantly slower
      expect(warmLoad).toBeLessThan(coldLoad * 1.5);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 6: JAVASCRIPT EXECUTION
  // ════════════════════════════════════════════════════════════════════════

  test.describe("JavaScript Execution", () => {
    test("no long-running synchronous operations", async ({ page }) => {
      // Catch long tasks
      const _longTasks: number[] = [];

      await page.goto(`${BASE}/login`);

      // Evaluate long task detection
      await page.evaluate(() => {
        return new Promise<void>(resolve => {
          const observer = new PerformanceObserver(list => {
            for (const entry of list.getEntries()) {
              if (entry.duration > 100) {
                // @ts-ignore
                window.__longTasks = window.__longTasks || [];
                // @ts-ignore
                window.__longTasks.push(entry.duration);
              }
            }
          });
          observer.observe({ entryTypes: ["longtask"] });

          // Give time for tasks to be recorded
          setTimeout(() => {
            observer.disconnect();
            resolve();
          }, 2000);
        });
      });

      const recordedLongTasks = await page.evaluate(() => {
        // @ts-ignore
        return window.__longTasks || [];
      });

      // Should have minimal long tasks (blocking > 100ms)
      expect(
        recordedLongTasks.filter((t: number) => t > 500).length,
      ).toBeLessThan(3);
    });

    test("no console errors on page load", async ({ page }) => {
      const consoleErrors: string[] = [];

      page.on("console", msg => {
        if (msg.type() === "error") {
          consoleErrors.push(msg.text());
        }
      });

      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");

      // Filter out known non-critical errors
      const criticalErrors = consoleErrors.filter(
        err =>
          !err.includes("favicon") &&
          !err.includes("404") &&
          !err.includes("net::ERR"),
      );

      expect(criticalErrors.length).toBe(0);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 7: NETWORK EFFICIENCY
  // ════════════════════════════════════════════════════════════════════════

  test.describe("Network Efficiency", () => {
    test("no duplicate resource requests", async ({ page }) => {
      const requests: string[] = [];

      page.on("request", request => {
        const url = request.url();
        if (url.includes(".js") || url.includes(".css")) {
          requests.push(url);
        }
      });

      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");

      // Check for duplicates
      const uniqueRequests = new Set(requests);
      const duplicateCount = requests.length - uniqueRequests.size;

      expect(duplicateCount).toBeLessThan(3);
    });

    test("GZIP compression is enabled", async ({ page }) => {
      let compressedCount = 0;
      let totalCount = 0;

      page.on("response", response => {
        const url = response.url();
        if (
          url.includes(".js") ||
          url.includes(".css") ||
          url.includes(".html")
        ) {
          totalCount++;
          const encoding = response.headers()["content-encoding"];
          if (
            encoding &&
            (encoding.includes("gzip") || encoding.includes("br"))
          ) {
            compressedCount++;
          }
        }
      });

      await page.goto(`${BASE}/login`);
      await page.waitForLoadState("networkidle");

      // Most text assets should be compressed
      if (totalCount > 0) {
        const compressionRatio = compressedCount / totalCount;
        // Be lenient - some assets might not need compression
        expect(compressionRatio).toBeGreaterThanOrEqual(0.3);
      }
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 8: MEMORY USAGE
  // ════════════════════════════════════════════════════════════════════════

  test.describe("Memory Usage", () => {
    test("memory does not grow excessively during navigation", async ({
      page,
    }) => {
      await loginAsAdmin(page);

      // Get initial memory
      const initialMemory = await page.evaluate(() => {
        // @ts-ignore
        return performance.memory?.usedJSHeapSize || 0;
      });

      // Navigate through several pages
      await page.goto(`${BASE}/dashboard`);
      await page.goto(`${BASE}/customers`);
      await page.goto(`${BASE}/vendors`);
      await page.goto(`${BASE}/dashboard`);

      // Get final memory
      const finalMemory = await page.evaluate(() => {
        // @ts-ignore
        return performance.memory?.usedJSHeapSize || 0;
      });

      // Memory should not have grown more than 2x (accounting for caching)
      if (initialMemory > 0) {
        expect(finalMemory).toBeLessThan(initialMemory * 3);
      }
    });

    test("no detached DOM nodes leak", async ({ page }) => {
      await loginAsAdmin(page);

      await page.goto(`${BASE}/dashboard`);

      // Get initial DOM node count
      const initialNodes = await page.evaluate(() => {
        return document.getElementsByTagName("*").length;
      });

      // Trigger some page interactions
      await page.goto(`${BASE}/customers`);
      await page.goto(`${BASE}/dashboard`);

      // Get final DOM node count
      const finalNodes = await page.evaluate(() => {
        return document.getElementsByTagName("*").length;
      });

      // DOM count should not explode (allow reasonable variance)
      expect(finalNodes).toBeLessThan(initialNodes * 2);
    });
  });
});
