/**
 * Tests for jQuery event handlers registered in custom.js:
 *
 *   - data-ajax-popup="true"  click handler (modal opening)
 *   - .bs-pass-para           click handler (SweetAlert confirm)
 *   - .bs-pass-para-pos       click handler (POS delete confirm)
 *   - data-ajax-popup-over    click handler (overlay modal)
 *   - input[type=file] change handler (file name display)
 */

const {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} = require("../helpers/setup.cjs");

let ajaxSpy;

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();

  // Capture $.ajax calls
  ajaxSpy = jest.fn(opts => {
    if (opts.success) opts.success("<p>loaded</p>");
  });

  loadCustomJs();

  // Attach spy AFTER loadCustomJs (which may reset $.ajax)
  global.$.ajax = ajaxSpy;
});

/* ================================================================== */
/*  data-ajax-popup                                                    */
/* ================================================================== */
describe('data-ajax-popup="true" click handler', () => {
  test("triggers $.ajax to the data-url", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<a href="#"
          data-ajax-popup="true"
          data-url="/test/modal"
          data-title="Test Modal"
          data-size="lg"
          class="popup-trigger">Open</a>`,
    );

    const link = document.querySelector(".popup-trigger");
    global.$(link).trigger("click");

    expect(ajaxSpy).toHaveBeenCalled();
    expect(ajaxSpy.mock.calls[0][0].url).toBe("/test/modal");
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

    global.$(document.querySelector("a[data-ajax-popup]")).trigger("click");
    const title = document.querySelector("#commonModal .modal-title");
    expect(title.innerHTML).toBe("My Title");
  });

  test("adds modal-lg class for size=lg", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<button data-ajax-popup="true" data-url="/x" data-title="T" data-size="lg">Go</button>`,
    );
    global.$(document.querySelector("[data-ajax-popup]")).trigger("click");
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
    global.Swal = {
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

    global.$(document.querySelector(".bs-pass-para")).trigger("click");
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
      // Simulate a change event
      const evt = new Event("change", { bubbles: true });
      input.dispatchEvent(evt);
    }).not.toThrow();
  });
});

/* ================================================================== */
/*  data-ajax-popup-over handler                                       */
/* ================================================================== */
describe('data-ajax-popup-over="true" click handler', () => {
  test("triggers $.ajax to the data-url", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<a href="#"
          data-ajax-popup-over="true"
          data-url="/ai/generate"
          data-title="AI Modal"
          data-size="lg">AI</a>`,
    );

    global.$(document.querySelector("[data-ajax-popup-over]")).trigger("click");
    expect(ajaxSpy).toHaveBeenCalled();
    expect(ajaxSpy.mock.calls[0][0].url).toContain("/ai/generate");
  });

  test("sets overlay modal title", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<div data-ajax-popup-over="true"
            data-url="/x"
            data-title="Overlay Title"
            data-size="md">Click</div>`,
    );
    global.$(document.querySelector("[data-ajax-popup-over]")).trigger("click");
    const title = document.querySelector("#commonModalOver .modal-title");
    expect(title.innerHTML).toBe("Overlay Title");
  });
});
