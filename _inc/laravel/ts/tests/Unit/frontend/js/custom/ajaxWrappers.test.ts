/**
 * Tests for postAjax(url, data, cb) and deleteAjax(url, data, cb)
 *
 * Both wrappers:
 *   1. Read the CSRF token from <meta name="csrf-token">
 *   2. Merge {_token, ...data}
 *   3. Call $.ajax with type POST or DELETE
 *   4. Invoke the callback with the response data
 */

import {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} from "../helpers/setup";

let ajaxSpy: jest.Mock;

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();
  loadCustomJs();

  // Spy on $.ajax so we can inspect calls and simulate responses
  ajaxSpy = jest.fn(opts => {
    // immediately call success with a canned response
    if (opts.success) opts.success({ ok: true });
  });
  global.$.ajax = ajaxSpy;
});

/* ======== postAjax ======== */

describe("postAjax", () => {
  test("is defined as a global function", () => {
    expect(typeof (globalThis as any).postAjax).toBe("function");
  });

  test("calls $.ajax with type POST", () => {
    const cb = jest.fn();
    (globalThis as any).postAjax("/api/test", { foo: "bar" }, cb);

    expect(ajaxSpy).toHaveBeenCalledTimes(1);
    const opts = ajaxSpy.mock.calls[0][0];
    expect(opts.type).toBe("POST");
  });

  test("sends the correct URL", () => {
    (globalThis as any).postAjax("/api/endpoint", {}, jest.fn());
    expect(ajaxSpy.mock.calls[0][0].url).toBe("/api/endpoint");
  });

  test("includes CSRF token from meta tag", () => {
    (globalThis as any).postAjax("/x", {}, jest.fn());
    const data = ajaxSpy.mock.calls[0][0].data;
    expect(data._token).toBe("test-csrf-token-123");
  });

  test("merges user data with _token", () => {
    (globalThis as any).postAjax("/x", { name: "Alice", age: 30 }, jest.fn());
    const data = ajaxSpy.mock.calls[0][0].data;
    expect(data.name).toBe("Alice");
    expect(data.age).toBe(30);
    expect(data._token).toBe("test-csrf-token-123");
  });

  test("invokes the callback with response data", () => {
    const cb = jest.fn();
    (globalThis as any).postAjax("/x", {}, cb);
    expect(cb).toHaveBeenCalledWith({ ok: true });
  });

  test("works with empty data object", () => {
    (globalThis as any).postAjax("/x", {}, jest.fn());
    const data = ajaxSpy.mock.calls[0][0].data;
    expect(Object.keys(data)).toEqual(["_token"]);
  });
});

/* ======== deleteAjax ======== */

describe("deleteAjax", () => {
  test("is defined as a global function", () => {
    expect(typeof (globalThis as any).deleteAjax).toBe("function");
  });

  test("calls $.ajax with type DELETE", () => {
    const cb = jest.fn();
    (globalThis as any).deleteAjax("/api/test/1", {}, cb);

    expect(ajaxSpy).toHaveBeenCalledTimes(1);
    const opts = ajaxSpy.mock.calls[0][0];
    expect(opts.type).toBe("DELETE");
  });

  test("sends the correct URL", () => {
    (globalThis as any).deleteAjax("/api/items/42", {}, jest.fn());
    expect(ajaxSpy.mock.calls[0][0].url).toBe("/api/items/42");
  });

  test("includes CSRF token from meta tag", () => {
    (globalThis as any).deleteAjax("/x", {}, jest.fn());
    const data = ajaxSpy.mock.calls[0][0].data;
    expect(data._token).toBe("test-csrf-token-123");
  });

  test("merges user data with _token", () => {
    (globalThis as any).deleteAjax("/x", { id: 5 }, jest.fn());
    const data = ajaxSpy.mock.calls[0][0].data;
    expect(data.id).toBe(5);
    expect(data._token).toBe("test-csrf-token-123");
  });

  test("invokes the callback with response data", () => {
    const cb = jest.fn();
    (globalThis as any).deleteAjax("/x", {}, cb);
    expect(cb).toHaveBeenCalledWith({ ok: true });
  });
});
