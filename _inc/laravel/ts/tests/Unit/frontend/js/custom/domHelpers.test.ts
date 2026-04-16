/**
 * Tests for DOM-initialisation helpers in custom.js:
 *   - daterange()      → flatpickr on #pc-daterangepicker-1
 *   - select2()        → Choices.js on .select2 elements
 *   - summernote()     → summernote on .summernote-simple
 *   - commonLoader()   → tooltip, tagsinput, scrollbar, jscolor, file input
 *   - common_bind()    → delegates to select2()
 *   - JsSearchBox()    → searchBox plugin on .js-searchBox
 *   - wcqib_refresh_quantity_increments() → +/- buttons for quantity inputs
 */

import { loadCustomJs, buildJQueryEnv, buildDomSkeleton } from "../helpers/setup";

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();
  loadCustomJs();
});

/* ================================================================== */
/*  daterange()                                                       */
/* ================================================================== */
describe("daterange", () => {
  test("is defined", () => {
    expect(typeof (globalThis as any).daterange).toBe("function");
  });

  test("does not throw when #pc-daterangepicker-1 exists", () => {
    expect(() => (globalThis as any).daterange()).not.toThrow();
  });

  test("does not throw when element is absent", () => {
    document.getElementById("pc-daterangepicker-1").remove();
    expect(() => (globalThis as any).daterange()).not.toThrow();
  });
});

/* ================================================================== */
/*  select2()                                                         */
/* ================================================================== */
describe("select2", () => {
  test("is defined", () => {
    expect(typeof (globalThis as any).select2).toBe("function");
  });

  test("does not throw with no .select2 elements", () => {
    expect(() => (globalThis as any).select2()).not.toThrow();
  });

  test("instantiates Choices on .select2 elements", () => {
    const choicesSpy = jest.fn();
    (globalThis as any).Choices = choicesSpy;

    document.body.insertAdjacentHTML("beforeend", '<select class="select2" id="mySelect"><option>A</option></select>');
    (globalThis as any).select2();
    expect(choicesSpy).toHaveBeenCalled();
    const arg = choicesSpy.mock.calls[0][0];
    expect(arg).toBe("#mySelect");
  });
});

/* ================================================================== */
/*  summernote()                                                      */
/* ================================================================== */
describe("summernote", () => {
  test("is defined", () => {
    expect(typeof (globalThis as any).summernote).toBe("function");
  });

  test("does not throw with no .summernote-simple elements", () => {
    expect(() => (globalThis as any).summernote()).not.toThrow();
  });

  test("calls $.fn.summernote on matching elements", () => {
    const spy = jest.fn().mockReturnThis();
    global.$.fn.summernote = spy;

    document.body.insertAdjacentHTML("beforeend", '<div class="summernote-simple"></div>');
    (globalThis as any).summernote();
    expect(spy).toHaveBeenCalled();
  });
});

/* ================================================================== */
/*  commonLoader()                                                     */
/* ================================================================== */
describe("commonLoader", () => {
  test("is defined", () => {
    expect(typeof (globalThis as any).commonLoader).toBe("function");
  });

  test("does not throw when called on empty DOM", () => {
    expect(() => (globalThis as any).commonLoader()).not.toThrow();
  });

  test("initialises tooltip on [data-toggle=tooltip]", () => {
    const tooltipSpy = jest.fn().mockReturnThis();
    global.$.fn.tooltip = tooltipSpy;

    document.body.insertAdjacentHTML("beforeend", '<span data-toggle="tooltip" title="hi">hover me</span>');
    (globalThis as any).commonLoader();
    expect(tooltipSpy).toHaveBeenCalled();
  });

  test("initialises tagsinput on [data-toggle=tags]", () => {
    const tagsSpy = jest.fn().mockReturnThis();
    global.$.fn.tagsinput = tagsSpy;

    document.body.insertAdjacentHTML("beforeend", '<input data-toggle="tags" />');
    (globalThis as any).commonLoader();
    expect(tagsSpy).toHaveBeenCalled();
  });
});

/* ================================================================== */
/*  common_bind()                                                      */
/* ================================================================== */
describe("common_bind", () => {
  test("is defined", () => {
    expect(typeof (globalThis as any).common_bind).toBe("function");
  });

  test("delegates to select2() without throwing", () => {
    expect(() => (globalThis as any).common_bind()).not.toThrow();
  });
});

/* ================================================================== */
/*  wcqib_refresh_quantity_increments()                                */
/* ================================================================== */
describe("wcqib_refresh_quantity_increments", () => {
  test("is defined", () => {
    expect(typeof (globalThis as any).wcqib_refresh_quantity_increments).toBe("function");
  });

  test("adds plus/minus buttons to quantity containers", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<div class="quantity">
         <input type="number" name="quantity" value="1" min="0" max="10" step="1" />
       </div>`,
    );
    (globalThis as any).wcqib_refresh_quantity_increments();

    const container = document.querySelector(".quantity");
    expect(container.classList.contains("buttons_added")).toBe(true);

    const minus = container.querySelector(".minus");
    const plus = container.querySelector(".plus");
    expect(minus).not.toBeNull();
    expect(plus).not.toBeNull();
    expect(minus.value).toBe("-");
    expect(plus.value).toBe("+");
  });

  test("does not duplicate buttons on second call", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<div class="quantity">
         <input type="number" name="quantity" value="1" />
       </div>`,
    );
    (globalThis as any).wcqib_refresh_quantity_increments();
    (globalThis as any).wcqib_refresh_quantity_increments();

    const buttons = document.querySelectorAll(".quantity .minus");
    expect(buttons.length).toBe(1);
  });
});

/* ================================================================== */
/*  String.prototype.getDecimals                                       */
/* ================================================================== */
describe("String.prototype.getDecimals", () => {
  test("is defined after custom.js loads", () => {
    expect(typeof "".getDecimals).toBe("function");
  });

  test('"1" → 0', () => expect("1".getDecimals()).toBe(0));
  test('"1.5" → 1', () => expect("1.5".getDecimals()).toBe(1));
  test('"1.50" → 2', () => expect("1.50".getDecimals()).toBe(2));
  test('"0.001" → 3', () => expect("0.001".getDecimals()).toBe(3));
});

/* ================================================================== */
/*  session_key — const-scoped (não vaza para globalThis)              */
/* ================================================================== */
describe("session_key", () => {
  test("is not leaked as a global (const-scoped)", () => {
    // Após migração TS: const session_key não polui globalThis
    expect((globalThis as any).session_key).toBeUndefined();
  });
});
