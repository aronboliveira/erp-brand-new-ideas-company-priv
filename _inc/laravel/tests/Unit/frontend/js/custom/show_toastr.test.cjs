/**
 * Tests for show_toastr(type, message)
 *
 * show_toastr() finds the #liveToast element, shows a Bootstrap 5 Toast,
 * applies a colour class, and writes the message into .toast-body.
 */

const {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} = require("../helpers/setup.cjs");

let toastShowSpy;

beforeEach(() => {
  // Reset the DOM completely between tests
  document.body.innerHTML = "";
  document.head.innerHTML = "";

  buildJQueryEnv();
  buildDomSkeleton();

  toastShowSpy = jest.fn();

  // Provide a bootstrap.Toast mock that tracks .show() calls
  global.bootstrap = {
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
    expect(typeof global.show_toastr).toBe("function");
  });

  test("sets bg-primary class for success type", () => {
    global.show_toastr("success", "Saved!");
    const toast = document.getElementById("liveToast");
    expect(toast.classList.contains("bg-primary")).toBe(true);
    expect(toast.classList.contains("bg-danger")).toBe(false);
  });

  test("sets bg-danger class for error type", () => {
    global.show_toastr("error", "Something failed");
    const toast = document.getElementById("liveToast");
    expect(toast.classList.contains("bg-danger")).toBe(true);
    expect(toast.classList.contains("bg-primary")).toBe(false);
  });

  test("writes message into .toast-body", () => {
    global.show_toastr("success", "<b>Hello</b>");
    const body = document.querySelector("#liveToast .toast-body");
    expect(body.innerHTML).toBe("<b>Hello</b>");
  });

  test("works with an empty message", () => {
    global.show_toastr("success", "");
    const body = document.querySelector("#liveToast .toast-body");
    expect(body.innerHTML).toBe("");
  });

  test("works with HTML-escaped content", () => {
    global.show_toastr("error", "&lt;script&gt;");
    const body = document.querySelector("#liveToast .toast-body");
    expect(body.innerHTML).toBe("&lt;script&gt;");
  });

  test("each call accumulates colour classes (no reset)", () => {
    // This documents current behaviour – the toast element does NOT remove
    // old colour classes when called again with a different type.
    global.show_toastr("success", "first");
    global.show_toastr("error", "second");
    const toast = document.getElementById("liveToast");
    expect(toast.classList.contains("bg-primary")).toBe(true);
    expect(toast.classList.contains("bg-danger")).toBe(true);
  });
});
