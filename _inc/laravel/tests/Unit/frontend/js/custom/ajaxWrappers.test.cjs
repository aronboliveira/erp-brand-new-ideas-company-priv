/**
 * Tests for postAjax(url, data, cb) and deleteAjax(url, data, cb)
 *
 * Both wrappers now use fetch (vanilla JS rewrite).
 *   1. Read the CSRF token from <meta name="csrf-token">
 *   2. Merge {_token, ...data} into URLSearchParams body
 *   3. Call fetch with method POST or DELETE
 *   4. Invoke the callback with the response data
 */

const {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} = require("../helpers/setup.cjs");

let fetchSpy;

/** Flush microtask queue so .then() callbacks run */
const flush = () => new Promise(r => setTimeout(r, 0));

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();

  // Mock fetch to resolve immediately with JSON
  fetchSpy = jest.fn(() =>
    Promise.resolve({
      headers: { get: () => "application/json" },
      json: () => Promise.resolve({ ok: true }),
      text: () => Promise.resolve("ok"),
    }),
  );
  global.fetch = fetchSpy;

  loadCustomJs();
});

/* ======== postAjax ======== */

describe("postAjax", () => {
  test("is defined as a global function", () => {
    expect(typeof global.postAjax).toBe("function");
  });

  test("calls fetch with method POST", async () => {
    const cb = jest.fn();
    global.postAjax("/api/test", { foo: "bar" }, cb);
    await flush();

    expect(fetchSpy).toHaveBeenCalledTimes(1);
    const [url, opts] = fetchSpy.mock.calls[0];
    expect(url).toBe("/api/test");
    expect(opts.method).toBe("POST");
  });

  test("sends the correct URL", async () => {
    global.postAjax("/api/endpoint", {}, jest.fn());
    await flush();
    expect(fetchSpy.mock.calls[0][0]).toBe("/api/endpoint");
  });

  test("includes CSRF token from meta tag", async () => {
    global.postAjax("/x", {}, jest.fn());
    await flush();
    const body = fetchSpy.mock.calls[0][1].body;
    expect(body).toContain("_token=test-csrf-token-123");
  });

  test("merges user data with _token", async () => {
    global.postAjax("/x", { name: "Alice", age: 30 }, jest.fn());
    await flush();
    const body = fetchSpy.mock.calls[0][1].body;
    expect(body).toContain("_token=test-csrf-token-123");
    expect(body).toContain("name=Alice");
    expect(body).toContain("age=30");
  });

  test("invokes the callback with response data", async () => {
    const cb = jest.fn();
    global.postAjax("/x", {}, cb);
    await flush();
    expect(cb).toHaveBeenCalledWith({ ok: true });
  });

  test("works with empty data object", async () => {
    global.postAjax("/x", {}, jest.fn());
    await flush();
    const body = fetchSpy.mock.calls[0][1].body;
    // Only _token should be present
    const params = new URLSearchParams(body);
    expect([...params.keys()]).toEqual(["_token"]);
  });
});

/* ======== deleteAjax ======== */

describe("deleteAjax", () => {
  test("is defined as a global function", () => {
    expect(typeof global.deleteAjax).toBe("function");
  });

  test("calls fetch with method DELETE", async () => {
    const cb = jest.fn();
    global.deleteAjax("/api/test/1", {}, cb);
    await flush();

    expect(fetchSpy).toHaveBeenCalledTimes(1);
    const [url, opts] = fetchSpy.mock.calls[0];
    expect(url).toBe("/api/test/1");
    expect(opts.method).toBe("DELETE");
  });

  test("sends the correct URL", async () => {
    global.deleteAjax("/api/items/42", {}, jest.fn());
    await flush();
    expect(fetchSpy.mock.calls[0][0]).toBe("/api/items/42");
  });

  test("includes CSRF token from meta tag", async () => {
    global.deleteAjax("/x", {}, jest.fn());
    await flush();
    const body = fetchSpy.mock.calls[0][1].body;
    expect(body).toContain("_token=test-csrf-token-123");
  });

  test("merges user data with _token", async () => {
    global.deleteAjax("/x", { id: 5 }, jest.fn());
    await flush();
    const body = fetchSpy.mock.calls[0][1].body;
    expect(body).toContain("id=5");
    expect(body).toContain("_token=test-csrf-token-123");
  });

  test("invokes the callback with response data", async () => {
    const cb = jest.fn();
    global.deleteAjax("/x", {}, cb);
    await flush();
    expect(cb).toHaveBeenCalledWith({ ok: true });
  });
});
