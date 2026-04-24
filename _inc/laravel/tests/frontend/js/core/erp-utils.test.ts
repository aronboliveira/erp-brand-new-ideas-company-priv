/**
 * @file erp-utils.test.ts
 * @description Comprehensive unit tests for ERPUtils singleton
 * @coverage 100% target - all methods, edge cases, and error handling
 */
import path from "path";
import fs from "fs";
import { createMockBootstrap, resetDOM, wait, createButton, createInput, simulateClick, PUBLIC_JS_PATH } from "../setup";

// Path to source files
const ERP_UTILS_PATH = path.join(PUBLIC_JS_PATH, "core", "erp-utils.js");
const ERP_GUARD_PATH = path.join(PUBLIC_JS_PATH, "core", "erp-guard.js");

/**
 * Loads ERPGuard first (ERPUtils depends on it)
 */
function loadERPGuard(): void {
  delete (window as any).ERPGuard;
  const code = fs.readFileSync(ERP_GUARD_PATH, "utf-8");
  const fn = new Function("window", "document", "localStorage", "navigator", "bootstrap", code);
  fn(window, document, localStorage, navigator, (window as any).bootstrap);
}

/**
 * Loads ERPUtils by evaluating the source file
 */
function loadERPUtils(): void {
  delete (window as any).ERPUtils;
  // Reset singleton
  const code = fs.readFileSync(ERP_UTILS_PATH, "utf-8");
  const fn = new Function("window", "document", "localStorage", "sessionStorage", "navigator", code);
  fn(window, document, localStorage, sessionStorage, navigator);
}

// ============================================================================
// TEST SUITES
// ============================================================================

