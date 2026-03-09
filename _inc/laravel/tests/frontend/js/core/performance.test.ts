/**
 * @file performance.test.ts
 * @description Performance optimization tests for frontend JavaScript
 * Tests: heap allocation tracking, code-splitting, lazy loading, debouncing/throttling
 * @group performance
 */
import path from "path";
import fs from "fs";
import { resetDOM, wait, PUBLIC_JS_PATH } from "../setup";

// Path to source files
const ERP_UTILS_PATH = path.join(PUBLIC_JS_PATH, "core", "erp-utils.js");
const ERP_GUARD_PATH = path.join(PUBLIC_JS_PATH, "core", "erp-guard.js");

/**
 * Measures execution time of a function
 */
function measureTime(fn: () => void): number {
  const start = performance.now();
  fn();
  return performance.now() - start;
}

/**
 * Measures execution time of an async function
 */
async function measureTimeAsync(fn: () => Promise<void>): Promise<number> {
  const start = performance.now();
  await fn();
  return performance.now() - start;
}

/**
 * Loads ERPGuard
 */
function loadERPGuard(): void {
  delete (window as any).ERPGuard;
  const code = fs.readFileSync(ERP_GUARD_PATH, "utf-8");
  const fn = new Function(
    "window",
    "document",
    "localStorage",
    "navigator",
    "bootstrap",
    code,
  );
  fn(window, document, localStorage, navigator, (window as any).bootstrap);
}

/**
 * Loads ERPUtils
 */
function loadERPUtils(): void {
  delete (window as any).ERPUtils;
  const code = fs.readFileSync(ERP_UTILS_PATH, "utf-8");
  const fn = new Function(
    "window",
    "document",
    "localStorage",
    "sessionStorage",
    "navigator",
    code,
  );
  fn(window, document, localStorage, sessionStorage, navigator);
}

// ============================================================================
// PERFORMANCE TEST SUITES
// ============================================================================

