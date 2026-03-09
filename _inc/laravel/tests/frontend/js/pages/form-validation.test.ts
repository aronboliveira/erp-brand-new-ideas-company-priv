/**
 * @file form-validation.test.ts
 * @description Tests for form-validation.js FormValidationController
 */
import {
  jest,
  describe,
  it,
  expect,
  beforeEach,
  afterEach,
} from "@jest/globals";
import path from "path";
import fs from "fs";
import { resetDOM, PUBLIC_JS_PATH } from "../setup";

const FORM_VALIDATION_PATH = path.join(
  PUBLIC_JS_PATH,
  "pages",
  "form-validation.js",
);
const win = window as any;

function createMockBouncer(): any {
  const MockBouncer = jest.fn(function (
    this: any,
    selector: string,
    options?: any,
  ) {
    this.selector = selector;
    this.options = options;
    this.destroyed = false;
    return this;
  }) as any;
  MockBouncer.prototype.destroy = jest.fn(function (this: any) {
    this.destroyed = true;
  });
  return MockBouncer;
}

function loadFormValidation(): void {
  document.body?.removeAttribute("data-validation-init");
  const code = fs.readFileSync(FORM_VALIDATION_PATH, "utf-8");
  const fn = new Function("window", "document", "Bouncer", code);
  fn(window, document, win.Bouncer);
}

describe("FormValidationController", () => {
  beforeEach(() => {
    resetDOM();
    win.Bouncer = createMockBouncer();
  });

  afterEach(() => {
    delete win.Bouncer;
  });

  describe("Initialization", () => {
    it("should initialize without errors", () => {
      expect(() => loadFormValidation()).not.toThrow();
    });

    it("should create a Bouncer instance with [data-bouncer] selector", () => {
      loadFormValidation();
      expect(win.Bouncer).toHaveBeenCalledWith(
        "[data-bouncer]",
        expect.objectContaining({ disableSubmit: true }),
      );
    });

    it("should not re-initialize if data-validation-init is set", () => {
      document.body.setAttribute("data-validation-init", "true");
      const code = fs.readFileSync(FORM_VALIDATION_PATH, "utf-8");
      const fn = new Function("window", "document", "Bouncer", code);
      fn(window, document, win.Bouncer);
      // Bouncer should NOT be called since init is skipped
      expect(win.Bouncer).not.toHaveBeenCalled();
    });

    it("should not create Bouncer when it is not loaded", () => {
      const MockBouncer = jest.fn();
      const code = fs.readFileSync(FORM_VALIDATION_PATH, "utf-8");
      const fn = new Function("window", "document", "Bouncer", code);
      // Pass undefined for Bouncer — constructor should not be called
      fn(window, document, undefined);
      expect(MockBouncer).not.toHaveBeenCalled();
    });

    it("should handle Bouncer constructor throwing", () => {
      const errorSpy = jest
        .spyOn(console, "error")
        .mockImplementation(() => {});
      win.Bouncer = jest.fn(() => {
        throw new Error("Bouncer crash");
      });
      loadFormValidation();
      expect(errorSpy).toHaveBeenCalledWith(
        expect.stringContaining("Error initializing Bouncer"),
        expect.any(Error),
      );
      errorSpy.mockRestore();
    });
  });

  describe("Custom Validations", () => {
    it("should configure valueMismatch custom validation", () => {
      loadFormValidation();
      const callArgs = (win.Bouncer as jest.Mock).mock.calls[0] as any[];
      expect(callArgs[1].customValidations).toBeDefined();
      expect(callArgs[1].customValidations.valueMismatch).toBeInstanceOf(
        Function,
      );
    });

    it("valueMismatch should return false when no data-bouncer-match", () => {
      loadFormValidation();
      const callArgs = (win.Bouncer as jest.Mock).mock.calls[0] as any[];
      const valueMismatch = callArgs[1].customValidations.valueMismatch;

      const field = document.createElement("input");
      expect(valueMismatch(field)).toBe(false);
    });

    it("valueMismatch should return false when fields match", () => {
      loadFormValidation();
      const callArgs = (win.Bouncer as jest.Mock).mock.calls[0] as any[];
      const valueMismatch = callArgs[1].customValidations.valueMismatch;

      const form = document.createElement("form");
      const field1 = document.createElement("input");
      field1.setAttribute("data-bouncer-match", "#confirm");
      field1.value = "test123";
      const field2 = document.createElement("input");
      field2.id = "confirm";
      field2.value = "test123";
      form.appendChild(field1);
      form.appendChild(field2);
      document.body.appendChild(form);

      expect(valueMismatch(field1)).toBe(false);
    });

    it("valueMismatch should return true when fields don't match", () => {
      loadFormValidation();
      const callArgs = (win.Bouncer as jest.Mock).mock.calls[0] as any[];
      const valueMismatch = callArgs[1].customValidations.valueMismatch;

      const form = document.createElement("form");
      const field1 = document.createElement("input");
      field1.setAttribute("data-bouncer-match", "#confirm");
      field1.value = "test123";
      const field2 = document.createElement("input");
      field2.id = "confirm";
      field2.value = "different";
      form.appendChild(field1);
      form.appendChild(field2);
      document.body.appendChild(form);

      expect(valueMismatch(field1)).toBe(true);
    });

    it("valueMismatch should return true when target field not found", () => {
      loadFormValidation();
      const callArgs = (win.Bouncer as jest.Mock).mock.calls[0] as any[];
      const valueMismatch = callArgs[1].customValidations.valueMismatch;

      const form = document.createElement("form");
      const field = document.createElement("input");
      field.setAttribute("data-bouncer-match", "#nonexistent");
      form.appendChild(field);
      document.body.appendChild(form);

      expect(valueMismatch(field)).toBe(true);
    });
  });
});
