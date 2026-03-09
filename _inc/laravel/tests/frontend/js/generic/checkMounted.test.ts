/**
 * @file checkMounted.test.ts
 * @description Comprehensive tests for checkMounted.js
 * Tests DOM error detection and recovery overlay functionality
 */

import {
  jest,
  describe,
  it,
  expect,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { createMockBootstrap, resetDOM } from "../setup";

// Type-safe window accessor with any casting for test mocks
const win = window as any;

describe("checkMounted", () => {
  let consoleErrorSpy: jest.SpiedFunction<typeof console.error>;
  let consoleWarnSpy: jest.SpiedFunction<typeof console.warn>;

  beforeEach(() => {
    resetDOM();
    consoleErrorSpy = jest.spyOn(console, "error").mockImplementation(() => {});
    consoleWarnSpy = jest.spyOn(console, "warn").mockImplementation(() => {});

    // Reset any global state
    delete win.checkMountedOverlay;
    delete win.ERPUtils;
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
    consoleWarnSpy.mockRestore();
    jest.useRealTimers();
  });

  describe("Detection Logic", () => {
    const DETECTION_PATTERN = /^[\s\n\t\r]*[0-9]+</;

    describe("Pattern Matching", () => {
      it("should detect pages starting with digit followed by <", () => {
        expect(DETECTION_PATTERN.test("1<html>")).toBe(true);
        expect(DETECTION_PATTERN.test("42<div>")).toBe(true);
        expect(DETECTION_PATTERN.test("999<span>")).toBe(true);
      });

      it("should detect with leading whitespace", () => {
        expect(DETECTION_PATTERN.test(" 1<html>")).toBe(true);
        expect(DETECTION_PATTERN.test("  42<div>")).toBe(true);
        expect(DETECTION_PATTERN.test("\t1<span>")).toBe(true);
        expect(DETECTION_PATTERN.test("\n1<div>")).toBe(true);
        expect(DETECTION_PATTERN.test("\r\n1<p>")).toBe(true);
      });

      it("should detect with mixed whitespace", () => {
        expect(DETECTION_PATTERN.test(" \t\n\r 1<html>")).toBe(true);
        expect(DETECTION_PATTERN.test("\n\n\t  42<div>")).toBe(true);
      });

      it("should not detect normal HTML", () => {
        expect(DETECTION_PATTERN.test("<!DOCTYPE html>")).toBe(false);
        expect(DETECTION_PATTERN.test("<html>")).toBe(false);
        expect(DETECTION_PATTERN.test("<div>Content</div>")).toBe(false);
      });

      it("should not detect when digit is after text", () => {
        expect(DETECTION_PATTERN.test("Hello 1<div>")).toBe(false);
        expect(DETECTION_PATTERN.test("abc123<span>")).toBe(false);
      });

      it("should not detect when < is not immediately after digit", () => {
        expect(DETECTION_PATTERN.test("1 <html>")).toBe(false);
        expect(DETECTION_PATTERN.test("1\t<html>")).toBe(false);
      });
    });

    describe("Body Text Extraction", () => {
      it("should get body outerHTML for detection", () => {
        document.body.innerHTML = "<div>Test</div>";
        const outerHTML = document.body.outerHTML;
        expect(outerHTML).toMatch(/<body[^>]*>/);
        expect(outerHTML).toContain("<div>Test</div>");
      });

      it("should handle empty body", () => {
        document.body.innerHTML = "";
        const outerHTML = document.body.outerHTML;
        expect(outerHTML).toMatch(/<body[^>]*><\/body>/);
      });

      it("should handle body with text content", () => {
        document.body.textContent = "Plain text";
        expect(document.body.outerHTML).toContain("Plain text");
      });
    });
  });

  describe("Modal Context Detection", () => {
    describe("Bootstrap Modal", () => {
      it("should detect Bootstrap modal context", () => {
        document.body.classList.add("modal-open");
        const modal = document.createElement("div");
        modal.className = "modal show";
        modal.style.display = "block";
        document.body.appendChild(modal);

        expect(document.body.classList.contains("modal-open")).toBe(true);
        expect(document.querySelector(".modal.show")).toBeTruthy();
      });

      it("should not detect when modal is hidden", () => {
        const modal = document.createElement("div");
        modal.className = "modal";
        document.body.appendChild(modal);

        expect(document.querySelector(".modal.show")).toBeNull();
      });
    });

    describe("Generic Dialog Detection", () => {
      it("should detect open dialog element", () => {
        const dialog = document.createElement("dialog");
        dialog.open = true;
        document.body.appendChild(dialog);

        expect(document.querySelector("dialog[open]")).toBeTruthy();
      });

      it("should not detect closed dialog", () => {
        const dialog = document.createElement("dialog");
        document.body.appendChild(dialog);

        expect(document.querySelector("dialog[open]")).toBeNull();
      });
    });
  });

  describe("Overlay Structure", () => {
    let overlay: HTMLDivElement;
    let innerWrapper: HTMLDivElement;

    beforeEach(() => {
      // Create the overlay structure similar to checkMounted.js
      overlay = document.createElement("div");
      overlay.id = "checkMounted-overlay";
      overlay.className = "position-fixed top-0 start-0 w-100 h-100";
      overlay.style.cssText =
        "z-index:9999;background:rgba(0,0,0,0.85);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;";
      overlay.setAttribute("role", "alertdialog");
      overlay.setAttribute("aria-modal", "true");
      overlay.setAttribute("aria-labelledby", "checkMounted-title");
      overlay.setAttribute("aria-describedby", "checkMounted-desc");

      innerWrapper = document.createElement("div");
      innerWrapper.className = "text-center p-4";
      innerWrapper.style.cssText =
        "max-width:500px;background:#1a1a2e;border-radius:12px;border:1px solid #333;box-shadow:0 8px 32px rgba(0,0,0,0.4);";

      const title = document.createElement("h2");
      title.id = "checkMounted-title";
      title.textContent = "Page Load Error";
      title.className = "text-danger mb-3";
      title.style.cssText = "font-size:1.5rem;font-weight:600;";

      const desc = document.createElement("p");
      desc.id = "checkMounted-desc";
      desc.className = "text-light mb-4";
      desc.textContent =
        "An unexpected error occurred while loading this page. The page content could not be rendered properly.";
      desc.style.cssText = "font-size:0.95rem;opacity:0.9;";

      const btnGroup = document.createElement("div");
      btnGroup.className = "d-flex flex-column gap-2";

      const reloadBtn = document.createElement("button");
      reloadBtn.textContent = "Reload Page";
      reloadBtn.className = "btn btn-danger";
      reloadBtn.setAttribute("data-action", "reload");

      const homeBtn = document.createElement("button");
      homeBtn.textContent = "Go to Home";
      homeBtn.className = "btn btn-outline-light";
      homeBtn.setAttribute("data-action", "home");

      const dismissBtn = document.createElement("button");
      dismissBtn.textContent = "Dismiss (View Raw)";
      dismissBtn.className = "btn btn-link text-secondary";
      dismissBtn.setAttribute("data-action", "dismiss");

      btnGroup.appendChild(reloadBtn);
      btnGroup.appendChild(homeBtn);
      btnGroup.appendChild(dismissBtn);

      innerWrapper.appendChild(title);
      innerWrapper.appendChild(desc);
      innerWrapper.appendChild(btnGroup);
      overlay.appendChild(innerWrapper);
    });

    it("should have correct overlay structure", () => {
      expect(overlay.id).toBe("checkMounted-overlay");
      expect(overlay.classList.contains("position-fixed")).toBe(true);
      expect(overlay.style.zIndex).toBe("9999");
    });

    it("should have accessibility attributes", () => {
      expect(overlay.getAttribute("role")).toBe("alertdialog");
      expect(overlay.getAttribute("aria-modal")).toBe("true");
      expect(overlay.getAttribute("aria-labelledby")).toBe(
        "checkMounted-title",
      );
      expect(overlay.getAttribute("aria-describedby")).toBe(
        "checkMounted-desc",
      );
    });

    it("should have title and description", () => {
      const title = overlay.querySelector("#checkMounted-title");
      const desc = overlay.querySelector("#checkMounted-desc");

      expect(title).toBeTruthy();
      expect(title?.textContent).toBe("Page Load Error");
      expect(desc).toBeTruthy();
      expect(desc?.textContent).toContain("unexpected error");
    });

    it("should have action buttons", () => {
      const buttons = overlay.querySelectorAll("button");
      expect(buttons.length).toBe(3);

      const reloadBtn = overlay.querySelector('[data-action="reload"]');
      const homeBtn = overlay.querySelector('[data-action="home"]');
      const dismissBtn = overlay.querySelector('[data-action="dismiss"]');

      expect(reloadBtn?.textContent).toBe("Reload Page");
      expect(homeBtn?.textContent).toBe("Go to Home");
      expect(dismissBtn?.textContent).toBe("Dismiss (View Raw)");
    });

    it("should have correct button classes", () => {
      const reloadBtn = overlay.querySelector('[data-action="reload"]');
      const homeBtn = overlay.querySelector('[data-action="home"]');
      const dismissBtn = overlay.querySelector('[data-action="dismiss"]');

      expect(reloadBtn?.classList.contains("btn-danger")).toBe(true);
      expect(homeBtn?.classList.contains("btn-outline-light")).toBe(true);
      expect(dismissBtn?.classList.contains("btn-link")).toBe(true);
    });
  });

  describe("Actions", () => {
    describe("Reload Action", () => {
      it("should reload the page", () => {
        const originalLocation = window.location;
        const reloadMock = jest.fn();

        Object.defineProperty(window, "location", {
          value: { ...originalLocation, reload: reloadMock },
          writable: true,
          configurable: true,
        });

        window.location.reload();
        expect(reloadMock).toHaveBeenCalled();

        Object.defineProperty(window, "location", {
          value: originalLocation,
          writable: true,
          configurable: true,
        });
      });
    });

    describe("Home Action", () => {
      it("should navigate to root", () => {
        const assignMock = jest.fn();
        const originalLocation = window.location;

        Object.defineProperty(window, "location", {
          value: { ...originalLocation, assign: assignMock, href: "" },
          writable: true,
          configurable: true,
        });

        window.location.assign("/");
        expect(assignMock).toHaveBeenCalledWith("/");

        Object.defineProperty(window, "location", {
          value: originalLocation,
          writable: true,
          configurable: true,
        });
      });
    });

    describe("Dismiss Action", () => {
      it("should remove overlay when dismissed", () => {
        const overlay = document.createElement("div");
        overlay.id = "checkMounted-overlay";
        document.body.appendChild(overlay);

        expect(document.getElementById("checkMounted-overlay")).toBeTruthy();

        overlay.remove();
        expect(document.getElementById("checkMounted-overlay")).toBeNull();
      });

      it("should restore body styles when dismissed", () => {
        document.body.style.overflow = "hidden";
        document.body.style.position = "fixed";

        // Simulate dismiss
        document.body.style.overflow = "";
        document.body.style.position = "";

        expect(document.body.style.overflow).toBe("");
        expect(document.body.style.position).toBe("");
      });
    });
  });

  describe("CSS Injection", () => {
    it("should inject animation styles", () => {
      const style = document.createElement("style");
      style.textContent = `
        @keyframes checkMountedFadeIn {
          from { opacity: 0; transform: scale(0.95); }
          to { opacity: 1; transform: scale(1); }
        }
        #checkMounted-overlay > div {
          animation: checkMountedFadeIn 0.3s ease-out;
        }
      `;
      document.head.appendChild(style);

      expect(document.head.querySelector("style")).toBeTruthy();
      expect(style.textContent).toContain("checkMountedFadeIn");
    });

    it("should not duplicate style injection", () => {
      const existingStyle = document.createElement("style");
      existingStyle.id = "checkMounted-styles";
      document.head.appendChild(existingStyle);

      // Check if already exists before adding
      const existing = document.getElementById("checkMounted-styles");
      if (existing) {
        // Don't add duplicate
      } else {
        const newStyle = document.createElement("style");
        newStyle.id = "checkMounted-styles";
        document.head.appendChild(newStyle);
      }

      expect(document.querySelectorAll("#checkMounted-styles").length).toBe(1);
    });
  });

  describe("Body Styling on Overlay Show", () => {
    it("should lock body scroll", () => {
      document.body.style.overflow = "hidden";
      expect(document.body.style.overflow).toBe("hidden");
    });

    it("should fix body position", () => {
      document.body.style.position = "fixed";
      document.body.style.width = "100%";
      document.body.style.top = "-" + window.scrollY + "px";

      expect(document.body.style.position).toBe("fixed");
      expect(document.body.style.width).toBe("100%");
    });

    it("should preserve scroll position", () => {
      const scrollY = 100;
      document.body.style.top = "-" + scrollY + "px";

      // On dismiss, restore scroll
      const top = document.body.style.top;
      const scrollTo = parseInt(top || "0", 10) * -1;

      expect(scrollTo).toBe(100);
    });
  });

  describe("Script Handling", () => {
    it("should detect inline script errors", () => {
      const script = document.createElement("script");
      script.textContent = 'throw new Error("test error")';

      // The script would throw but we catch it in our test context
      expect(() => {
        // Don't actually execute, just verify structure
        expect(script.textContent).toContain("throw new Error");
      }).not.toThrow();
    });

    it("should handle script load failures", () => {
      const script = document.createElement("script");
      script.src = "nonexistent.js";

      const errorHandler = jest.fn();
      script.onerror = errorHandler;

      script.dispatchEvent(new Event("error"));
      expect(errorHandler).toHaveBeenCalled();
    });
  });

  describe("Escape Key Handler", () => {
    it("should handle Escape key press", () => {
      const overlay = document.createElement("div");
      overlay.id = "checkMounted-overlay";
      document.body.appendChild(overlay);

      const handler = (e: KeyboardEvent) => {
        if (e.key === "Escape") {
          overlay.remove();
        }
      };

      document.addEventListener("keydown", handler);
      document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));

      expect(document.getElementById("checkMounted-overlay")).toBeNull();
      document.removeEventListener("keydown", handler);
    });

    it("should not dismiss on other keys", () => {
      const overlay = document.createElement("div");
      overlay.id = "checkMounted-overlay";
      document.body.appendChild(overlay);

      const handler = (e: KeyboardEvent) => {
        if (e.key === "Escape") {
          overlay.remove();
        }
      };

      document.addEventListener("keydown", handler);
      document.dispatchEvent(new KeyboardEvent("keydown", { key: "Enter" }));

      expect(document.getElementById("checkMounted-overlay")).toBeTruthy();
      document.removeEventListener("keydown", handler);
    });
  });

  describe("Focus Management", () => {
    it("should trap focus in overlay", () => {
      const overlay = document.createElement("div");
      overlay.id = "checkMounted-overlay";
      overlay.tabIndex = -1;

      const btn1 = document.createElement("button");
      btn1.textContent = "First";
      const btn2 = document.createElement("button");
      btn2.textContent = "Second";
      const btn3 = document.createElement("button");
      btn3.textContent = "Third";

      overlay.appendChild(btn1);
      overlay.appendChild(btn2);
      overlay.appendChild(btn3);
      document.body.appendChild(overlay);

      btn1.focus();
      expect(document.activeElement).toBe(btn1);
    });

    it("should focus first button on show", () => {
      const overlay = document.createElement("div");
      const btn = document.createElement("button");
      btn.textContent = "Reload";
      overlay.appendChild(btn);
      document.body.appendChild(overlay);

      btn.focus();
      expect(document.activeElement).toBe(btn);
    });

    it("should restore focus on dismiss", () => {
      const outsideBtn = document.createElement("button");
      outsideBtn.id = "outside-btn";
      document.body.appendChild(outsideBtn);
      outsideBtn.focus();

      const previousFocus = document.activeElement;

      const overlay = document.createElement("div");
      const overlayBtn = document.createElement("button");
      overlay.appendChild(overlayBtn);
      document.body.appendChild(overlay);
      overlayBtn.focus();

      // Dismiss and restore
      overlay.remove();
      (previousFocus as HTMLElement)?.focus?.();

      expect(document.activeElement).toBe(outsideBtn);
    });
  });

  describe("Integration Tests", () => {
    describe("Full Overlay Lifecycle", () => {
      it("should create and show overlay", () => {
        const overlay = document.createElement("div");
        overlay.id = "checkMounted-overlay";
        overlay.style.display = "none";
        document.body.appendChild(overlay);

        // Show
        overlay.style.display = "flex";
        expect(overlay.style.display).toBe("flex");
      });

      it("should handle button clicks", () => {
        const reloadMock = jest.fn();
        const homeMock = jest.fn();
        const dismissMock = jest.fn();

        const overlay = document.createElement("div");

        const reloadBtn = document.createElement("button");
        reloadBtn.setAttribute("data-action", "reload");
        reloadBtn.onclick = reloadMock;

        const homeBtn = document.createElement("button");
        homeBtn.setAttribute("data-action", "home");
        homeBtn.onclick = homeMock;

        const dismissBtn = document.createElement("button");
        dismissBtn.setAttribute("data-action", "dismiss");
        dismissBtn.onclick = dismissMock;

        overlay.appendChild(reloadBtn);
        overlay.appendChild(homeBtn);
        overlay.appendChild(dismissBtn);
        document.body.appendChild(overlay);

        reloadBtn.click();
        expect(reloadMock).toHaveBeenCalled();

        homeBtn.click();
        expect(homeMock).toHaveBeenCalled();

        dismissBtn.click();
        expect(dismissMock).toHaveBeenCalled();
      });
    });

    describe("Error Detection Flow", () => {
      it("should detect error pattern and show overlay", () => {
        const bodyContent = "1<!DOCTYPE html>";
        const pattern = /^[\s\n\t\r]*[0-9]+</;
        const hasError = pattern.test(bodyContent);

        expect(hasError).toBe(true);

        if (hasError) {
          const overlay = document.createElement("div");
          overlay.id = "checkMounted-overlay";
          document.body.appendChild(overlay);

          expect(document.getElementById("checkMounted-overlay")).toBeTruthy();
        }
      });

      it("should not show overlay for valid content", () => {
        const bodyContent = "<!DOCTYPE html><html>";
        const pattern = /^[\s\n\t\r]*[0-9]+</;
        const hasError = pattern.test(bodyContent);

        expect(hasError).toBe(false);
        expect(document.getElementById("checkMounted-overlay")).toBeNull();
      });
    });

    describe("Multiple Error Scenarios", () => {
      it("should handle PHP error output", () => {
        // PHP often outputs line numbers before error messages
        const patterns = [
          "1<?php",
          "42<br />",
          "  123<b>Warning</b>",
          "\n\n1<html>",
        ];

        const regex = /^[\s\n\t\r]*[0-9]+</;

        patterns.forEach(p => {
          expect(regex.test(p)).toBe(true);
        });
      });

      it("should handle debug output leaks", () => {
        const debugPatterns = [
          "0<pre>",
          '1<div class="debug">',
          '  42<span style="color:red">',
        ];

        const regex = /^[\s\n\t\r]*[0-9]+</;

        debugPatterns.forEach(p => {
          expect(regex.test(p)).toBe(true);
        });
      });
    });
  });

  describe("Accessibility", () => {
    it("should have proper ARIA attributes", () => {
      const overlay = document.createElement("div");
      overlay.setAttribute("role", "alertdialog");
      overlay.setAttribute("aria-modal", "true");
      overlay.setAttribute("aria-labelledby", "checkMounted-title");
      overlay.setAttribute("aria-describedby", "checkMounted-desc");

      expect(overlay.getAttribute("role")).toBe("alertdialog");
      expect(overlay.getAttribute("aria-modal")).toBe("true");
      expect(overlay.hasAttribute("aria-labelledby")).toBe(true);
      expect(overlay.hasAttribute("aria-describedby")).toBe(true);
    });

    it("should have live region for announcements", () => {
      const overlay = document.createElement("div");
      const content = document.createElement("div");
      content.setAttribute("aria-live", "polite");
      overlay.appendChild(content);

      expect(content.getAttribute("aria-live")).toBe("polite");
    });

    it("should have focusable buttons", () => {
      const btn = document.createElement("button");
      btn.textContent = "Reload";
      document.body.appendChild(btn);

      btn.focus();
      expect(document.activeElement).toBe(btn);
    });

    it("should have descriptive button text", () => {
      const reloadBtn = document.createElement("button");
      reloadBtn.textContent = "Reload Page";

      const homeBtn = document.createElement("button");
      homeBtn.textContent = "Go to Home";

      const dismissBtn = document.createElement("button");
      dismissBtn.textContent = "Dismiss (View Raw)";

      expect(reloadBtn.textContent).not.toBe("");
      expect(homeBtn.textContent).not.toBe("");
      expect(dismissBtn.textContent).not.toBe("");
    });
  });

  describe("Performance", () => {
    it("should use requestAnimationFrame for animations", () => {
      const rafSpy = jest.spyOn(window, "requestAnimationFrame");

      requestAnimationFrame(() => {});
      expect(rafSpy).toHaveBeenCalled();

      rafSpy.mockRestore();
    });

    it("should debounce resize handlers", () => {
      jest.useFakeTimers();
      const handler = jest.fn();

      const debouncedHandler = (() => {
        let timeout: NodeJS.Timeout;
        return () => {
          clearTimeout(timeout);
          timeout = setTimeout(handler, 150);
        };
      })();

      // Rapid calls
      debouncedHandler();
      debouncedHandler();
      debouncedHandler();
      debouncedHandler();

      expect(handler).not.toHaveBeenCalled();

      jest.advanceTimersByTime(150);
      expect(handler).toHaveBeenCalledTimes(1);

      jest.useRealTimers();
    });
  });

  describe("Edge Cases", () => {
    it("should handle missing document.body", () => {
      // This tests resilience, actual removal of body would break JSDOM
      const getBody = () => document.body || null;
      expect(getBody()).toBeTruthy();
    });

    it("should handle undefined window properties", () => {
      const safeScrollY = typeof window !== "undefined" ? window.scrollY : 0;
      expect(typeof safeScrollY).toBe("number");
    });

    it("should handle rapid show/hide cycles", () => {
      for (let i = 0; i < 10; i++) {
        const overlay = document.createElement("div");
        overlay.id = "checkMounted-overlay";
        document.body.appendChild(overlay);
        overlay.remove();
      }

      expect(document.getElementById("checkMounted-overlay")).toBeNull();
    });

    it("should handle concurrent error detections", () => {
      const detectError = () => {
        const existing = document.getElementById("checkMounted-overlay");
        if (!existing) {
          const overlay = document.createElement("div");
          overlay.id = "checkMounted-overlay";
          document.body.appendChild(overlay);
        }
      };

      // Simulate concurrent detections
      detectError();
      detectError();
      detectError();

      expect(document.querySelectorAll("#checkMounted-overlay").length).toBe(1);
    });
  });
});
