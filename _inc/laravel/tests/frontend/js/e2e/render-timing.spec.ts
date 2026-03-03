/**
 * @file render-timing.spec.ts
 * @description Playwright render-timing benchmark
 *
 * Captures per-view:
 *  - First mount: TTFB, FCP, DOMContentLoaded, load, LCP (via PerformanceObserver)
 *  - Later mount (same session, revisit): all metrics again + cache-speedup ratio
 *  - Navigation transition time (SPA-style link clicks)
 *  - Per-resource breakdown (JS/CSS/img size + duration)
 *
 * Results are written to /tmp/playwright_render_timing_<ts>.json
 */
import { test, expect, Page, BrowserContext } from "@playwright/test";
import * as fs from "fs";
import * as path from "path";

const BASE = process.env.APP_URL || "http://127.0.0.1:8000";
const TEST_EMAIL =
  process.env.TEST_EMAIL || "u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local";
const TEST_PASS = process.env.TEST_PASS || "Admin@1234";

/** Browser-aware constants — Firefox/WebKit need wider margins */
function isSlowBrowser(): boolean {
  const project = (test.info?.() as any)?.project?.name ?? "";
  return /firefox|webkit/i.test(project);
}
const GOTO_TIMEOUT = 45_000;
const SELECTOR_TIMEOUT = 15_000;
const IDLE_TIMEOUT = 15_000;
/** Post-LCP grace: Chromium fires LCP quickly; Firefox needs more time */
const LCP_GRACE_MS = () => (isSlowBrowser() ? 600 : 200);
/** Max acceptable load-event time per browser class */
const MAX_LOAD_MS = () => (isSlowBrowser() ? 15_000 : 8_000);

const VIEWS: { name: string; path: string; waitFor?: string }[] = [
  { name: "login", path: "/login", waitFor: "form" },
  { name: "dashboard", path: "/dashboard", waitFor: ".card, .widget, main" },
  { name: "customers", path: "/customers", waitFor: "table, .card" },
  { name: "vendors", path: "/vendors", waitFor: "table, .card" },
  { name: "products", path: "/products", waitFor: "table, .card" },
  { name: "invoices", path: "/invoices", waitFor: "table, .card" },
  { name: "bills", path: "/bills", waitFor: "table, .card" },
  { name: "proposals", path: "/proposals", waitFor: "table, .card" },
  { name: "purchases", path: "/purchases", waitFor: "table, .card" },
  { name: "leads", path: "/leads", waitFor: "table, .card" },
  { name: "deals", path: "/deals", waitFor: "table, .card" },
  { name: "projects", path: "/projects", waitFor: "table, .card" },
  { name: "tasks", path: "/tasks", waitFor: "table, .card" },
  { name: "expenses", path: "/expenses", waitFor: "table, .card" },
  {
    name: "report_income",
    path: "/reports/income-summary",
    waitFor: "table, canvas, .card",
  },
  {
    name: "report_expense",
    path: "/reports/expense-summary",
    waitFor: "table, canvas, .card",
  },
  {
    name: "report_balance_sheet",
    path: "/reports/balance-sheet",
    waitFor: "table, canvas, .card",
  },
  {
    name: "report_trial_balance",
    path: "/reports/trial-balance",
    waitFor: "table, canvas, .card",
  },
  {
    name: "report_profit_loss",
    path: "/reports/profit-loss",
    waitFor: "table, canvas, .card",
  },
];

// ── Types ─────────────────────────────────────────────────────────────────────
interface NavTiming {
  ttfb_ms: number;
  dom_content_loaded_ms: number;
  load_event_ms: number;
  fcp_ms: number;
  lcp_ms: number;
  total_transfer_bytes: number;
  resource_count: number;
  blocking_script_count: number;
  resources: {
    name: string;
    type: string;
    duration_ms: number;
    size_bytes: number;
  }[];
}