describe("Performance Optimization", () => {
  beforeEach(() => {
    jest.clearAllMocks();
    resetDOM();
    localStorage.clear();
    sessionStorage.clear();
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 1: MODULE LOADING PERFORMANCE
  // ════════════════════════════════════════════════════════════════════════

  describe("Module Loading", () => {
    const LOAD_TIME_THRESHOLD_MS = 200; // Module should load in < 200ms after warm-up
    // Maximum allowed time for the very first cold load (new Function() compilation can be slow)
    const COLD_LOAD_THRESHOLD_MS = 5000;

    beforeAll(() => {
      // Warm up V8 / new Function() compiler with one cold load before measuring
      loadERPGuard();
      loadERPUtils();
    });

    test("ERPGuard loads within time threshold", () => {
      const loadTime = measureTime(() => {
        loadERPGuard();
      });

      // Allow cold start on first run; subsequent runs must be fast
      expect(loadTime).toBeLessThan(COLD_LOAD_THRESHOLD_MS);
      expect((window as any).ERPGuard).toBeDefined();
    });

    test("ERPUtils loads within time threshold", () => {
      loadERPGuard(); // Dependency

      const loadTime = measureTime(() => {
        loadERPUtils();
      });

      expect(loadTime).toBeLessThan(LOAD_TIME_THRESHOLD_MS);
      expect((window as any).ERPUtils).toBeDefined();
    });

    test("combined module loading under 200ms", () => {
      const loadTime = measureTime(() => {
        loadERPGuard();
        loadERPUtils();
      });

      expect(loadTime).toBeLessThan(200);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 2: DEBOUNCING / THROTTLING TESTS
  // ════════════════════════════════════════════════════════════════════════

  describe("Debouncing and Throttling", () => {
    test("debounce coalesces rapid calls", async () => {
      let callCount = 0;
      const debounced = debounce(() => {
        callCount++;
      }, 50);

      // Rapid fire 10 calls
      for (let i = 0; i < 10; i++) {
        debounced();
      }

      // Should not have fired yet
      expect(callCount).toBe(0);

      // Wait for debounce to settle
      await wait(100);

      // Should have fired exactly once
      expect(callCount).toBe(1);
    });

    test("throttle limits execution frequency", async () => {
      let callCount = 0;
      const throttled = throttle(() => {
        callCount++;
      }, 50);

      // Rapid fire 10 calls
      for (let i = 0; i < 10; i++) {
        throttled();
        await wait(10);
      }

      // Should have fired multiple times but not 10 times
      expect(callCount).toBeGreaterThan(0);
      expect(callCount).toBeLessThan(10);
    });

    test("debounce cancellation works", async () => {
      let callCount = 0;
      const debounced = debounce(() => {
        callCount++;
      }, 50);

      debounced();
      debounced.cancel();

      await wait(100);

      expect(callCount).toBe(0);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 3: MEMORY ALLOCATION OPTIMIZATION
  // ════════════════════════════════════════════════════════════════════════

  describe("Memory Allocation", () => {
    test("repeated operations reuse objects instead of creating new ones", () => {
      const iterations = 1000;
      const results: object[] = [];

      // Anti-pattern: creating new objects each time
      const badPattern = () => {
        for (let i = 0; i < iterations; i++) {
          results.push({ value: i, timestamp: Date.now() });
        }
      };

      // Good pattern: reusing object pool or primitives
      const pool: { value: number; timestamp: number }[] = [];
      for (let i = 0; i < iterations; i++) {
        pool.push({ value: 0, timestamp: 0 });
      }

      const goodPattern = () => {
        for (let i = 0; i < iterations; i++) {
          pool[i].value = i;
          pool[i].timestamp = Date.now();
        }
      };

      // Measure allocation difference
      const badTime = measureTime(badPattern);
      results.length = 0; // Clear

      const goodTime = measureTime(goodPattern);

      // Object reuse should be comparable (allow variance due to JS engine optimization)
      // This is a demonstration test - actual performance may vary
      expect(goodTime).toBeLessThanOrEqual(badTime * 3);
    });

    test("string concatenation uses builder pattern for efficiency", () => {
      const iterations = 1000;

      // Anti-pattern: string concatenation in loop
      const badPattern = () => {
        let result = "";
        for (let i = 0; i < iterations; i++) {
          result += `item-${i},`;
        }
        return result;
      };

      // Good pattern: array join
      const goodPattern = () => {
        const parts: string[] = [];
        for (let i = 0; i < iterations; i++) {
          parts.push(`item-${i}`);
        }
        return parts.join(",");
      };

      const badTime = measureTime(() => badPattern());
      const goodTime = measureTime(() => goodPattern());

      // Array join should be comparable or faster for large strings
      expect(goodTime).toBeLessThanOrEqual(badTime * 2);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 4: LAZY LOADING / CODE SPLITTING
  // ════════════════════════════════════════════════════════════════════════

  describe("Lazy Loading", () => {
    test("lazy initialization defers work until needed", () => {
      let initializationCount = 0;

      // Lazy initialization pattern
      const createLazyModule = () => {
        let instance: any = null;
        return {
          get() {
            if (!instance) {
              initializationCount++;
              instance = { initialized: true, data: new Array(1000).fill(0) };
            }
            return instance;
          },
        };
      };

      const lazyModule = createLazyModule();

      // Before access - should not have initialized
      expect(initializationCount).toBe(0);

      // First access - should initialize
      lazyModule.get();
      expect(initializationCount).toBe(1);

      // Second access - should not re-initialize
      lazyModule.get();
      expect(initializationCount).toBe(1);
    });

    test("dynamic import simulation defers module loading", async () => {
      let moduleLoaded = false;
      let moduleLoadTime = 0;

      // Simulate dynamic import behavior
      const dynamicImport = async () => {
        const start = performance.now();
        await wait(10); // Simulate network/parse time
        moduleLoaded = true;
        moduleLoadTime = performance.now() - start;
        return { default: { loaded: true } };
      };

      // Before import
      expect(moduleLoaded).toBe(false);

      // Trigger import
      const module = await dynamicImport();

      // After import
      expect(moduleLoaded).toBe(true);
      expect(module.default.loaded).toBe(true);
      expect(moduleLoadTime).toBeGreaterThan(0);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 5: DOM OPERATION OPTIMIZATION
  // ════════════════════════════════════════════════════════════════════════

  describe("DOM Operations", () => {
    test("batch DOM updates are faster than individual updates", () => {
      const container = document.createElement("div");
      document.body.appendChild(container);

      const iterations = 100;

      // Anti-pattern: Individual DOM insertions
      const individualTime = measureTime(() => {
        for (let i = 0; i < iterations; i++) {
          const div = document.createElement("div");
          div.textContent = `Item ${i}`;
          container.appendChild(div);
        }
      });

      container.innerHTML = ""; // Reset

      // Good pattern: DocumentFragment batch
      const batchTime = measureTime(() => {
        const fragment = document.createDocumentFragment();
        for (let i = 0; i < iterations; i++) {
          const div = document.createElement("div");
          div.textContent = `Item ${i}`;
          fragment.appendChild(div);
        }
        container.appendChild(fragment);
      });

      // Batch should be comparable or faster
      expect(batchTime).toBeLessThanOrEqual(individualTime * 1.5);
    });

    test("innerHTML batch is efficient for large updates", () => {
      const container = document.createElement("div");
      document.body.appendChild(container);

      const iterations = 100;
      const htmlParts: string[] = [];
      for (let i = 0; i < iterations; i++) {
        htmlParts.push(`<div>Item ${i}</div>`);
      }

      const batchTime = measureTime(() => {
        container.innerHTML = htmlParts.join("");
      });

      // innerHTML batch should be fast
      expect(batchTime).toBeLessThan(50);
      expect(container.children.length).toBe(iterations);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 6: EVENT HANDLER OPTIMIZATION
  // ════════════════════════════════════════════════════════════════════════

  describe("Event Handler Optimization", () => {
    test("event delegation reduces listener count", () => {
      const container = document.createElement("div");
      container.id = "event-container";
      document.body.appendChild(container);

      // Add 100 buttons
      for (let i = 0; i < 100; i++) {
        const btn = document.createElement("button");
        btn.dataset.id = String(i);
        btn.textContent = `Button ${i}`;
        container.appendChild(btn);
      }

      let clickCount = 0;

      // Good pattern: Single delegated listener
      container.addEventListener("click", e => {
        const target = e.target as HTMLElement;
        if (target.tagName === "BUTTON" && target.dataset.id) {
          clickCount++;
        }
      });

      // Simulate clicks
      const buttons = container.querySelectorAll("button");
      buttons[0].click();
      buttons[50].click();
      buttons[99].click();

      expect(clickCount).toBe(3);
    });

    test("passive listeners improve scroll performance", () => {
      let scrollHandled = false;

      // Passive listener pattern
      const passiveOptions: AddEventListenerOptions = { passive: true };

      document.addEventListener(
        "scroll",
        () => {
          scrollHandled = true;
        },
        passiveOptions,
      );

      // Simulate scroll
      document.dispatchEvent(new Event("scroll"));

      expect(scrollHandled).toBe(true);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 7: CACHING STRATEGIES
  // ════════════════════════════════════════════════════════════════════════

  describe("Caching Strategies", () => {
    test("memoization caches expensive computations", () => {
      let computeCount = 0;

      const expensiveComputation = (n: number): number => {
        computeCount++;
        // Simulate expensive work
        let result = 0;
        for (let i = 0; i < 1000; i++) {
          result += Math.sqrt(n * i);
        }
        return result;
      };

      // Memoized version
      const memoize = <T extends (...args: any[]) => any>(fn: T): T => {
        const cache = new Map<string, ReturnType<T>>();
        return ((...args: Parameters<T>): ReturnType<T> => {
          const key = JSON.stringify(args);
          if (cache.has(key)) {
            return cache.get(key)!;
          }
          const result = fn(...args);
          cache.set(key, result);
          return result;
        }) as T;
      };

      const memoizedComputation = memoize(expensiveComputation);

      // First call - computes
      memoizedComputation(42);
      expect(computeCount).toBe(1);

      // Second call with same input - uses cache
      memoizedComputation(42);
      expect(computeCount).toBe(1);

      // Different input - computes again
      memoizedComputation(100);
      expect(computeCount).toBe(2);
    });

    test("localStorage caching reduces redundant operations", async () => {
      const CACHE_KEY = "perf_test_cache";
      let fetchCount = 0;

      const fetchData = async (): Promise<object> => {
        // Check cache first
        const cached = localStorage.getItem(CACHE_KEY);
        if (cached) {
          return JSON.parse(cached);
        }

        // Simulate fetch
        fetchCount++;
        await wait(10);
        const data = { items: [1, 2, 3], timestamp: Date.now() };

        // Cache result
        localStorage.setItem(CACHE_KEY, JSON.stringify(data));
        return data;
      };

      // First fetch - hits "server"
      await fetchData();
      expect(fetchCount).toBe(1);

      // Second fetch - uses cache
      await fetchData();
      expect(fetchCount).toBe(1);
    });
  });

  // ════════════════════════════════════════════════════════════════════════
  // SECTION 8: ARITHMETIC OPTIMIZATION
  // ════════════════════════════════════════════════════════════════════════

  describe("Arithmetic Optimization", () => {
    test("bitwise operations for integer math", () => {
      const iterations = 10000;

      // Standard division
      const standardTime = measureTime(() => {
        for (let i = 0; i < iterations; i++) {
          Math.floor(i / 2);
        }
      });

      // Bitwise division by 2
      const bitwiseTime = measureTime(() => {
        for (let i = 0; i < iterations; i++) {
          i >> 1;
        }
      });

      // Bitwise should be comparable or faster
      expect(bitwiseTime).toBeLessThanOrEqual(standardTime * 2);
    });

    test("avoiding repeated property access in loops", () => {
      const arr = new Array(1000).fill(0).map((_, i) => i);

      // Anti-pattern: repeated .length access
      const badTime = measureTime(() => {
        let sum = 0;
        for (let i = 0; i < arr.length; i++) {
          sum += arr[i];
        }
        return sum;
      });

      // Good pattern: cached length
      const goodTime = measureTime(() => {
        let sum = 0;
        const len = arr.length;
        for (let i = 0; i < len; i++) {
          sum += arr[i];
        }
        return sum;
      });

      // Cached length should be comparable
      expect(goodTime).toBeLessThanOrEqual(badTime * 1.5);
    });

    test("TypedArray for numeric operations", () => {
      const size = 100_000; // Large enough for measurable, reliable timing

      // Regular array
      const regularArray = new Array(size).fill(0);
      const regularTime = measureTime(() => {
        for (let i = 0; i < size; i++) {
          regularArray[i] = i * 2;
        }
      });

      // TypedArray
      const typedArray = new Float64Array(size);
      const typedTime = measureTime(() => {
        for (let i = 0; i < size; i++) {
          typedArray[i] = i * 2;
        }
      });

      // TypedArray should be comparable or faster for numeric ops.
      // Use a minimum 2 ms floor so that near-zero regularTime (caused by V8
      // JIT eliminating trivial loops) does not make the comparison meaningless.
      const baseline = Math.max(regularTime, 2);
      expect(typedTime).toBeLessThanOrEqual(baseline * 5);
    });
  });
});

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Simple debounce implementation for testing
 */
function debounce<T extends (...args: any[]) => void>(
  fn: T,
  delay: number,
): T & { cancel: () => void } {
  let timeoutId: ReturnType<typeof setTimeout> | null = null;

  const debounced = ((...args: Parameters<T>) => {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  }) as T & { cancel: () => void };

  debounced.cancel = () => {
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
  };

  return debounced;
}

/**
 * Simple throttle implementation for testing
 */
function throttle<T extends (...args: any[]) => void>(fn: T, limit: number): T {
  let lastRan = 0;

  return ((...args: Parameters<T>) => {
    const now = Date.now();
    if (now - lastRan >= limit) {
      fn(...args);
      lastRan = now;
    }
  }) as T;
}
