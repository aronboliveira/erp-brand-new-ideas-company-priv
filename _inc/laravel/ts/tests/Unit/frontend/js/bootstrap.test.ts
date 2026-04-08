/**
 * Tests for resources/js/bootstrap.ts
 *
 * bootstrap.ts imports lodash and axios, exposes them on window,
 * and sets the X-Requested-With header.
 */

const BOOTSTRAP_MODULE = "../../../../src/resources/js/bootstrap";

describe("bootstrap.ts", () => {
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
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    require(BOOTSTRAP_MODULE);
    expect((window as any).axios).toBeDefined();
  });

  test("sets X-Requested-With header on axios", () => {
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    require(BOOTSTRAP_MODULE);
    expect((window as any).axios.defaults.headers.common["X-Requested-With"]).toBe("XMLHttpRequest");
  });

  test("sets window._ (lodash)", () => {
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    require(BOOTSTRAP_MODULE);
    expect((window as any)._).toBeDefined();
  });

  test("lodash on window has expected property", () => {
    // eslint-disable-next-line @typescript-eslint/no-require-imports
    require(BOOTSTRAP_MODULE);
    expect((window as any)._.VERSION).toBe("4.x-mock");
  });
});
