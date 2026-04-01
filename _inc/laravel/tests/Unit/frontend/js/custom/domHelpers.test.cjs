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

const {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} = require("../helpers/setup.cjs");

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
    expect(typeof global.daterange).toBe("function");
  });

  test("does not throw when #pc-daterangepicker-1 exists", () => {
    expect(() => global.daterange()).not.toThrow();
  });

  test("does not throw when element is absent", () => {
    document.getElementById("pc-daterangepicker-1").remove();
    expect(() => global.daterange()).not.toThrow();
  });
});

/* ================================================================== */
/*  select2()                                                         */
/* ================================================================== */
describe("select2", () => {
  test("is defined", () => {
    expect(typeof global.select2).toBe("function");
  });

  test("does not throw with no .select2 elements", () => {
    expect(() => global.select2()).not.toThrow();
  });

  test("instantiates Choices on .select2 elements", () => {
    const choicesSpy = jest.fn();
    global.Choices = choicesSpy;

    document.body.insertAdjacentHTML(
      "beforeend",
      '<select class="select2" id="mySelect"><option>A</option></select>',
    );
    global.select2();
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
    expect(typeof global.summernote).toBe("function");
  });

  test("does not throw with no .summernote-simple elements", () => {
    expect(() => global.summernote()).not.toThrow();
  });

  test("calls $.fn.summernote on matching elements", () => {
    const spy = jest.fn().mockReturnThis();
    global.$.fn.summernote = spy;

    document.body.insertAdjacentHTML(
      "beforeend",
      '<div class="summernote-simple"></div>',
    );
    global.summernote();
    expect(spy).toHaveBeenCalled();
  });
});

/* ================================================================== */
/*  commonLoader()                                                     */
/* ================================================================== */
describe("commonLoader", () => {
  test("is defined", () => {
    expect(typeof global.commonLoader).toBe("function");
  });

  test("does not throw when called on empty DOM", () => {
    expect(() => global.commonLoader()).not.toThrow();
  });

  test("initialises tooltip on [data-toggle=tooltip]", () => {
    const tooltipSpy = jest.fn().mockReturnThis();
    global.$.fn.tooltip = tooltipSpy;

    document.body.insertAdjacentHTML(
      "beforeend",
      '<span data-toggle="tooltip" title="hi">hover me</span>',
    );
    global.commonLoader();
    expect(tooltipSpy).toHaveBeenCalled();
  });

  test("initialises tagsinput on [data-toggle=tags]", () => {
    const tagsSpy = jest.fn().mockReturnThis();
    global.$.fn.tagsinput = tagsSpy;

    document.body.insertAdjacentHTML(
      "beforeend",
      '<input data-toggle="tags" />',
    );
    global.commonLoader();
    expect(tagsSpy).toHaveBeenCalled();
  });
});

/* ================================================================== */
/*  common_bind()                                                      */
/* ================================================================== */
describe("common_bind", () => {
  test("is defined", () => {
    expect(typeof global.common_bind).toBe("function");
  });

  test("delegates to select2() without throwing", () => {
    expect(() => global.common_bind()).not.toThrow();
  });
});

/* ================================================================== */
/*  wcqib_refresh_quantity_increments()                                */
/* ================================================================== */
describe("wcqib_refresh_quantity_increments", () => {
  test("is defined", () => {
    expect(typeof global.wcqib_refresh_quantity_increments).toBe("function");
  });

  test("adds plus/minus buttons to quantity containers", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<div class="quantity">
         <input type="number" name="quantity" value="1" min="0" max="10" step="1" />
       </div>`,
    );
    global.wcqib_refresh_quantity_increments();

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
    global.wcqib_refresh_quantity_increments();
    global.wcqib_refresh_quantity_increments();

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
/*  session_key global                                                 */
/* ================================================================== */
describe("session_key", () => {
  test("is defined as a string", () => {
    expect(typeof global.session_key).toBe("string");
  });
});
