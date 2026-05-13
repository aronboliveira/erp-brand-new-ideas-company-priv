/**
 * Tests for show_toastr(type, message)
 *
 * show_toastr() finds the #liveToast element, shows a Bootstrap 5 Toast,
 * applies a colour class, and writes the message into .toast-body.
 */

import {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} from "../helpers/setup";

let toastShowSpy;

beforeEach(() => {
  // Reset the DOM completely between tests
  document.body.innerHTML = "";
  document.head.innerHTML = "";

  buildJQueryEnv();
  buildDomSkeleton();

  toastShowSpy = jest.fn();

  // Provide a bootstrap.Toast mock that tracks .show() calls
  (globalThis as any).bootstrap = {
    Toast: class Toast {
      constructor() {}
      show() {
        toastShowSpy();
      }
    },
  };

  loadCustomJs();
});

describe("show_toastr", () => {
  test("is defined as a global function", () => {
    expect(typeof (globalThis as any).show_toastr).toBe("function");
  });

  test("sets bg-primary class for success type", () => {
    (globalThis as any).show_toastr("success", "Saved!");
    const toast = document.getElementById("liveToast");
    expect(toast.classList.contains("bg-primary")).toBe(true);
    expect(toast.classList.contains("bg-danger")).toBe(false);
  });

  test("sets bg-danger class for error type", () => {
    (globalThis as any).show_toastr("error", "Something failed");
    const toast = document.getElementById("liveToast");
    expect(toast.classList.contains("bg-danger")).toBe(true);
    expect(toast.classList.contains("bg-primary")).toBe(false);
  });

  test("writes message into .toast-body (textContent — no HTML injection)", () => {
    // custom.ts uses textContent (safe), which strips HTML tags
    (globalThis as any).show_toastr("success", "<b>Hello</b>");
    const body = document.querySelector("#liveToast .toast-body");
    expect(body.textContent).toBe("<b>Hello</b>");
  });

  test("works with an empty message", () => {
    (globalThis as any).show_toastr("success", "");
    const body = document.querySelector("#liveToast .toast-body");
    expect(body.textContent).toBe("");
  });

  test("works with HTML-escaped content", () => {
    // textContent decodes HTML entities before storing, then the browser
    // re-encodes them in .innerHTML — the textContent path preserves literal text
    (globalThis as any).show_toastr("error", "&lt;script&gt;");
    const body = document.querySelector("#liveToast .toast-body");
    expect(body.textContent).toBe("&lt;script&gt;");
  });

  test("colour classes are reset between calls", () => {
    // custom.ts calls classList.remove() before adding — intentional reset
    (globalThis as any).show_toastr("success", "first");
    expect(document.getElementById("liveToast").classList.contains("bg-primary")).toBe(true);
    (globalThis as any).show_toastr("error", "second");
    const toast = document.getElementById("liveToast");
    expect(toast.classList.contains("bg-primary")).toBe(false);
    expect(toast.classList.contains("bg-danger")).toBe(true);
  });
});