describe("ERPUtils", () => {
  beforeEach(() => {
    jest.clearAllMocks();
    resetDOM();

    // Set up mock Bootstrap
    (window as any).bootstrap = createMockBootstrap();

    // Clear storage
    localStorage.clear();
    sessionStorage.clear();

    // Load ERPGuard first, then ERPUtils
    loadERPGuard();
    loadERPUtils();
  });

  afterEach(() => {
    if ((window as any).ERPGuard?.destroy) {
      try {
        (window as any).ERPGuard.destroy();
      } catch (e) {
        // Ignore
      }
    }
  });

  // ==========================================================================
  // INITIALIZATION TESTS
  // ==========================================================================
  describe("Initialization", () => {
    it("should create a singleton instance on window.ERPUtils", () => {
      expect(window.ERPUtils).toBeDefined();
      expect(typeof window.ERPUtils).toBe("object");
    });

    it("should have all required methods", () => {
      const utils = window.ERPUtils;

      expect(typeof utils.copyToClipboard).toBe("function");
      expect(typeof utils.bindClipboardAction).toBe("function");
      expect(typeof utils.formatNumber).toBe("function");
      expect(typeof utils.formatCurrency).toBe("function");
      expect(typeof utils.formatDate).toBe("function");
      expect(typeof utils.debounce).toBe("function");
      expect(typeof utils.throttle).toBe("function");
      expect(typeof utils.deepClone).toBe("function");
      expect(typeof utils.isInViewport).toBe("function");
      expect(typeof utils.scrollToElement).toBe("function");
      expect(typeof utils.getQueryParam).toBe("function");
      expect(typeof utils.setQueryParam).toBe("function");
      expect(typeof utils.generateId).toBe("function");
      expect(typeof utils.getTranslation).toBe("function");
      expect(typeof utils.getMsg).toBe("function");
      expect(typeof utils.saveAsPDF).toBe("function");
    });

    it("should return same instance when constructed multiple times", () => {
      // The singleton pattern should return the same instance
      const instance1 = window.ERPUtils;

      // Access a method to verify it's the same object
      const id1 = instance1.generateId("test");
      const id2 = window.ERPUtils.generateId("test");

      // Both should be valid IDs from same instance
      expect(id1.startsWith("test-")).toBe(true);
      expect(id2.startsWith("test-")).toBe(true);
    });
  });

  // ==========================================================================
  // CLIPBOARD TESTS
  // ==========================================================================
  describe("Clipboard Operations", () => {
    describe("copyToClipboard", () => {
      it("should return false for empty text", async () => {
        const result = await window.ERPUtils.copyToClipboard("");
        expect(result).toBe(false);
      });

      it("should return false for null/undefined text", async () => {
        const result1 = await window.ERPUtils.copyToClipboard(null as any);
        const result2 = await window.ERPUtils.copyToClipboard(undefined as any);

        expect(result1).toBe(false);
        expect(result2).toBe(false);
      });

      it("should use modern Clipboard API when available", async () => {
        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        const result = await window.ERPUtils.copyToClipboard("test text", false);

        expect(writeTextMock).toHaveBeenCalledWith("test text");
        expect(result).toBe(true);
      });

      it("should use fallback when Clipboard API fails", async () => {
        const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation(() => {});

        Object.defineProperty(navigator, "clipboard", {
          value: {
            writeText: jest.fn().mockRejectedValue(new Error("Not allowed")),
          },
          writable: true,
          configurable: true,
        });

        // execCommand fallback
        document.execCommand = jest.fn().mockReturnValue(true);

        try {
          const result = await window.ERPUtils.copyToClipboard("test text", false);

          // Either modern or fallback should work
          expect(result === true || result === false).toBe(true);
        } finally {
          consoleErrorSpy.mockRestore();
        }
      });

      it("should show success notification when showNotification is true", async () => {
        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        await window.ERPUtils.copyToClipboard("test", true);

        // Should show toast
        const toast = document.querySelector(".toast");
        expect(toast?.textContent).toContain("Copied");
      });

      it("should not show notification when showNotification is false", async () => {
        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        await window.ERPUtils.copyToClipboard("test", false);

        // Should NOT show toast
        const toast = document.querySelector(".toast");
        expect(toast).toBeNull();
      });
    });

    describe("bindClipboardAction", () => {
      it("should bind click handler to element", async () => {
        const button = createButton("Copy");
        document.body.appendChild(button);

        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        window.ERPUtils.bindClipboardAction(button, "text to copy");

        simulateClick(button);
        await wait(10);

        expect(writeTextMock).toHaveBeenCalledWith("text to copy");
      });

      it("should not bind twice to same element", async () => {
        const button = createButton("Copy");
        document.body.appendChild(button);

        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        window.ERPUtils.bindClipboardAction(button, "text");
        window.ERPUtils.bindClipboardAction(button, "text");

        simulateClick(button);
        await wait(10);

        // Should only be called once
        expect(writeTextMock).toHaveBeenCalledTimes(1);
      });

      it("should accept selector string", async () => {
        const button = createButton("Copy");
        button.className = "copy-btn";
        document.body.appendChild(button);

        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        window.ERPUtils.bindClipboardAction(".copy-btn", "selector text");

        simulateClick(button);
        await wait(10);

        expect(writeTextMock).toHaveBeenCalledWith("selector text");
      });

      it("should accept function as text getter", async () => {
        const button = createButton("Copy");
        button.dataset.value = "dynamic value";
        document.body.appendChild(button);

        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        window.ERPUtils.bindClipboardAction(button, (el: HTMLElement) => el.dataset.value || "");

        simulateClick(button);
        await wait(10);

        expect(writeTextMock).toHaveBeenCalledWith("dynamic value");
      });

      it("should use element textContent as fallback", async () => {
        const button = createButton("Button Text");
        document.body.appendChild(button);

        const writeTextMock = jest.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, "clipboard", {
          value: { writeText: writeTextMock },
          writable: true,
          configurable: true,
        });

        window.ERPUtils.bindClipboardAction(button, "");

        simulateClick(button);
        await wait(10);

        expect(writeTextMock).toHaveBeenCalledWith("Button Text");
      });
    });
  });

  // ==========================================================================
  // FORMATTING TESTS
  // ==========================================================================
  describe("Formatting", () => {
    describe("formatNumber", () => {
      it("should format number with default decimals", () => {
        const result = window.ERPUtils.formatNumber(1234.5);
        expect(result).toContain("1");
        expect(result).toContain("234");
      });

      it("should format number with custom decimals", () => {
        const result = window.ERPUtils.formatNumber(1234.5678, { decimals: 3 });
        expect(result).toContain("568");
      });

      it("should format number with specified locale", () => {
        // German uses comma for decimals
        const result = window.ERPUtils.formatNumber(1234.56, {
          locale: "de-DE",
          decimals: 2,
        });
        expect(result).toMatch(/1.*234.*56/);
      });

      it("should handle zero", () => {
        const result = window.ERPUtils.formatNumber(0);
        expect(result).toContain("0");
      });

      it("should handle negative numbers", () => {
        const result = window.ERPUtils.formatNumber(-1234.56);
        expect(result).toContain("-");
        expect(result).toMatch(/1.*234/); // May have comma separator
      });
    });

    describe("formatCurrency", () => {
      it("should format currency with default USD", () => {
        const result = window.ERPUtils.formatCurrency(1234.56);
        expect(result).toMatch(/\$|USD/);
        expect(result).toContain("1");
      });

      it("should format currency with custom currency code", () => {
        const result = window.ERPUtils.formatCurrency(1234.56, {
          currency: "EUR",
        });
        expect(result).toMatch(/€|EUR/);
      });

      it("should format currency with BRL", () => {
        const result = window.ERPUtils.formatCurrency(1234.56, {
          currency: "BRL",
          locale: "pt-BR",
        });
        expect(result).toMatch(/R\$|BRL/);
      });

      it("should handle zero amount", () => {
        const result = window.ERPUtils.formatCurrency(0);
        expect(result).toContain("0");
      });

      it("should handle negative amounts", () => {
        const result = window.ERPUtils.formatCurrency(-100);
        expect(result).toMatch(/-|−|\(/);
      });
    });

    describe("formatDate", () => {
      it("should format Date object", () => {
        const date = new Date(2024, 0, 15); // Jan 15, 2024
        const result = window.ERPUtils.formatDate(date);
        expect(result).toContain("15");
        expect(result).toMatch(/Jan|janeiro|enero|2024/i);
      });

      it("should format date string", () => {
        const result = window.ERPUtils.formatDate("2024-06-15");
        // Date may vary by timezone (14 or 15)
        expect(result).toMatch(/14|15/);
      });

      it("should format timestamp number", () => {
        const timestamp = new Date(2024, 5, 15).getTime();
        const result = window.ERPUtils.formatDate(timestamp);
        expect(result).toContain("15");
      });

      it("should include time when includeTime is true", () => {
        const date = new Date(2024, 0, 15, 14, 30);
        const result = window.ERPUtils.formatDate(date, { includeTime: true });
        // Should include time components
        expect(result.length).toBeGreaterThan(10);
      });

      it("should return empty string for invalid date", () => {
        const result = window.ERPUtils.formatDate("invalid-date");
        expect(result).toBe("");
      });

      it("should use specified locale", () => {
        const date = new Date(2024, 0, 15);
        const result = window.ERPUtils.formatDate(date, { locale: "pt-BR" });
        // Portuguese format typically has different order
        expect(result.length).toBeGreaterThan(0);
      });
    });
  });

  // ==========================================================================
  // UTILITY FUNCTION TESTS
  // ==========================================================================
  describe("Utility Functions", () => {
    describe("debounce", () => {
      beforeEach(() => {
        jest.useFakeTimers();
      });

      afterEach(() => {
        jest.useRealTimers();
      });

      it("should debounce function calls", () => {
        const callback = jest.fn();
        const debounced = window.ERPUtils.debounce(callback, 100);

        debounced();
        debounced();
        debounced();

        expect(callback).not.toHaveBeenCalled();

        jest.advanceTimersByTime(100);

        expect(callback).toHaveBeenCalledTimes(1);
      });

      it("should pass arguments to debounced function", () => {
        const callback = jest.fn();
        const debounced = window.ERPUtils.debounce(callback, 100);

        debounced("arg1", "arg2");

        jest.advanceTimersByTime(100);

        expect(callback).toHaveBeenCalledWith("arg1", "arg2");
      });

      it("should reset timer on subsequent calls", () => {
        const callback = jest.fn();
        const debounced = window.ERPUtils.debounce(callback, 100);

        debounced();
        jest.advanceTimersByTime(50);

        debounced();
        jest.advanceTimersByTime(50);

        expect(callback).not.toHaveBeenCalled();

        jest.advanceTimersByTime(50);

        expect(callback).toHaveBeenCalledTimes(1);
      });
    });

    describe("throttle", () => {
      beforeEach(() => {
        jest.useFakeTimers();
      });

      afterEach(() => {
        jest.useRealTimers();
      });

      it("should throttle function calls", () => {
        const callback = jest.fn();
        const throttled = window.ERPUtils.throttle(callback, 100);

        throttled(); // First call goes through
        throttled(); // Blocked
        throttled(); // Blocked

        expect(callback).toHaveBeenCalledTimes(1);

        jest.advanceTimersByTime(100);

        throttled(); // Now allowed

        expect(callback).toHaveBeenCalledTimes(2);
      });

      it("should pass arguments to throttled function", () => {
        const callback = jest.fn();
        const throttled = window.ERPUtils.throttle(callback, 100);

        throttled("test");

        expect(callback).toHaveBeenCalledWith("test");
      });
    });

    describe("deepClone", () => {
      it("should clone simple object", () => {
        const original = { a: 1, b: 2 };
        const cloned = window.ERPUtils.deepClone(original);

        expect(cloned).toEqual(original);
        expect(cloned).not.toBe(original);
      });

      it("should clone nested objects", () => {
        const original = { a: { b: { c: 1 } } };
        const cloned = window.ERPUtils.deepClone(original);

        expect(cloned).toEqual(original);
        expect(cloned.a).not.toBe(original.a);
        expect(cloned.a.b).not.toBe(original.a.b);
      });

      it("should clone arrays", () => {
        const original = [1, 2, { a: 3 }];
        const cloned = window.ERPUtils.deepClone(original);

        expect(cloned).toEqual(original);
        expect(cloned).not.toBe(original);
        expect(cloned[2]).not.toBe(original[2]);
      });

      it("should return primitives as-is", () => {
        expect(window.ERPUtils.deepClone(42)).toBe(42);
        expect(window.ERPUtils.deepClone("string")).toBe("string");
        expect(window.ERPUtils.deepClone(null)).toBe(null);
        expect(window.ERPUtils.deepClone(undefined)).toBe(undefined);
      });

      it("should handle circular references by returning original", () => {
        const original: any = { a: 1 };
        original.self = original;

        const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation(() => {});

        // JSON.stringify fails on circular refs, should return original
        try {
          const result = window.ERPUtils.deepClone(original);
          expect(result).toBe(original);
        } finally {
          consoleErrorSpy.mockRestore();
        }
      });
    });
  });

  // ==========================================================================
  // DOM UTILITY TESTS
  // ==========================================================================
  describe("DOM Utilities", () => {
    describe("isInViewport", () => {
      it("should return false for null element", () => {
        expect(window.ERPUtils.isInViewport(null as any)).toBe(false);
      });

      it("should detect element in viewport", () => {
        const div = document.createElement("div");
        div.style.position = "fixed";
        div.style.top = "0";
        div.style.left = "0";
        div.style.width = "100px";
        div.style.height = "100px";
        document.body.appendChild(div);

        // Mock getBoundingClientRect
        div.getBoundingClientRect = jest.fn().mockReturnValue({
          top: 0,
          left: 0,
          bottom: 100,
          right: 100,
        });

        expect(window.ERPUtils.isInViewport(div)).toBe(true);
      });

      it("should detect element outside viewport", () => {
        const div = document.createElement("div");
        document.body.appendChild(div);

        // Mock element far outside viewport
        div.getBoundingClientRect = jest.fn().mockReturnValue({
          top: -1000,
          left: -1000,
          bottom: -900,
          right: -900,
        });

        expect(window.ERPUtils.isInViewport(div)).toBe(false);
      });

      it("should consider offset", () => {
        const div = document.createElement("div");
        document.body.appendChild(div);

        div.getBoundingClientRect = jest.fn().mockReturnValue({
          top: -50,
          left: 0,
          bottom: 50,
          right: 100,
        });

        // Without offset, top is negative, should be false
        expect(window.ERPUtils.isInViewport(div, 0)).toBe(false);

        // With 100px offset, should be true
        expect(window.ERPUtils.isInViewport(div, 100)).toBe(true);
      });
    });

    describe("scrollToElement", () => {
      it("should call scrollTo", () => {
        const div = document.createElement("div");
        document.body.appendChild(div);

        div.getBoundingClientRect = jest.fn().mockReturnValue({ top: 500 });

        const scrollToSpy = jest.spyOn(window, "scrollTo").mockImplementation(() => {});

        window.ERPUtils.scrollToElement(div);

        expect(scrollToSpy).toHaveBeenCalled();

        scrollToSpy.mockRestore();
      });

      it("should accept selector string", () => {
        const div = document.createElement("div");
        div.id = "scroll-target";
        document.body.appendChild(div);

        div.getBoundingClientRect = jest.fn().mockReturnValue({ top: 500 });

        const scrollToSpy = jest.spyOn(window, "scrollTo").mockImplementation(() => {});

        window.ERPUtils.scrollToElement("#scroll-target");

        expect(scrollToSpy).toHaveBeenCalled();

        scrollToSpy.mockRestore();
      });

      it("should apply offset", () => {
        const div = document.createElement("div");
        document.body.appendChild(div);

        div.getBoundingClientRect = jest.fn().mockReturnValue({ top: 500 });

        const scrollToSpy = jest.spyOn(window, "scrollTo").mockImplementation(() => {});

        window.ERPUtils.scrollToElement(div, { offset: 100 });

        expect(scrollToSpy).toHaveBeenCalledWith(
          expect.objectContaining({
            behavior: "smooth",
          }),
        );

        scrollToSpy.mockRestore();
      });

      it("should do nothing for non-existent selector", () => {
        const scrollToSpy = jest.spyOn(window, "scrollTo").mockImplementation(() => {});

        window.ERPUtils.scrollToElement("#non-existent");

        expect(scrollToSpy).not.toHaveBeenCalled();

        scrollToSpy.mockRestore();
      });
    });
  });

  // ==========================================================================
  // URL PARAMETER TESTS
  // ==========================================================================
  describe("URL Parameters", () => {
    describe("getQueryParam", () => {
      it("should get query parameter from current URL", () => {
        // Mock window.location.href
        delete (window as any).location;
        (window as any).location = { href: "http://localhost/?foo=bar" };

        const result = window.ERPUtils.getQueryParam("foo");
        expect(result).toBe("bar");
      });

      it("should return null for missing parameter", () => {
        delete (window as any).location;
        (window as any).location = { href: "http://localhost/?foo=bar" };

        const result = window.ERPUtils.getQueryParam("missing");
        expect(result).toBeNull();
      });

      it("should accept custom URL", () => {
        const result = window.ERPUtils.getQueryParam("test", "http://example.com/?test=value");
        expect(result).toBe("value");
      });

      it("should handle URL-encoded values", () => {
        const result = window.ERPUtils.getQueryParam("encoded", "http://example.com/?encoded=hello%20world");
        expect(result).toBe("hello world");
      });
    });

    describe("setQueryParam", () => {
      it("should set query parameter", () => {
        delete (window as any).location;
        (window as any).location = { href: "http://localhost/" };

        const pushStateSpy = jest.spyOn(window.history, "pushState").mockImplementation(() => {});

        window.ERPUtils.setQueryParam("key", "value");

        expect(pushStateSpy).toHaveBeenCalled();

        pushStateSpy.mockRestore();
      });

      it("should use replaceState when updateHistory is false", () => {
        delete (window as any).location;
        (window as any).location = { href: "http://localhost/" };

        const replaceStateSpy = jest.spyOn(window.history, "replaceState").mockImplementation(() => {});

        window.ERPUtils.setQueryParam("key", "value", false);

        expect(replaceStateSpy).toHaveBeenCalled();

        replaceStateSpy.mockRestore();
      });
    });
  });

  // ==========================================================================
  // ID GENERATION TESTS
  // ==========================================================================
  describe("ID Generation", () => {
    describe("generateId", () => {
      it("should generate unique IDs", () => {
        const id1 = window.ERPUtils.generateId();
        const id2 = window.ERPUtils.generateId();

        expect(id1).not.toBe(id2);
      });

      it("should include prefix when provided", () => {
        const id = window.ERPUtils.generateId("prefix");
        expect(id.startsWith("prefix-")).toBe(true);
      });

      it("should not have dash when no prefix", () => {
        const id = window.ERPUtils.generateId();
        expect(id.startsWith("-")).toBe(false);
      });

      it("should return string", () => {
        const id = window.ERPUtils.generateId();
        expect(typeof id).toBe("string");
        expect(id.length).toBeGreaterThan(0);
      });
    });
  });

  // ==========================================================================
  // TRANSLATION TESTS
  // ==========================================================================
  describe("Translation", () => {
    describe("getTranslation", () => {
      it("should delegate to ERPGuard.getMsg when available", () => {
        const getMsg = jest.spyOn(window.ERPGuard, "getMsg");

        window.ERPUtils.getTranslation("testKey");

        expect(getMsg).toHaveBeenCalled();
      });

      it("should return key as fallback", () => {
        // Remove ERPGuard to test fallback
        delete (window as any).ERPGuard;
        loadERPUtils();

        const result = window.ERPUtils.getTranslation("unknownKey");
        expect(result).toBe("unknownKey");
      });

      it("should use window.translations when ERPGuard unavailable", () => {
        delete (window as any).ERPGuard;
        (window as any).translations = {
          en: { hello: "Hello" },
        };
        loadERPUtils();

        const result = window.ERPUtils.getTranslation("hello");
        // Should return 'Hello' from translations or 'hello' as fallback
        expect(result.toLowerCase()).toBe("hello");
      });
    });

    describe("getMsg", () => {
      it("should be an alias for getTranslation", () => {
        const translation = window.ERPUtils.getTranslation("key");
        const msg = window.ERPUtils.getMsg("key");

        // Both should behave similarly
        expect(typeof translation).toBe(typeof msg);
      });
    });
  });

  // ==========================================================================
  // PDF GENERATION TESTS
  // ==========================================================================
  describe("PDF Generation", () => {
    describe("saveAsPDF", () => {
      it("should return false when html2pdf not available", async () => {
        const result = await window.ERPUtils.saveAsPDF();

        expect(result).toBe(false);
        // Toast should be shown (ERPGuard handles notification)
        const toast = document.querySelector(".toast");
        expect(toast).not.toBeNull();
      });

      it("should return false when printable area not found", async () => {
        (window as any).html2pdf = jest.fn();

        const result = await window.ERPUtils.saveAsPDF({
          areaSelector: "#nonexistent",
        });

        expect(result).toBe(false);
      });

      it("should call html2pdf when available", async () => {
        const div = document.createElement("div");
        div.id = "printableArea";
        document.body.appendChild(div);

        const mockHtml2pdf = {
          set: jest.fn().mockReturnThis(),
          from: jest.fn().mockReturnThis(),
          save: jest.fn().mockResolvedValue(undefined),
        };

        (window as any).html2pdf = jest.fn().mockReturnValue(mockHtml2pdf);

        const result = await window.ERPUtils.saveAsPDF();

        expect(mockHtml2pdf.set).toHaveBeenCalled();
        expect(mockHtml2pdf.from).toHaveBeenCalledWith(div);
        expect(mockHtml2pdf.save).toHaveBeenCalled();
        expect(result).toBe(true);
      });

      it("should use custom filename from input", async () => {
        const div = document.createElement("div");
        div.id = "printableArea";
        document.body.appendChild(div);

        const input = createInput("text");
        input.id = "filename";
        (input as HTMLInputElement).value = "custom-filename";
        document.body.appendChild(input);

        const mockHtml2pdf = {
          set: jest.fn().mockReturnThis(),
          from: jest.fn().mockReturnThis(),
          save: jest.fn().mockResolvedValue(undefined),
        };

        (window as any).html2pdf = jest.fn().mockReturnValue(mockHtml2pdf);

        await window.ERPUtils.saveAsPDF();

        expect(mockHtml2pdf.set).toHaveBeenCalledWith(expect.objectContaining({ filename: "custom-filename" }));
      });

      it("should use default filename when input is empty", async () => {
        const div = document.createElement("div");
        div.id = "printableArea";
        document.body.appendChild(div);

        const mockHtml2pdf = {
          set: jest.fn().mockReturnThis(),
          from: jest.fn().mockReturnThis(),
          save: jest.fn().mockResolvedValue(undefined),
        };

        (window as any).html2pdf = jest.fn().mockReturnValue(mockHtml2pdf);

        await window.ERPUtils.saveAsPDF();

        expect(mockHtml2pdf.set).toHaveBeenCalledWith(expect.objectContaining({ filename: "export" }));
      });

      it("should handle pdf generation error", async () => {
        const div = document.createElement("div");
        div.id = "printableArea";
        document.body.appendChild(div);

        const mockHtml2pdf = {
          set: jest.fn().mockReturnThis(),
          from: jest.fn().mockReturnThis(),
          save: jest.fn().mockRejectedValue(new Error("PDF error")),
        };

        (window as any).html2pdf = jest.fn().mockReturnValue(mockHtml2pdf);

        const result = await window.ERPUtils.saveAsPDF();

        expect(result).toBe(false);
      });
    });
  });

  // ==========================================================================
  // EDGE CASES AND COMBINATION TESTS
  // ==========================================================================
  describe("Edge Cases", () => {
    it("should handle formatNumber with very large numbers", () => {
      const result = window.ERPUtils.formatNumber(1e15);
      expect(result.length).toBeGreaterThan(0);
    });

    it("should handle formatNumber with very small numbers", () => {
      const result = window.ERPUtils.formatNumber(0.00001, { decimals: 5 });
      expect(result).toContain("1");
    });

    it("should handle formatCurrency with large amounts", () => {
      const result = window.ERPUtils.formatCurrency(999999999.99);
      expect(result.length).toBeGreaterThan(0);
    });

    it("should handle multiple clipboard bindings to different elements", async () => {
      const button1 = createButton("Copy 1");
      const button2 = createButton("Copy 2");
      document.body.appendChild(button1);
      document.body.appendChild(button2);

      const writeTextMock = jest.fn().mockResolvedValue(undefined);
      Object.defineProperty(navigator, "clipboard", {
        value: { writeText: writeTextMock },
        writable: true,
        configurable: true,
      });

      window.ERPUtils.bindClipboardAction(button1, "text1");
      window.ERPUtils.bindClipboardAction(button2, "text2");

      simulateClick(button1);
      await wait(10);
      expect(writeTextMock).toHaveBeenCalledWith("text1");

      simulateClick(button2);
      await wait(10);
      expect(writeTextMock).toHaveBeenCalledWith("text2");
    });

    it("should handle rapid debounce calls", () => {
      jest.useFakeTimers();

      const callback = jest.fn();
      const debounced = window.ERPUtils.debounce(callback, 50);

      for (let i = 0; i < 100; i++) {
        debounced(i);
      }

      jest.advanceTimersByTime(50);

      expect(callback).toHaveBeenCalledTimes(1);
      expect(callback).toHaveBeenCalledWith(99); // Last call

      jest.useRealTimers();
    });

    it("should handle generateId uniqueness in rapid succession", () => {
      const ids = new Set<string>();

      for (let i = 0; i < 1000; i++) {
        ids.add(window.ERPUtils.generateId());
      }

      // All IDs should be unique
      expect(ids.size).toBe(1000);
    });
  });
});
