/**
 * Tests for jQuery event handlers registered in custom.js:
 *
 *   - data-ajax-popup="true"  click handler (modal opening via fetch)
 *   - .bs-pass-para           click handler (SweetAlert confirm)
 *   - .bs-pass-para-pos       click handler (POS delete confirm)
 *   - data-ajax-popup-over    click handler (overlay modal via fetch)
 *   - input[type=file] change handler (file name display)
 */

import {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} from "../helpers/setup";

let fetchSpy: jest.Mock;

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();

  // custom.ts uses fetch() not $.ajax — mock the former
  fetchSpy = jest.fn(() =>
    Promise.resolve({
      ok: true,
      headers: new Headers({ "content-type": "text/html" }),
      text: () => Promise.resolve("<p>loaded</p>"),
      json: () => Promise.resolve({}),
    } as Response)
  );
  (globalThis as any).fetch = fetchSpy;

  // Provide a bootstrap.Modal mock (needed for popup handlers)
  (globalThis as any).bootstrap = {
    Toast: class Toast {
      show() {}
    },
    Modal: class Modal {
      constructor() {}
      show() {}
    },
  };

  loadCustomJs();
});

/* ================================================================== */
/*  data-ajax-popup                                                    */
/* ================================================================== */
describe('data-ajax-popup="true" click handler', () => {
  test("triggers fetch to the data-url", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<a href="#"
          data-ajax-popup="true"
          data-url="/test/modal"
          data-title="Test Modal"
          data-size="lg"
          class="popup-trigger">Open</a>`,
    );

    const link = document.querySelector(".popup-trigger") as HTMLElement;
    // Use native click — onDelegate uses addEventListener, not jQuery.on
    link.click();

    expect(fetchSpy).toHaveBeenCalled();
    expect(fetchSpy.mock.calls[0][0]).toBe("/test/modal");
  });

  test("sets modal title from data-title", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<a href="#"
          data-ajax-popup="true"
          data-url="/x"
          data-title="My Title"
          data-size="md">Go</a>`,
    );

    const el = document.querySelector("a[data-ajax-popup]") as HTMLElement;
    el.click();
    const title = document.querySelector("#commonModal .modal-title");
    expect(title.textContent).toBe("My Title");
  });

  test("adds modal-lg class for size=lg", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<button data-ajax-popup="true" data-url="/x" data-title="T" data-size="lg">Go</button>`,
    );
    const el = document.querySelector("[data-ajax-popup]") as HTMLElement;
    el.click();
    const dialog = document.querySelector("#commonModal .modal-dialog");
    expect(dialog.classList.contains("modal-lg")).toBe(true);
  });
});

/* ================================================================== */
/*  .bs-pass-para (SweetAlert confirm)                                 */
/* ================================================================== */
describe(".bs-pass-para click handler", () => {
  test("fires Swal confirmation on click", () => {
    const fireSpy = jest.fn().mockResolvedValue({ isConfirmed: false });
    (globalThis as any).Swal = {
      mixin: () => ({ fire: fireSpy }),
      DismissReason: { cancel: "cancel" },
    };

    // Re-load custom.js so the new Swal is captured by the handler
    loadCustomJs();

    document.body.insertAdjacentHTML(
      "beforeend",
      `<form id="delForm" action="/delete">
         <button class="bs-pass-para" type="button">Delete</button>
       </form>`,
    );

    const el = document.querySelector(".bs-pass-para") as HTMLElement;
    el.click();
    expect(fireSpy).toHaveBeenCalled();
  });
});

/* ================================================================== */
/*  input[type=file] change handler                                    */
/* ================================================================== */
describe("input[type=file] change handler", () => {
  test("does not throw on file input change", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<input type="file" data-filename="upload-label" />
       <span class="upload-label">Choose file</span>`,
    );

    expect(() => {
      const input = document.querySelector("input[type=file]");
      const evt = new Event("change", { bubbles: true });
      input.dispatchEvent(evt);
    }).not.toThrow();
  });
});

/* ================================================================== */
/*  data-ajax-popup-over handler                                       */
/* ================================================================== */
describe('data-ajax-popup-over="true" click handler', () => {
  test("triggers fetch to the data-url", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<a href="#"
          data-ajax-popup-over="true"
          data-url="/ai/generate"
          data-title="AI Modal"
          data-size="lg">AI</a>`,
    );

    const el = document.querySelector("[data-ajax-popup-over]") as HTMLElement;
    el.click();
    expect(fetchSpy).toHaveBeenCalled();
    expect(fetchSpy.mock.calls[0][0]).toContain("/ai/generate");
  });

  test("sets overlay modal title", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<div data-ajax-popup-over="true"
            data-url="/x"
            data-title="Overlay Title"
            data-size="md">Click</div>`,
    );
    const el = document.querySelector("[data-ajax-popup-over]") as HTMLElement;
    el.click();
    const title = document.querySelector("#commonModalOver .modal-title");
    expect(title.textContent).toBe("Overlay Title");
  });
});
