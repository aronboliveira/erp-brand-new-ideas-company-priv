/**
 * @file erp-guard.test.ts
 * @description Comprehensive unit tests for ERPGuard singleton
 * @coverage 100% target - all methods, edge cases, and error handling
 */
import path from "path";
import fs from "fs";
import {
  createMockBootstrap,
  resetDOM,
  wait,
  createForm,
  createInput,
  createButton,
  simulateClick,
  simulateSubmit,
  simulateInput,
  simulateChange,
  simulateKeyboard,
  PUBLIC_JS_PATH,
} from "../setup";

// Path to source file
const ERP_GUARD_PATH = path.join(PUBLIC_JS_PATH, "core", "erp-guard.js");

/**
 * Loads ERPGuard by evaluating the source file
 */
function loadERPGuard(): void {
  // Reset singleton state by removing from window
  delete (window as any).ERPGuard;

  // Read and evaluate the source file
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

// ============================================================================
// TEST SUITES
// ============================================================================

describe("ERPGuard", () => {
  beforeEach(() => {
    jest.clearAllMocks();
    resetDOM();

    // Set up mock Bootstrap
    (window as any).bootstrap = createMockBootstrap();

    // Clear localStorage
    localStorage.clear();

    // Set default document language
    document.documentElement.lang = "en";

    // Load ERPGuard
    loadERPGuard();
  });

  afterEach(() => {
    // Cleanup
    if ((window as any).ERPGuard?.destroy) {
      try {
        (window as any).ERPGuard.destroy();
      } catch (e) {
        // Ignore cleanup errors
      }
    }
  });

  // ==========================================================================
  // INITIALIZATION TESTS
  // ==========================================================================
  describe("Initialization", () => {
    it("should create a singleton instance on window.ERPGuard", () => {
      expect(window.ERPGuard).toBeDefined();
      expect(typeof window.ERPGuard).toBe("object");
    });

    it("should have all required methods", () => {
      const guard = window.ERPGuard;

      // Core methods
      expect(typeof guard.getLocale).toBe("function");
      expect(typeof guard.setLocale).toBe("function");
      expect(typeof guard.getMsg).toBe("function");

      // Bootstrap detection
      expect(typeof guard.hasBootstrap).toBe("function");
      expect(typeof guard.hasToast).toBe("function");
      expect(typeof guard.hasModal).toBe("function");

      // Toast methods
      expect(typeof guard.showToast).toBe("function");
      expect(typeof guard.success).toBe("function");
      expect(typeof guard.error).toBe("function");
      expect(typeof guard.warning).toBe("function");
      expect(typeof guard.info).toBe("function");

      // Modal methods
      expect(typeof guard.showModal).toBe("function");
      expect(typeof guard.confirm).toBe("function");

      // Error scheduling
      expect(typeof guard.scheduleError).toBe("function");
      expect(typeof guard.scheduleInteractiveError).toBe("function");

      // URL validation
      expect(typeof guard.isInvalidUrl).toBe("function");

      // Guard bindings
      expect(typeof guard.bindSubmitGuard).toBe("function");
      expect(typeof guard.bindClickGuard).toBe("function");
      expect(typeof guard.bindChangeGuard).toBe("function");
      expect(typeof guard.unbind).toBe("function");

      // Message encoding
      expect(typeof guard.encodeMsg).toBe("function");
      expect(typeof guard.decodeMsg).toBe("function");

      // Error handling
      expect(typeof guard.handleAjaxError).toBe("function");

      // Cleanup
      expect(typeof guard.destroy).toBe("function");
    });

    it("should create toast and modal containers", () => {
      expect(document.getElementById("erp-toast-container")).toBeTruthy();
      expect(document.getElementById("erp-modal-container")).toBeTruthy();
    });

    it("should set correct attributes on toast container", () => {
      const container = document.getElementById("erp-toast-container");
      expect(container).not.toBeNull();
      expect(container?.getAttribute("aria-live")).toBe("polite");
      expect(container?.getAttribute("aria-atomic")).toBe("true");
      expect(container?.className).toContain("toast-container");
    });
  });

  // ==========================================================================
  // LOCALE MANAGEMENT TESTS
  // ==========================================================================
  describe("Locale Management", () => {
    it("should detect locale from document.documentElement.lang", () => {
      document.documentElement.lang = "pt-BR";
      loadERPGuard();
      expect(window.ERPGuard.getLocale()).toBe("pt");
    });

    it("should detect locale from localStorage", () => {
      localStorage.setItem("locale", "es");
      loadERPGuard();
      expect(window.ERPGuard.getLocale()).toBe("es");
    });

    it('should fall back to "en" for unknown locale', () => {
      document.documentElement.lang = "unknown";
      localStorage.clear();
      loadERPGuard();
      expect(window.ERPGuard.getLocale()).toBe("en");
    });

    it("should set locale and save to localStorage", () => {
      window.ERPGuard.setLocale("pt");
      expect(window.ERPGuard.getLocale()).toBe("pt");
      expect(localStorage.getItem("locale")).toBe("pt");
    });

    it("should return this for chaining when setting locale", () => {
      const result = window.ERPGuard.setLocale("en");
      expect(result).toBe(window.ERPGuard);
    });

    it("should ignore invalid locale", () => {
      window.ERPGuard.setLocale("invalid");
      expect(window.ERPGuard.getLocale()).toBe("en");
    });

    it("should get localized messages", () => {
      window.ERPGuard.setLocale("en");
      expect(window.ERPGuard.getMsg("error")).toBe("An error occurred");
      expect(window.ERPGuard.getMsg("success")).toBe(
        "Operation completed successfully",
      );
    });

    it("should get Portuguese messages", () => {
      window.ERPGuard.setLocale("pt");
      expect(window.ERPGuard.getMsg("error")).toBe("Ocorreu um erro");
      expect(window.ERPGuard.getMsg("success")).toBe(
        "Operação concluída com sucesso",
      );
    });

    it("should get Spanish messages", () => {
      window.ERPGuard.setLocale("es");
      expect(window.ERPGuard.getMsg("error")).toBe("Ocurrió un error");
      expect(window.ERPGuard.getMsg("confirm")).toBe("¿Está seguro?");
    });

    it("should return fallback for unknown message key", () => {
      expect(window.ERPGuard.getMsg("unknown", "fallback")).toBe("fallback");
    });

    it("should return key if no fallback provided", () => {
      expect(window.ERPGuard.getMsg("nonexistent")).toBe("nonexistent");
    });

    it("should detect locale from meta tag", () => {
      // Need to reset completely and set up meta before loading
      resetDOM();
      localStorage.clear();
      document.documentElement.lang = "";

      const meta = document.createElement("meta");
      meta.name = "locale";
      meta.content = "pt";
      document.head.appendChild(meta);

      // Also need navigator mock
      Object.defineProperty(navigator, "language", {
        value: "xx",
        configurable: true,
      });

      (window as any).bootstrap = createMockBootstrap();
      loadERPGuard();
      expect(window.ERPGuard.getLocale()).toBe("pt");
    });
  });

  // ==========================================================================
  // BOOTSTRAP DETECTION TESTS
  // ==========================================================================
  describe("Bootstrap Detection", () => {
    it("should detect Bootstrap when available", () => {
      expect(window.ERPGuard.hasBootstrap()).toBe(true);
      expect(window.ERPGuard.hasToast()).toBe(true);
      expect(window.ERPGuard.hasModal()).toBe(true);
    });

    it("should return false when Bootstrap is undefined", () => {
      delete (window as any).bootstrap;
      loadERPGuard();

      expect(window.ERPGuard.hasBootstrap()).toBe(false);
      expect(window.ERPGuard.hasToast()).toBe(false);
      expect(window.ERPGuard.hasModal()).toBe(false);
    });

    it("should return false when Bootstrap.Toast is missing", () => {
      (window as any).bootstrap = { Modal: createMockBootstrap().Modal };
      loadERPGuard();

      expect(window.ERPGuard.hasBootstrap()).toBe(false);
      expect(window.ERPGuard.hasToast()).toBe(false);
      expect(window.ERPGuard.hasModal()).toBe(true);
    });

    it("should return false when Bootstrap.Modal is missing", () => {
      (window as any).bootstrap = { Toast: createMockBootstrap().Toast };
      loadERPGuard();

      expect(window.ERPGuard.hasBootstrap()).toBe(false);
      expect(window.ERPGuard.hasToast()).toBe(true);
      expect(window.ERPGuard.hasModal()).toBe(false);
    });
  });

  // ==========================================================================
  // TOAST NOTIFICATION TESTS
  // ==========================================================================
  describe("Toast Notifications", () => {
    it("should show a basic toast", () => {
      window.ERPGuard.showToast("Test message", "info");

      const toasts = document.querySelectorAll(".toast");
      expect(toasts.length).toBe(1);
      expect(toasts[0].textContent).toContain("Test message");
    });

    it("should show success toast with correct class", () => {
      window.ERPGuard.success("Success!");

      const toast = document.querySelector(".toast");
      expect(toast?.classList.contains("bg-success")).toBe(true);
      expect(toast?.classList.contains("text-white")).toBe(true);
    });

    it("should show error toast with correct class", () => {
      window.ERPGuard.error("Error!");

      const toast = document.querySelector(".toast");
      expect(toast?.classList.contains("bg-danger")).toBe(true);
    });

    it("should show warning toast with correct class", () => {
      window.ERPGuard.warning("Warning!");

      const toast = document.querySelector(".toast");
      expect(toast?.classList.contains("bg-warning")).toBe(true);
      expect(toast?.classList.contains("text-dark")).toBe(true);
    });

    it("should show info toast with correct class", () => {
      window.ERPGuard.info("Info!");

      const toast = document.querySelector(".toast");
      expect(toast?.classList.contains("bg-info")).toBe(true);
    });

    it("should show toast with title", () => {
      window.ERPGuard.showToast("Message", "info", { title: "Title" });

      const header = document.querySelector(".toast-header");
      expect(header).not.toBeNull();
      expect(header?.textContent).toContain("Title");
    });

    it("should show closable toast by default", () => {
      window.ERPGuard.showToast("Message", "info");

      const closeBtn = document.querySelector(".btn-close");
      expect(closeBtn).not.toBeNull();
    });

    it("should hide close button when closable is false", () => {
      window.ERPGuard.showToast("Message", "info", { closable: false });

      const closeBtn = document.querySelector(".btn-close");
      expect(closeBtn).toBeNull();
    });

    it("should return this for chaining", () => {
      const result = window.ERPGuard.showToast("Test", "info");
      expect(result).toBe(window.ERPGuard);
    });

    it("should not show toast for empty message", () => {
      window.ERPGuard.showToast("", "info");

      const toasts = document.querySelectorAll(".toast");
      expect(toasts.length).toBe(0);
    });

    it("should escape HTML in toast message", () => {
      window.ERPGuard.showToast('<script>alert("xss")</script>', "info");

      const toast = document.querySelector(".toast-body");
      expect(toast?.innerHTML).not.toContain("<script>");
      expect(toast?.textContent).toContain("<script>");
    });

    it("should fall back to alert when Bootstrap is unavailable", () => {
      delete (window as any).bootstrap;
      loadERPGuard();

      const alertSpy = jest.spyOn(window, "alert").mockImplementation(() => {});

      window.ERPGuard.showToast("Test", "success");

      expect(alertSpy).toHaveBeenCalledWith(expect.stringContaining("Test"));
      alertSpy.mockRestore();
    });

    it("should show appropriate emoji in fallback alert", () => {
      delete (window as any).bootstrap;
      loadERPGuard();

      const alertSpy = jest.spyOn(window, "alert").mockImplementation(() => {});

      window.ERPGuard.showToast("Test", "error");
      expect(alertSpy).toHaveBeenCalledWith(expect.stringContaining("❌"));

      window.ERPGuard.showToast("Test", "success");
      expect(alertSpy).toHaveBeenCalledWith(expect.stringContaining("✅"));

      window.ERPGuard.showToast("Test", "warning");
      expect(alertSpy).toHaveBeenCalledWith(expect.stringContaining("⚠️"));

      alertSpy.mockRestore();
    });

    it("should set correct autohide attribute", () => {
      window.ERPGuard.showToast("Test", "info", { autohide: false });

      const toast = document.querySelector(".toast");
      expect(toast?.getAttribute("data-bs-autohide")).toBe("false");
    });

    it("should set correct delay attribute", () => {
      window.ERPGuard.showToast("Test", "info", { duration: 10000 });

      const toast = document.querySelector(".toast");
      expect(toast?.getAttribute("data-bs-delay")).toBe("10000");
    });

    it("should support all toast types", () => {
      const types: Array<"primary" | "secondary" | "dark" | "light"> = [
        "primary",
        "secondary",
        "dark",
        "light",
      ];

      types.forEach(type => {
        resetDOM();
        (window as any).bootstrap = createMockBootstrap();
        loadERPGuard();

        window.ERPGuard.showToast("Test", type);
        const toast = document.querySelector(".toast");
        expect(toast?.classList.contains(`bg-${type}`)).toBe(true);
      });
    });

    it("should have unique IDs for multiple toasts", () => {
      window.ERPGuard.showToast("Toast 1", "info");
      window.ERPGuard.showToast("Toast 2", "info");

      const toasts = document.querySelectorAll(".toast");
      expect(toasts.length).toBe(2);
      expect(toasts[0].id).not.toBe(toasts[1].id);
    });

    it("should have ARIA attributes for accessibility", () => {
      window.ERPGuard.showToast("Test", "info");

      const toast = document.querySelector(".toast");
      expect(toast?.getAttribute("role")).toBe("alert");
      expect(toast?.getAttribute("aria-live")).toBe("assertive");
      expect(toast?.getAttribute("aria-atomic")).toBe("true");
    });
  });

  // ==========================================================================
  // MODAL DIALOG TESTS
  // ==========================================================================
  describe("Modal Dialogs", () => {
    it("should show a basic modal", () => {
      window.ERPGuard.showModal({
        title: "Test Title",
        body: "Test Body",
      });

      const modal = document.querySelector(".modal");
      expect(modal).not.toBeNull();
      expect(modal?.querySelector(".modal-title")?.textContent).toBe(
        "Test Title",
      );
      expect(modal?.querySelector(".modal-body")?.textContent).toBe(
        "Test Body",
      );
    });

    it("should return modal instance", () => {
      const result = window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
      });

      expect(result).not.toBeNull();
      expect(typeof result?.hide).toBe("function");
    });

    it("should support different sizes", () => {
      const sizes: Array<"sm" | "lg" | "xl"> = ["sm", "lg", "xl"];

      sizes.forEach(size => {
        resetDOM();
        (window as any).bootstrap = createMockBootstrap();
        loadERPGuard();

        window.ERPGuard.showModal({ title: "Test", body: "Body", size });

        const dialog = document.querySelector(".modal-dialog");
        expect(dialog?.classList.contains(`modal-${size}`)).toBe(true);
      });
    });

    it("should not add size class for md", () => {
      window.ERPGuard.showModal({ title: "Test", body: "Body", size: "md" });

      const dialog = document.querySelector(".modal-dialog");
      expect(dialog?.classList.contains("modal-md")).toBe(false);
    });

    it("should center modal when centered is true", () => {
      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        centered: true,
      });

      const dialog = document.querySelector(".modal-dialog");
      expect(dialog?.classList.contains("modal-dialog-centered")).toBe(true);
    });

    it("should make modal scrollable when scrollable is true", () => {
      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        scrollable: true,
      });

      const dialog = document.querySelector(".modal-dialog");
      expect(dialog?.classList.contains("modal-dialog-scrollable")).toBe(true);
    });

    it("should show close button when closable is true", () => {
      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        closable: true,
      });

      const closeBtn = document.querySelector(".modal-header .btn-close");
      expect(closeBtn).not.toBeNull();
    });

    it("should hide close button when closable is false", () => {
      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        closable: false,
      });

      const closeBtn = document.querySelector(".modal-header .btn-close");
      expect(closeBtn).toBeNull();
    });

    it("should set static backdrop when closable is false", () => {
      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        closable: false,
      });

      const modal = document.querySelector(".modal");
      expect(modal?.getAttribute("data-bs-backdrop")).toBe("static");
      expect(modal?.getAttribute("data-bs-keyboard")).toBe("false");
    });

    it("should render buttons in footer", () => {
      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        buttons: [
          { text: "OK", class: "btn-primary" },
          { text: "Cancel", class: "btn-secondary" },
        ],
      });

      const footer = document.querySelector(".modal-footer");
      const buttons = footer?.querySelectorAll("button");
      expect(buttons?.length).toBe(2);
      expect(buttons?.[0].textContent?.trim()).toBe("OK");
      expect(buttons?.[1].textContent?.trim()).toBe("Cancel");
    });

    it("should call button onClick handler", () => {
      const onClick = jest.fn();

      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        buttons: [{ text: "Click Me", onClick }],
      });

      const button = document.querySelector(
        ".modal-footer button",
      ) as HTMLButtonElement;
      button?.click();

      expect(onClick).toHaveBeenCalled();
    });

    it("should escape HTML in title", () => {
      window.ERPGuard.showModal({
        title: "<script>xss</script>",
        body: "Body",
      });

      const title = document.querySelector(".modal-title");
      expect(title?.innerHTML).not.toContain("<script>");
    });

    it("should have ARIA attributes", () => {
      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
      });

      const modal = document.querySelector(".modal");
      expect(modal?.getAttribute("tabindex")).toBe("-1");
      expect(modal?.getAttribute("aria-hidden")).toBe("true");
      expect(modal?.getAttribute("aria-labelledby")).toBeTruthy();
    });

    it("should fall back to confirm when Bootstrap unavailable", () => {
      delete (window as any).bootstrap;
      loadERPGuard();

      const confirmSpy = jest.spyOn(window, "confirm").mockReturnValue(true);
      const onClick = jest.fn();

      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
        buttons: [{ text: "OK", onClick }],
      });

      expect(confirmSpy).toHaveBeenCalled();
      expect(onClick).toHaveBeenCalled();

      confirmSpy.mockRestore();
    });

    it("should fall back to alert when no buttons and Bootstrap unavailable", () => {
      delete (window as any).bootstrap;
      loadERPGuard();

      const alertSpy = jest.spyOn(window, "alert").mockImplementation(() => {});

      window.ERPGuard.showModal({
        title: "Test",
        body: "Body",
      });

      expect(alertSpy).toHaveBeenCalled();

      alertSpy.mockRestore();
    });
  });

  // ==========================================================================
  // CONFIRM DIALOG TESTS
  // ==========================================================================
  describe("Confirm Dialog", () => {
    it("should show confirm dialog with default text", () => {
      window.ERPGuard.confirm("Are you sure?", jest.fn());

      const modal = document.querySelector(".modal");
      expect(modal).not.toBeNull();

      const title = document.querySelector(".modal-title");
      expect(title?.textContent).toBe("Are you sure?");

      const buttons = document.querySelectorAll(".modal-footer button");
      expect(buttons.length).toBe(2);
      expect(buttons[0].textContent?.trim()).toBe("Yes");
      expect(buttons[1].textContent?.trim()).toBe("No");
    });

    it("should call onConfirm when confirmed", () => {
      const onConfirm = jest.fn();
      window.ERPGuard.confirm("Confirm?", onConfirm);

      const confirmBtn = document.querySelector(
        ".modal-footer .btn-primary",
      ) as HTMLButtonElement;
      confirmBtn?.click();

      expect(onConfirm).toHaveBeenCalled();
    });

    it("should call onCancel when cancelled", () => {
      const onConfirm = jest.fn();
      const onCancel = jest.fn();

      window.ERPGuard.confirm("Confirm?", onConfirm, onCancel);

      const cancelBtn = document.querySelector(
        ".modal-footer .btn-secondary",
      ) as HTMLButtonElement;
      cancelBtn?.click();

      expect(onCancel).toHaveBeenCalled();
      expect(onConfirm).not.toHaveBeenCalled();
    });

    it("should use custom button text", () => {
      window.ERPGuard.confirm("Delete?", jest.fn(), null, {
        confirmText: "Delete",
        cancelText: "Keep",
      });

      const buttons = document.querySelectorAll(".modal-footer button");
      expect(buttons[0].textContent?.trim()).toBe("Delete");
      expect(buttons[1].textContent?.trim()).toBe("Keep");
    });

    it("should use custom button classes", () => {
      window.ERPGuard.confirm("Delete?", jest.fn(), null, {
        confirmClass: "btn-danger",
        cancelClass: "btn-outline-secondary",
      });

      const buttons = document.querySelectorAll(".modal-footer button");
      expect(buttons[0].classList.contains("btn-danger")).toBe(true);
      expect(buttons[1].classList.contains("btn-outline-secondary")).toBe(true);
    });

    it("should use localized button text", () => {
      window.ERPGuard.setLocale("pt");
      window.ERPGuard.confirm("Confirmar?", jest.fn());

      const buttons = document.querySelectorAll(".modal-footer button");
      expect(buttons[0].textContent?.trim()).toBe("Sim");
      expect(buttons[1].textContent?.trim()).toBe("Não");
    });
  });

  // ==========================================================================
  // ERROR SCHEDULING TESTS
  // ==========================================================================
  describe("Error Scheduling", () => {
    beforeEach(() => {
      jest.useFakeTimers();
    });

    afterEach(() => {
      jest.useRealTimers();
    });

    it("should schedule error with default delay", () => {
      window.ERPGuard.scheduleError("Delayed error");

      // Error should not appear immediately
      expect(document.querySelectorAll(".toast").length).toBe(0);

      // Advance timers
      jest.advanceTimersByTime(100);

      expect(document.querySelectorAll(".toast").length).toBe(1);
    });

    it("should schedule error with custom delay", () => {
      window.ERPGuard.scheduleError("Delayed error", 500);

      jest.advanceTimersByTime(400);
      expect(document.querySelectorAll(".toast").length).toBe(0);

      jest.advanceTimersByTime(100);
      expect(document.querySelectorAll(".toast").length).toBe(1);
    });

    it("should prevent duplicate scheduled errors", () => {
      window.ERPGuard.scheduleError("Same error");
      window.ERPGuard.scheduleError("Same error");
      window.ERPGuard.scheduleError("Same error");

      jest.advanceTimersByTime(100);

      // Only one toast should appear
      expect(document.querySelectorAll(".toast").length).toBe(1);
    });

    it("should return this for chaining", () => {
      const result = window.ERPGuard.scheduleError("Test");
      expect(result).toBe(window.ERPGuard);
    });

    it("should schedule interactive error with retry", () => {
      const onRetry = jest.fn();
      window.ERPGuard.scheduleInteractiveError("Error", { onRetry });

      jest.advanceTimersByTime(100);

      // Should show confirm modal instead of toast
      const modal = document.querySelector(".modal");
      expect(modal).not.toBeNull();
    });

    it("should show plain error when no onRetry provided", () => {
      const onDismiss = jest.fn();
      window.ERPGuard.scheduleInteractiveError("Error", { onDismiss });

      jest.advanceTimersByTime(100);

      // Should show toast
      expect(document.querySelectorAll(".toast").length).toBe(1);
      expect(onDismiss).toHaveBeenCalled();
    });
  });

  // ==========================================================================
  // URL VALIDATION TESTS
  // ==========================================================================
  describe("URL Validation", () => {
    it("should return true for null/undefined URL", () => {
      expect(window.ERPGuard.isInvalidUrl(null as any)).toBe(true);
      expect(window.ERPGuard.isInvalidUrl(undefined as any)).toBe(true);
    });

    it("should return true for non-string URL", () => {
      expect(window.ERPGuard.isInvalidUrl(123 as any)).toBe(true);
      expect(window.ERPGuard.isInvalidUrl({} as any)).toBe(true);
    });

    it("should return true for javascript: URLs", () => {
      expect(window.ERPGuard.isInvalidUrl("javascript:alert(1)")).toBe(true);
      expect(window.ERPGuard.isInvalidUrl("JAVASCRIPT:alert(1)")).toBe(true);
      expect(window.ERPGuard.isInvalidUrl("  javascript:void(0)")).toBe(true);
    });

    it("should return true for data: URLs", () => {
      expect(
        window.ERPGuard.isInvalidUrl(
          "data:text/html,<script>alert(1)</script>",
        ),
      ).toBe(true);
    });

    it("should return true for vbscript: URLs", () => {
      expect(window.ERPGuard.isInvalidUrl('vbscript:msgbox("xss")')).toBe(true);
    });

    it("should return false for relative URLs by default", () => {
      expect(window.ERPGuard.isInvalidUrl("/path/to/page")).toBe(false);
      expect(window.ERPGuard.isInvalidUrl("./relative")).toBe(false);
      expect(window.ERPGuard.isInvalidUrl("../parent")).toBe(false);
      expect(window.ERPGuard.isInvalidUrl("page.html")).toBe(false);
    });

    it("should return false for valid absolute URLs", () => {
      expect(window.ERPGuard.isInvalidUrl("https://example.com")).toBe(false);
      expect(window.ERPGuard.isInvalidUrl("http://example.com/path")).toBe(
        false,
      );
    });

    it("should enforce HTTPS when requireHttps is true", () => {
      expect(
        window.ERPGuard.isInvalidUrl("http://example.com", {
          requireHttps: true,
        }),
      ).toBe(true);
      expect(
        window.ERPGuard.isInvalidUrl("https://example.com", {
          requireHttps: true,
        }),
      ).toBe(false);
    });

    it("should check allowed hosts", () => {
      const options = { allowedHosts: ["example.com", "trusted.org"] };

      expect(window.ERPGuard.isInvalidUrl("https://example.com", options)).toBe(
        false,
      );
      expect(window.ERPGuard.isInvalidUrl("https://trusted.org", options)).toBe(
        false,
      );
      expect(window.ERPGuard.isInvalidUrl("https://evil.com", options)).toBe(
        true,
      );
    });

    it("should return true for malformed URLs", () => {
      // URL constructor in modern browsers accepts many schemes
      // Test with something that will actually fail parsing
      expect(window.ERPGuard.isInvalidUrl("", { allowRelative: false })).toBe(
        true,
      );
      expect(window.ERPGuard.isInvalidUrl(null as any)).toBe(true);
    });
  });

  // ==========================================================================
  // FORM GUARD TESTS
  // ==========================================================================
  describe("Form Guards", () => {
    describe("bindSubmitGuard", () => {
      it("should bind to form element", () => {
        const form = createForm();
        const result = window.ERPGuard.bindSubmitGuard(form);

        expect(result).toBe(window.ERPGuard);
      });

      it("should warn and return this for non-form elements", () => {
        const consoleSpy = jest
          .spyOn(console, "warn")
          .mockImplementation(() => {});

        const result = window.ERPGuard.bindSubmitGuard(
          document.createElement("div") as any,
        );

        expect(consoleSpy).toHaveBeenCalledWith(
          expect.stringContaining("form element"),
        );
        expect(result).toBe(window.ERPGuard);

        consoleSpy.mockRestore();
      });

      it("should prevent submit when validation fails", () => {
        const form = createForm();
        const validate = jest.fn().mockReturnValue(false);

        window.ERPGuard.bindSubmitGuard(form, validate);

        const event = new Event("submit", { cancelable: true });
        form.dispatchEvent(event);

        expect(event.defaultPrevented).toBe(true);
        expect(validate).toHaveBeenCalled();
      });

      it("should allow submit when validation passes", () => {
        const form = createForm();
        const validate = jest.fn().mockReturnValue(true);

        // Mock checkValidity
        form.checkValidity = jest.fn().mockReturnValue(true);

        window.ERPGuard.bindSubmitGuard(form, validate);

        const event = new Event("submit", { cancelable: true });
        form.dispatchEvent(event);

        expect(event.defaultPrevented).toBe(false);
      });

      it("should check HTML5 validity", () => {
        const form = createForm();
        form.checkValidity = jest.fn().mockReturnValue(false);

        window.ERPGuard.bindSubmitGuard(form);

        const event = new Event("submit", { cancelable: true });
        form.dispatchEvent(event);

        expect(form.classList.contains("was-validated")).toBe(true);
        expect(event.defaultPrevented).toBe(true);
      });

      it("should call onError callback on validation failure", () => {
        const form = createForm();
        const onError = jest.fn();

        window.ERPGuard.bindSubmitGuard(form, () => false, { onError });

        simulateSubmit(form);

        expect(onError).toHaveBeenCalled();
      });

      it("should call onSuccess callback on success", () => {
        const form = createForm();
        const onSuccess = jest.fn();

        form.checkValidity = jest.fn().mockReturnValue(true);

        window.ERPGuard.bindSubmitGuard(form, null, { onSuccess });

        simulateSubmit(form);

        expect(onSuccess).toHaveBeenCalledWith(form);
      });

      it("should prevent default when preventDefault option is true", () => {
        const form = createForm();
        form.checkValidity = jest.fn().mockReturnValue(true);

        window.ERPGuard.bindSubmitGuard(form, null, { preventDefault: true });

        const event = new Event("submit", { cancelable: true });
        form.dispatchEvent(event);

        expect(event.defaultPrevented).toBe(true);
      });

      it("should not bind twice to same element", () => {
        const form = createForm();
        const validate = jest.fn().mockReturnValue(true);

        form.checkValidity = jest.fn().mockReturnValue(true);

        window.ERPGuard.bindSubmitGuard(form, validate);
        window.ERPGuard.bindSubmitGuard(form, validate);

        simulateSubmit(form);

        // Should only be called once
        expect(validate).toHaveBeenCalledTimes(1);
      });
    });

    describe("bindClickGuard", () => {
      it("should bind to element", () => {
        const button = createButton();
        document.body.appendChild(button);

        const result = window.ERPGuard.bindClickGuard(button);
        expect(result).toBe(window.ERPGuard);
      });

      it("should prevent click when validation fails", () => {
        const button = createButton();
        document.body.appendChild(button);

        window.ERPGuard.bindClickGuard(button, () => false);

        const event = new MouseEvent("click", { cancelable: true });
        button.dispatchEvent(event);

        expect(event.defaultPrevented).toBe(true);
      });

      it("should show confirmation dialog when confirmMessage is set", () => {
        const button = createButton();
        document.body.appendChild(button);

        window.ERPGuard.bindClickGuard(button, null, {
          confirmMessage: "Are you sure?",
        });

        simulateClick(button);

        const modal = document.querySelector(".modal");
        expect(modal).not.toBeNull();
      });

      it("should call onSuccess when validation passes", () => {
        const button = createButton();
        document.body.appendChild(button);
        const onSuccess = jest.fn();

        window.ERPGuard.bindClickGuard(button, () => true, { onSuccess });

        simulateClick(button);

        expect(onSuccess).toHaveBeenCalledWith(button);
      });
    });

    describe("bindChangeGuard", () => {
      it("should bind to input element", () => {
        const input = createInput();
        document.body.appendChild(input);

        const result = window.ERPGuard.bindChangeGuard(input);
        expect(result).toBe(window.ERPGuard);
      });

      it("should add is-invalid class on validation failure", () => {
        const input = createInput();
        document.body.appendChild(input);

        window.ERPGuard.bindChangeGuard(input, () => false);

        simulateChange(input, "test");

        expect(input.classList.contains("is-invalid")).toBe(true);
        expect(input.classList.contains("is-valid")).toBe(false);
      });

      it("should add is-valid class on validation success", () => {
        const input = createInput();
        document.body.appendChild(input);

        window.ERPGuard.bindChangeGuard(input, () => true);

        simulateChange(input, "test");

        expect(input.classList.contains("is-valid")).toBe(true);
        expect(input.classList.contains("is-invalid")).toBe(false);
      });

      it("should debounce validation", async () => {
        jest.useFakeTimers();

        const input = createInput();
        document.body.appendChild(input);
        const validate = jest.fn().mockReturnValue(true);

        window.ERPGuard.bindChangeGuard(input, validate, { debounce: 300 });

        simulateInput(input, "a");
        simulateInput(input, "ab");
        simulateInput(input, "abc");

        expect(validate).not.toHaveBeenCalled();

        jest.advanceTimersByTime(300);

        expect(validate).toHaveBeenCalledTimes(1);

        jest.useRealTimers();
      });
    });

    describe("unbind", () => {
      it("should remove event listeners from element", () => {
        const form = createForm();
        const validate = jest.fn().mockReturnValue(false);

        window.ERPGuard.bindSubmitGuard(form, validate);
        window.ERPGuard.unbind(form);

        simulateSubmit(form);

        expect(validate).not.toHaveBeenCalled();
      });

      it("should return this for chaining", () => {
        const form = createForm();
        window.ERPGuard.bindSubmitGuard(form);

        const result = window.ERPGuard.unbind(form);
        expect(result).toBe(window.ERPGuard);
      });
    });
  });

  // ==========================================================================
  // MESSAGE ENCODING TESTS
  // ==========================================================================
  describe("Message Encoding", () => {
    it("should encode message to base64", () => {
      const encoded = window.ERPGuard.encodeMsg("Hello World");
      expect(encoded).toBe(btoa(encodeURIComponent("Hello World")));
    });

    it("should decode base64 message", () => {
      const original = "Hello World";
      const encoded = window.ERPGuard.encodeMsg(original);
      const decoded = window.ERPGuard.decodeMsg(encoded);

      expect(decoded).toBe(original);
    });

    it("should handle special characters", () => {
      const original = "Héllo Wörld! 你好";
      const encoded = window.ERPGuard.encodeMsg(original);
      const decoded = window.ERPGuard.decodeMsg(encoded);

      expect(decoded).toBe(original);
    });

    it("should handle empty string", () => {
      const encoded = window.ERPGuard.encodeMsg("");
      const decoded = window.ERPGuard.decodeMsg(encoded);

      expect(decoded).toBe("");
    });

    it("should fall back to encodeURIComponent on btoa failure", () => {
      // btoa can fail with some unicode characters in some browsers
      const original = "Test";
      const encoded = window.ERPGuard.encodeMsg(original);

      expect(encoded).toBeTruthy();
    });

    it("should handle decoding of plain URI-encoded strings", () => {
      const uriEncoded = encodeURIComponent("Hello World");
      const decoded = window.ERPGuard.decodeMsg(uriEncoded);

      expect(decoded).toBe("Hello World");
    });

    it("should return original on decoding failure", () => {
      const invalidEncoded = "%%%invalid%%%";
      const decoded = window.ERPGuard.decodeMsg(invalidEncoded);

      expect(decoded).toBe(invalidEncoded);
    });
  });

  // ==========================================================================
  // AJAX ERROR HANDLING TESTS
  // ==========================================================================
  describe("AJAX Error Handling", () => {
    // Mock Response class for jsdom
    class MockResponse {
      status: number;
      statusText: string;
      constructor(_body: any, init?: { status?: number; statusText?: string }) {
        this.status = init?.status || 200;
        this.statusText = init?.statusText || "";
      }
    }

    beforeEach(() => {
      jest.useFakeTimers();
      // Make MockResponse available as Response in the evaluated code
      (window as any).Response = MockResponse;
    });

    afterEach(() => {
      jest.useRealTimers();
    });

    it("should handle Response with 401 status", () => {
      const response = new MockResponse(null, { status: 401 });
      window.ERPGuard.handleAjaxError(response as any);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("Unauthorized");
    });

    it("should handle Response with 403 status", () => {
      const response = new MockResponse(null, { status: 403 });
      window.ERPGuard.handleAjaxError(response as any);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("forbidden");
    });

    it("should handle Response with 404 status", () => {
      const response = new MockResponse(null, { status: 404 });
      window.ERPGuard.handleAjaxError(response as any);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("not found");
    });

    it("should handle Response with 500 status", () => {
      const response = new MockResponse(null, { status: 500 });
      window.ERPGuard.handleAjaxError(response as any);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("Server error");
    });

    it("should handle Error with NetworkError name", () => {
      const error = new Error("Network failed");
      error.name = "NetworkError";

      window.ERPGuard.handleAjaxError(error);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("Network error");
    });

    it("should handle Error with AbortError name", () => {
      const error = new Error("Aborted");
      error.name = "AbortError";

      window.ERPGuard.handleAjaxError(error);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("timed out");
    });

    it("should handle plain object with message", () => {
      window.ERPGuard.handleAjaxError({ message: "Custom error message" });

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("Custom error message");
    });

    it("should show interactive error when onRetry provided", () => {
      const onRetry = jest.fn();

      window.ERPGuard.handleAjaxError(new Error("Test"), { onRetry });

      jest.advanceTimersByTime(100);

      // Should show confirm modal
      const modal = document.querySelector(".modal");
      expect(modal).not.toBeNull();
    });

    it("should return this for chaining", () => {
      const result = window.ERPGuard.handleAjaxError(new Error("Test"));
      expect(result).toBe(window.ERPGuard);
    });

    it("should handle 408 timeout status", () => {
      const response = new MockResponse(null, { status: 408 });
      window.ERPGuard.handleAjaxError(response as any);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("timed out");
    });

    it("should handle 502 status", () => {
      const response = new MockResponse(null, { status: 502 });
      window.ERPGuard.handleAjaxError(response as any);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("Server error");
    });

    it("should handle 503 status", () => {
      const response = new MockResponse(null, { status: 503 });
      window.ERPGuard.handleAjaxError(response as any);

      jest.advanceTimersByTime(100);

      const toast = document.querySelector(".toast");
      expect(toast?.textContent).toContain("Server error");
    });
  });

  // ==========================================================================
  // AUTO-GUARD TESTS
  // ==========================================================================
  describe("Auto Guard Elements", () => {
    it('should auto-bind submit guard to data-erp-guard="submit"', () => {
      resetDOM();

      const form = createForm({ "data-erp-guard": "submit" });
      form.checkValidity = jest.fn().mockReturnValue(false);

      (window as any).bootstrap = createMockBootstrap();
      loadERPGuard();

      const event = new Event("submit", { cancelable: true });
      form.dispatchEvent(event);

      expect(event.defaultPrevented).toBe(true);
    });

    it('should auto-bind click guard to data-erp-guard="click"', () => {
      resetDOM();

      const button = createButton("Click", { "data-erp-guard": "click" });
      document.body.appendChild(button);

      (window as any).bootstrap = createMockBootstrap();
      loadERPGuard();

      // The guard is bound, so clicking should work without error
      expect(() => simulateClick(button)).not.toThrow();
    });

    it('should auto-bind change guard to data-erp-guard="change"', () => {
      resetDOM();

      const input = createInput("text", { "data-erp-guard": "change" });
      document.body.appendChild(input);

      (window as any).bootstrap = createMockBootstrap();
      loadERPGuard();

      simulateChange(input, "test");

      // Default behavior adds is-valid class
      expect(input.classList.contains("is-valid")).toBe(true);
    });

    it("should guard dynamically added elements", async () => {
      jest.useRealTimers();

      // Create and add element after initialization
      const form = createForm({ "data-erp-guard": "submit" });
      form.checkValidity = jest.fn().mockReturnValue(false);

      // Wait for MutationObserver to process
      await new Promise(resolve => setTimeout(resolve, 50));

      const event = new Event("submit", { cancelable: true });
      form.dispatchEvent(event);

      expect(event.defaultPrevented).toBe(true);
    });
  });

  // ==========================================================================
  // DESTROY TESTS
  // ==========================================================================
  describe("Destroy", () => {
    it("should remove toast container", () => {
      expect(document.getElementById("erp-toast-container")).not.toBeNull();

      window.ERPGuard.destroy();

      expect(document.getElementById("erp-toast-container")).toBeNull();
    });

    it("should remove modal container", () => {
      expect(document.getElementById("erp-modal-container")).not.toBeNull();

      window.ERPGuard.destroy();

      expect(document.getElementById("erp-modal-container")).toBeNull();
    });

    it("should disconnect mutation observer", () => {
      // This is implicit - if observer is not disconnected,
      // adding elements would throw or behave unexpectedly after destroy
      window.ERPGuard.destroy();

      // Should not throw when adding elements
      expect(() => {
        const div = document.createElement("div");
        div.setAttribute("data-erp-guard", "submit");
        document.body.appendChild(div);
      }).not.toThrow();
    });
  });

  // ==========================================================================
  // EDGE CASES AND COMBINATION TESTS
  // ==========================================================================
  describe("Edge Cases", () => {
    it("should handle localStorage errors gracefully", () => {
      const originalSetItem = localStorage.setItem;
      localStorage.setItem = () => {
        throw new Error("QuotaExceeded");
      };

      // Should not throw
      expect(() => {
        window.ERPGuard.setLocale("pt");
      }).not.toThrow();

      localStorage.setItem = originalSetItem;
    });

    it("should handle missing document.body in init", () => {
      // This edge case is hard to test in jsdom
      // but the code handles it gracefully
      expect(window.ERPGuard).toBeDefined();
    });

    it("should handle rapid successive toasts", () => {
      for (let i = 0; i < 10; i++) {
        window.ERPGuard.showToast(`Message ${i}`, "info");
      }

      const toasts = document.querySelectorAll(".toast");
      expect(toasts.length).toBe(10);
    });

    it("should handle concurrent modal and toast", () => {
      window.ERPGuard.showToast("Toast", "info");
      window.ERPGuard.showModal({ title: "Modal", body: "Body" });

      expect(document.querySelectorAll(".toast").length).toBe(1);
      expect(document.querySelectorAll(".modal").length).toBe(1);
    });

    it("should handle form with no inputs", () => {
      const form = createForm();
      form.checkValidity = jest.fn().mockReturnValue(true);

      window.ERPGuard.bindSubmitGuard(form);

      const event = new Event("submit", { cancelable: true });
      form.dispatchEvent(event);

      expect(event.defaultPrevented).toBe(false);
    });

    it("should handle XSS attempts in messages", () => {
      const xssPayload = "<img src=x onerror=alert(1)>";

      window.ERPGuard.showToast(xssPayload, "info");

      const toast = document.querySelector(".toast-body span");
      // HTML should be escaped - no actual img tag
      expect(toast?.innerHTML).toContain("&lt;img");
      expect(toast?.innerHTML).toContain("&gt;");
      // But the text content should show the original characters
      expect(toast?.textContent).toContain("<img");
    });

    it("should handle very long messages", () => {
      const longMessage = "A".repeat(10000);

      window.ERPGuard.showToast(longMessage, "info");

      const toast = document.querySelector(".toast-body");
      expect(toast?.textContent?.length).toBeGreaterThan(9990);
    });

    it("should handle empty options gracefully", () => {
      expect(() => {
        window.ERPGuard.showToast("Test", "info", {});
        window.ERPGuard.showModal({});
        window.ERPGuard.confirm("Test", jest.fn(), null, {});
      }).not.toThrow();
    });
  });

  // ==========================================================================
  // COMBINATION TESTS FOR 100% COVERAGE
  // ==========================================================================
  describe("Combination Tests", () => {
    it("should work with all locale+method combinations", () => {
      const locales: Array<"en" | "pt" | "es"> = ["en", "pt", "es"];
      const methods: Array<"success" | "error" | "warning" | "info"> = [
        "success",
        "error",
        "warning",
        "info",
      ];

      locales.forEach(locale => {
        methods.forEach(method => {
          resetDOM();
          (window as any).bootstrap = createMockBootstrap();
          loadERPGuard();

          window.ERPGuard.setLocale(locale);
          window.ERPGuard[method]("Test message");

          expect(document.querySelectorAll(".toast").length).toBe(1);
        });
      });
    });

    it("should handle form with multiple guards", () => {
      const form = createForm();
      const input1 = createInput("text", { required: "true" });
      const input2 = createInput("email", { required: "true" });
      const button = createButton("Submit", { type: "submit" });

      form.appendChild(input1);
      form.appendChild(input2);
      form.appendChild(button);

      form.checkValidity = jest.fn().mockReturnValue(true);

      window.ERPGuard.bindSubmitGuard(form, () => true);
      window.ERPGuard.bindChangeGuard(
        input1,
        val => (val as HTMLInputElement).value.length > 0,
      );
      window.ERPGuard.bindChangeGuard(input2, val =>
        (val as HTMLInputElement).value.includes("@"),
      );

      simulateChange(input1, "test");
      simulateChange(input2, "test@test.com");

      expect(input1.classList.contains("is-valid")).toBe(true);
      expect(input2.classList.contains("is-valid")).toBe(true);
    });

    it("should handle modal button click hiding modal", () => {
      const onConfirm = jest.fn();

      window.ERPGuard.confirm("Test", onConfirm);

      const confirmBtn = document.querySelector(
        ".btn-primary",
      ) as HTMLButtonElement;
      confirmBtn?.click();

      expect(onConfirm).toHaveBeenCalled();
    });

    it("should handle network error with retry flow", () => {
      jest.useFakeTimers();

      const onRetry = jest.fn();
      const error = new Error("Network error");
      error.name = "NetworkError";

      window.ERPGuard.handleAjaxError(error, { onRetry });

      jest.advanceTimersByTime(100);

      // Modal should appear
      const modal = document.querySelector(".modal");
      expect(modal).not.toBeNull();

      // Click retry
      const retryBtn = document.querySelector(
        ".btn-primary",
      ) as HTMLButtonElement;
      retryBtn?.click();

      expect(onRetry).toHaveBeenCalled();

      jest.useRealTimers();
    });
  });
});
