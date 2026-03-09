/**
 * Tests for resources/js/bootstrap.js
 *
 * bootstrap.js imports lodash and axios, exposes them on window,
 * and sets the X-Requested-With header.
 */

describe("bootstrap.js", () => {
  let originalWindow;

  beforeEach(() => {
    // Clear module cache so each test gets a fresh import
    jest.resetModules();

    // Mock lodash
    jest.mock(
      "lodash",
      () => {
        const lodashMock = { VERSION: "4.x-mock", isEmpty: () => true };
        return { __esModule: true, default: lodashMock };
      },
      { virtual: true },
    );

    // Mock axios
    jest.mock(
      "axios",
      () => {
        const axiosMock = {
          get: jest.fn(),
          post: jest.fn(),
          defaults: {
            headers: {
              common: {},
            },
          },
        };
        return { __esModule: true, default: axiosMock };
      },
      { virtual: true },
    );
  });

  test("sets window.axios", () => {
    require("../../../../resources/js/bootstrap.js");
    expect(window.axios).toBeDefined();
  });

  test("sets X-Requested-With header on axios", () => {
    require("../../../../resources/js/bootstrap.js");
    expect(window.axios.defaults.headers.common["X-Requested-With"]).toBe(
      "XMLHttpRequest",
    );
  });

  test("sets window._ (lodash)", () => {
    require("../../../../resources/js/bootstrap.js");
    expect(window._).toBeDefined();
  });

  test("lodash on window has expected property", () => {
    require("../../../../resources/js/bootstrap.js");
    expect(window._.VERSION).toBe("4.x-mock");
  });
});
