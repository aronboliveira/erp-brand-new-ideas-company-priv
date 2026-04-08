/**
 * Tests for arrayToJson(form)
 *
 * arrayToJson() takes a jQuery-wrapped form, calls serializeArray(),
 * and converts the [{name, value}] array into a plain {name: value} object.
 */

import {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} from "../helpers/setup";

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();
  loadCustomJs();
});

describe("arrayToJson", () => {
  test("is defined as a global function", () => {
    expect(typeof (globalThis as any).arrayToJson).toBe("function");
  });

  test("converts a simple form with text inputs", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<form id="testForm">
         <input name="first_name" value="John" />
         <input name="last_name"  value="Doe"  />
       </form>`,
    );
    const result = (globalThis as any).arrayToJson("#testForm");
    expect(result).toEqual({ first_name: "John", last_name: "Doe" });
  });

  test("returns empty object for an empty form", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      '<form id="emptyForm"></form>',
    );
    const result = (globalThis as any).arrayToJson("#emptyForm");
    expect(result).toEqual({});
  });

  test("handles select elements", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<form id="selectForm">
         <select name="color">
           <option value="red" selected>Red</option>
           <option value="blue">Blue</option>
         </select>
       </form>`,
    );
    const result = (globalThis as any).arrayToJson("#selectForm");
    expect(result).toEqual({ color: "red" });
  });

  test("handles textarea elements", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<form id="textareaForm">
         <textarea name="notes">Hello world</textarea>
       </form>`,
    );
    const result = (globalThis as any).arrayToJson("#textareaForm");
    expect(result).toEqual({ notes: "Hello world" });
  });

  test("last value wins for duplicate field names", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<form id="dupForm">
         <input name="item" value="first" />
         <input name="item" value="second" />
       </form>`,
    );
    // serializeArray produces two entries; the loop overwrites, so last wins
    const result = (globalThis as any).arrayToJson("#dupForm");
    expect(result.item).toBe("second");
  });

  test("handles checkbox (only checked ones serialize)", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<form id="cbForm">
         <input type="checkbox" name="agree" value="yes" checked />
         <input type="checkbox" name="newsletter" value="yes" />
       </form>`,
    );
    const result = (globalThis as any).arrayToJson("#cbForm");
    expect(result).toEqual({ agree: "yes" });
    expect(result.newsletter).toBeUndefined();
  });

  test("handles hidden inputs", () => {
    document.body.insertAdjacentHTML(
      "beforeend",
      `<form id="hiddenForm">
         <input type="hidden" name="_token" value="abc123" />
       </form>`,
    );
    const result = (globalThis as any).arrayToJson("#hiddenForm");
    expect(result).toEqual({ _token: "abc123" });
  });
});
