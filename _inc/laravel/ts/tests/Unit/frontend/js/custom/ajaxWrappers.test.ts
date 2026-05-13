/**
 * Tests for postAjax(url, data, cb) and deleteAjax(url, data, cb)
 *
 * custom.ts migrated from $.ajax to fetch() — both wrappers:
 *   1. Read the CSRF token from <meta name="csrf-token">
 *   2. Merge {_token, ...data} into URL-encoded body
 *   3. Call fetch with type POST or DELETE
 *   4. Invoke the callback with the response data
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

  // Mock global fetch — custom.ts uses fetch() not $.ajax
  fetchSpy = jest.fn(() =>
    Promise.resolve({
      ok: true,
      headers: new Headers({ "content-type": "application/json" }),
      json: () => Promise.resolve({ ok: true }),
      text: () => Promise.resolve(""),
    } as Response)
  );
  (globalThis as any).fetch = fetchSpy;

  loadCustomJs();
});

/* ======== postAjax ======== */

describe("postAjax", () => {
  test("is defined as a global function", () => {
    expect(typeof (globalThis as any).postAjax).toBe("function");
  });

  test("calls fetch with method POST", () => {
    (globalThis as any).postAjax("/api/test", { foo: "bar" }, jest.fn());
    expect(fetchSpy).toHaveBeenCalledTimes(1);
    const opts = fetchSpy.mock.calls[0][1];
    expect(opts.method).toBe("POST");
  });

  test("sends the correct URL", () => {
    (globalThis as any).postAjax("/api/endpoint", {}, jest.fn());
    expect(fetchSpy.mock.calls[0][0]).toBe("/api/endpoint");
  });

  test("includes CSRF token in the body", async () => {
    (globalThis as any).postAjax("/x", {}, jest.fn());
    const opts = fetchSpy.mock.calls[0][1];
    const body = new URLSearchParams(opts.body as string);
    expect(body.get("_token")).toBe("test-csrf-token-123");
  });

  test("merges user data with _token", async () => {
    (globalThis as any).postAjax("/x", { name: "Alice", age: 30 }, jest.fn());
    const opts = fetchSpy.mock.calls[0][1];
    const body = new URLSearchParams(opts.body as string);
    expect(body.get("name")).toBe("Alice");
    expect(body.get("age")).toBe("30");
    expect(body.get("_token")).toBe("test-csrf-token-123");
  });

  test("invokes the callback with response data", async () => {
    const cb = jest.fn();
    (globalThis as any).postAjax("/x", {}, cb);
    // flush microtasks so the .then() fires
    await new Promise(r => setTimeout(r, 0));
    expect(cb).toHaveBeenCalledWith({ ok: true });
  });

  test("works with empty data object", () => {
    (globalThis as any).postAjax("/x", {}, jest.fn());
    const opts = fetchSpy.mock.calls[0][1];
    const body = new URLSearchParams(opts.body as string);
    // Only _token should be present
    expect(body.has("_token")).toBe(true);
  });
});

/* ======== deleteAjax ======== */

describe("deleteAjax", () => {
  test("is defined as a global function", () => {
    expect(typeof (globalThis as any).deleteAjax).toBe("function");
  });

  test("calls fetch with method DELETE", () => {
    (globalThis as any).deleteAjax("/api/test/1", {}, jest.fn());
    expect(fetchSpy).toHaveBeenCalledTimes(1);
    const opts = fetchSpy.mock.calls[0][1];
    expect(opts.method).toBe("DELETE");
  });

  test("sends the correct URL", () => {
    (globalThis as any).deleteAjax("/api/items/42", {}, jest.fn());
    expect(fetchSpy.mock.calls[0][0]).toBe("/api/items/42");
  });

  test("includes CSRF token in the body", () => {
    (globalThis as any).deleteAjax("/x", {}, jest.fn());
    const opts = fetchSpy.mock.calls[0][1];
    const body = new URLSearchParams(opts.body as string);
    expect(body.get("_token")).toBe("test-csrf-token-123");
  });

  test("merges user data with _token", () => {
    (globalThis as any).deleteAjax("/x", { id: 5 }, jest.fn());
    const opts = fetchSpy.mock.calls[0][1];
    const body = new URLSearchParams(opts.body as string);
    expect(body.get("id")).toBe("5");
    expect(body.get("_token")).toBe("test-csrf-token-123");
  });

  test("invokes the callback with response data", async () => {
    const cb = jest.fn();
    (globalThis as any).deleteAjax("/x", {}, cb);
    await new Promise(r => setTimeout(r, 0));
    expect(cb).toHaveBeenCalledWith({ ok: true });
  });
});