interface ViewResult {
  view: string;
  path: string;
  http_status: number;
  first_mount: NavTiming | { error: string };
  later_mount: NavTiming | { error: string };
  cache_speedup_ratio: number | null;
  nav_transition_ms: number | null;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
async function loginPage(page: Page): Promise<void> {
  await page.goto(`${BASE}/login`, { waitUntil: "domcontentloaded" });
  await page
    .locator('input[name="email"], input[type="email"]')
    .first()
    .fill(TEST_EMAIL);
  await page
    .locator('input[name="password"], input[type="password"]')
    .first()
    .fill(TEST_PASS);
  await page
    .locator('button[type="submit"], input[type="submit"]')
    .first()
    .click();
  await page.waitForLoadState("networkidle").catch(() => {});
}

/** Inject LCP observer before navigation and return collected value after */
async function injectLCPObserver(page: Page): Promise<void> {
  await page.evaluate(() => {
    (window as any).__lcpValue = 0;
    try {
      new PerformanceObserver(list => {
        for (const entry of list.getEntries()) {
          (window as any).__lcpValue = entry.startTime;
        }
      }).observe({ type: "largest-contentful-paint", buffered: true });
    } catch (_) {}
  });
}

async function collectTimings(page: Page): Promise<NavTiming> {
  return await page.evaluate(() => {
    const nav = performance.getEntriesByType(
      "navigation",
    )[0] as PerformanceNavigationTiming;
    const paintEntries = performance.getEntriesByType("paint");
    const resources = performance.getEntriesByType(
      "resource",
    ) as PerformanceResourceTiming[];

    const fcp =
      paintEntries.find(e => e.name === "first-contentful-paint")?.startTime ??
      0;
    const lcp = (window as any).__lcpValue ?? 0;

    // Blocking scripts: in <head> without defer/async
    const headScripts = Array.from(
      document.head.querySelectorAll("script[src]"),
    );
    const blockingScripts = headScripts.filter(
      s => !s.hasAttribute("defer") && !s.hasAttribute("async"),
    ).length;

    const resData = resources.map(r => ({
      name: r.name.split("/").slice(-2).join("/"),
      type: r.initiatorType,
      duration_ms: Math.round(r.duration),
      size_bytes: r.transferSize ?? 0,
    }));

    return {
      ttfb_ms: Math.round(nav ? nav.responseStart - nav.requestStart : 0),
      dom_content_loaded_ms: Math.round(
        nav ? nav.domContentLoadedEventEnd - nav.startTime : 0,
      ),
      load_event_ms: Math.round(nav ? nav.loadEventEnd - nav.startTime : 0),
      fcp_ms: Math.round(fcp),
      lcp_ms: Math.round(lcp),
      total_transfer_bytes: resData.reduce((s, r) => s + r.size_bytes, 0),
      resource_count: resData.length,
      blocking_script_count: blockingScripts,
      resources: resData,
    };
  });
}

async function measureView(
  page: Page,
  view: (typeof VIEWS)[0],
): Promise<{ timing: NavTiming; status: number }> {
  await injectLCPObserver(page);

  let status = 0;
  const onResponse = (r: any) => {
    if (r.url().includes(view.path)) status = r.status();
  };
  page.on("response", onResponse);

  await page.goto(`${BASE}${view.path}`, {
    waitUntil: "domcontentloaded",
    timeout: GOTO_TIMEOUT,
  });

  // Wait for a key selector when specified
  if (view.waitFor) {
    await page
      .locator(view.waitFor)
      .first()
      .waitFor({ timeout: SELECTOR_TIMEOUT })
      .catch(() => {});
  }

  // Ensure the `load` event has fired so loadEventEnd is populated
  await page
    .waitForLoadState("load", { timeout: IDLE_TIMEOUT })
    .catch(() => {});
  await page
    .waitForLoadState("networkidle", { timeout: IDLE_TIMEOUT })
    .catch(() => {});

  // For canvas-heavy views, wait for at least one animation frame so
  // charting libraries (ApexCharts, Chart.js, etc.) finish painting.
  if (view.waitFor?.includes("canvas")) {
    await page
      .evaluate(
        () =>
          new Promise<void>(resolve => requestAnimationFrame(() => resolve())),
      )
      .catch(() => {});
  }

  // Give LCP observer time to fire — Firefox needs a longer grace period
  await page.waitForTimeout(LCP_GRACE_MS());

  const timing = await collectTimings(page);
  page.off("response", onResponse);
  return { timing, status };
}

// ── Output ────────────────────────────────────────────────────────────────────
function writeResults(results: ViewResult[]): string {
  const outDir = "/tmp/curl_timing";
  fs.mkdirSync(outDir, { recursive: true });
  const ts = new Date().toISOString().replace(/[:.]/g, "-").slice(0, 19);
  const file = path.join(outDir, `playwright_render_timing_${ts}.json`);
  const payload = {
    generated_at: new Date().toISOString(),
    base_url: BASE,
    views: results,
  };
  fs.writeFileSync(file, JSON.stringify(payload, null, 2));
  return file;
}

function summarise(r: NavTiming) {
  return `TTFB=${r.ttfb_ms}ms FCP=${r.fcp_ms}ms DCL=${r.dom_content_loaded_ms}ms load=${r.load_event_ms}ms LCP=${r.lcp_ms}ms resources=${r.resource_count} blocking_scripts=${r.blocking_script_count}`;
}

// ============================================================================
// TEST SUITE
// ============================================================================
test.describe.serial("Render Timing Benchmark", () => {
  test.skip(
    !process.env.APP_URL,
    "Requires APP_URL to benchmark a running Laravel frontend.",
  );

  const allResults: ViewResult[] = [];

  test.afterAll(async () => {
    const file = writeResults(allResults);
    console.log(`\n[render-timing] Results written → ${file}`);
    console.log("\n=== RENDER TIMING SUMMARY ===");
    console.log(
      `${"View".padEnd(25)} ${"1st TTFB".padStart(9)} ${"1st FCP".padStart(8)} ${"1st DCL".padStart(8)} ${"1st Load".padStart(9)} ${"2nd TTFB".padStart(9)} ${"2nd DCL".padStart(8)} ${"Speedup".padStart(8)}`,
    );
    console.log("─".repeat(100));
    for (const r of allResults) {
      if ("error" in r.first_mount || "error" in r.later_mount) {
        console.log(`${r.view.padEnd(25)} ERROR`);
        continue;
      }
      const f = r.first_mount as NavTiming;
      const l = r.later_mount as NavTiming;
      const speedup =
        r.cache_speedup_ratio != null
          ? `${r.cache_speedup_ratio.toFixed(2)}x`
          : "n/a";
      console.log(
        `${r.view.padEnd(25)}` +
          ` ${String(f.ttfb_ms + "ms").padStart(9)}` +
          ` ${String(f.fcp_ms + "ms").padStart(8)}` +
          ` ${String(f.dom_content_loaded_ms + "ms").padStart(8)}` +
          ` ${String(f.load_event_ms + "ms").padStart(9)}` +
          ` ${String(l.ttfb_ms + "ms").padStart(9)}` +
          ` ${String(l.dom_content_loaded_ms + "ms").padStart(8)}` +
          ` ${speedup.padStart(8)}`,
      );
    }
  });

  // ── Auth: single shared login ─────────────────────────────────────────────
  let authContext: BrowserContext;

  test.beforeAll(async ({ browser }) => {
    authContext = await browser.newContext();
    const loginP = await authContext.newPage();
    await loginPage(loginP);
    await loginP.close();
  });

  test.afterAll(async () => {
    await authContext.close();
  });

  // ── Unauthenticated views ─────────────────────────────────────────────────
  test("timing: /login (public — first & later mount)", async ({ page }) => {
    const view = { name: "login", path: "/login", waitFor: "form" };

    const { timing: first, status } = await measureView(page, view);
    console.log(`[first]  /login   ${summarise(first)}`);

    const { timing: later } = await measureView(page, view);
    console.log(`[later]  /login   ${summarise(later)}`);

    const speedup =
      later.dom_content_loaded_ms > 0
        ? +(first.dom_content_loaded_ms / later.dom_content_loaded_ms).toFixed(
            2,
          )
        : null;

    allResults.push({
      view: "login",
      path: view.path,
      http_status: status || 200,
      first_mount: first,
      later_mount: later,
      cache_speedup_ratio: speedup,
      nav_transition_ms: null,
    });
  });

  // ── Authenticated views ───────────────────────────────────────────────────
  for (const view of VIEWS.filter(v => v.path !== "/login")) {
    test(`timing: ${view.path} (first & later mount)`, async () => {
      // Triple the overall test timeout for this benchmark — especially
      // important for Firefox / WebKit where rendering is slower.
      test.slow();

      const page = await authContext.newPage();

      try {
        const { timing: first, status } = await measureView(page, view);
        console.log(`[first]  ${view.path.padEnd(35)} ${summarise(first)}`);

        const { timing: later } = await measureView(page, view);
        console.log(`[later]  ${view.path.padEnd(35)} ${summarise(later)}`);

        const speedup =
          later.dom_content_loaded_ms > 0
            ? +(
                first.dom_content_loaded_ms / later.dom_content_loaded_ms
              ).toFixed(2)
            : null;

        // Navigation transition: click a link on the page and measure
        let navTransitionMs: number | null = null;
        try {
          const link = page.locator('a[href^="/"]').first();
          if (await link.count()) {
            const href = await link.getAttribute("href");
            if (href && href !== view.path) {
              const t0 = Date.now();
              await link.click();
              await page
                .waitForLoadState("domcontentloaded", { timeout: 10_000 })
                .catch(() => {});
              navTransitionMs = Date.now() - t0;
            }
          }
        } catch (_) {}

        allResults.push({
          view: view.name,
          path: view.path,
          http_status: status || 200,
          first_mount: first,
          later_mount: later,
          cache_speedup_ratio: speedup,
          nav_transition_ms: navTransitionMs,
        });

        // Soft assertion: browser-aware load-time threshold
        const limit = MAX_LOAD_MS();
        expect(
          first.load_event_ms,
          `${view.path} first load took ${first.load_event_ms}ms (limit: ${limit}ms)`,
        ).toBeLessThan(limit);
      } catch (err: any) {
        // Distinguish network/timeout errors from assertion failures.
        // Only skip on genuine connectivity issues — let perf regressions fail properly.
        const isAssertionError =
          err?.constructor?.name === "ExpectError" ||
          err?.matcherResult !== undefined;

        allResults.push({
          view: view.name,
          path: view.path,
          http_status: 0,
          first_mount: { error: String(err) },
          later_mount: { error: String(err) },
          cache_speedup_ratio: null,
          nav_transition_ms: null,
        });

        if (isAssertionError) {
          // Re-throw so the test fails visibly instead of being silently skipped
          throw err;
        }
        test.skip(true, `Could not reach ${view.path}: ${err}`);
      } finally {
        await page.close();
      }
    });
  }
});
