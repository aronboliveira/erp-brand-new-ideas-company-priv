/**
 * @file setup.ts
 * @description Jest test setup for ERP Brand New Ideas Company frontend tests
 */
import path from "path";
import fs from "fs";

// Path constants
export const PUBLIC_JS_PATH = path.resolve(
  __dirname,
  "../../../public/assets/js",
);
export const CORE_PATH = path.join(PUBLIC_JS_PATH, "core");
export const GENERIC_PATH = path.join(PUBLIC_JS_PATH, "generic");
export const PAGES_PATH = path.join(PUBLIC_JS_PATH, "pages");
export const ROUTES_PATH = path.join(PAGES_PATH, "routes");

/**
 * Loads a JavaScript file by evaluating it in the global scope
 * @param relativePath Path relative to public/assets/js
 * @returns The evaluated code
 */
export function loadJsFile(relativePath: string): string {
  const fullPath = path.join(PUBLIC_JS_PATH, relativePath);
  const code = fs.readFileSync(fullPath, "utf-8");

  // Evaluate the code in the global context
  const script = new Function(code);
  script();

  return code;
}

/**
 * Loads a JavaScript file and returns its content without executing
 * @param relativePath Path relative to public/assets/js
 * @returns The file content
 */
export function readJsFile(relativePath: string): string {
  const fullPath = path.join(PUBLIC_JS_PATH, relativePath);
  return fs.readFileSync(fullPath, "utf-8");
}

/**
 * Creates a mock Bootstrap object
 */
export function createMockBootstrap(): typeof window.bootstrap {
  const mockToastInstances = new Map<Element, any>();
  const mockModalInstances = new Map<Element, any>();

  const MockToast = function (this: any, element: Element, options?: any) {
    this.element = element;
    this.options = options;
    this._shown = false;
    mockToastInstances.set(element, this);
  } as any;

  MockToast.prototype = {
    show() {
      this._shown = true;
      this.element.classList.add("show");
      this.element.dispatchEvent(new Event("shown.bs.toast"));
    },
    hide() {
      this._shown = false;
      this.element.classList.remove("show");
      this.element.dispatchEvent(new Event("hidden.bs.toast"));
    },
    dispose() {
      mockToastInstances.delete(this.element);
    },
  };

  MockToast.getInstance = (el: Element) => mockToastInstances.get(el) || null;
  MockToast.getOrCreateInstance = (el: Element) => {
    return mockToastInstances.get(el) || new MockToast(el);
  };

  const MockModal = function (this: any, element: Element, options?: any) {
    this.element = element;
    this.options = options;
    this._shown = false;
    mockModalInstances.set(element, this);
  } as any;

  MockModal.prototype = {
    show() {
      this._shown = true;
      this.element.classList.add("show");
      document.body.classList.add("modal-open");
      this.element.dispatchEvent(new Event("shown.bs.modal"));
    },
    hide() {
      this._shown = false;
      this.element.classList.remove("show");
      document.body.classList.remove("modal-open");
      this.element.dispatchEvent(new Event("hidden.bs.modal"));
    },
    toggle() {
      if (this._shown) {
        this.hide();
      } else {
        this.show();
      }
    },
    handleUpdate() {},
    dispose() {
      mockModalInstances.delete(this.element);
    },
  };

  MockModal.getInstance = (el: Element) => mockModalInstances.get(el) || null;
  MockModal.getOrCreateInstance = (el: Element) => {
    return mockModalInstances.get(el) || new MockModal(el);
  };

  return {
    Toast: MockToast,
    Modal: MockModal,
  };
}

/**
 * Resets the DOM to a clean state
 */
export function resetDOM(): void {
  document.head.innerHTML = "";
  document.body.innerHTML = "";
  document.body.className = "";

  // Clear any added styles
  document.querySelectorAll("style").forEach(el => el.remove());
}

/**
 * Waits for a specified number of milliseconds
 */
export function wait(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Creates a form element with specified attributes
 */
export function createForm(
  attributes: Record<string, string> = {},
): HTMLFormElement {
  const form = document.createElement("form");
  Object.entries(attributes).forEach(([key, value]) => {
    form.setAttribute(key, value);
  });
  document.body.appendChild(form);
  return form;
}

/**
 * Creates an input element with specified attributes
 */
export function createInput(
  type: string = "text",
  attributes: Record<string, string> = {},
): HTMLInputElement {
  const input = document.createElement("input");
  input.type = type;
  Object.entries(attributes).forEach(([key, value]) => {
    input.setAttribute(key, value);
  });
  return input;
}

/**
 * Creates a button element with specified attributes
 */
export function createButton(
  text: string = "Click",
  attributes: Record<string, string> = {},
): HTMLButtonElement {
  const button = document.createElement("button");
  button.textContent = text;
  Object.entries(attributes).forEach(([key, value]) => {
    button.setAttribute(key, value);
  });
  return button;
}

/**
 * Simulates a click event on an element
 */
export function simulateClick(element: HTMLElement): void {
  const event = new MouseEvent("click", {
    bubbles: true,
    cancelable: true,
    view: window,
  });
  element.dispatchEvent(event);
}

/**
 * Simulates a submit event on a form
 */
export function simulateSubmit(form: HTMLFormElement): void {
  const event = new Event("submit", {
    bubbles: true,
    cancelable: true,
  });
  form.dispatchEvent(event);
}

/**
 * Simulates an input event on an element
 */
export function simulateInput(element: HTMLElement, value: string): void {
  if (
    element instanceof HTMLInputElement ||
    element instanceof HTMLTextAreaElement
  ) {
    element.value = value;
  }
  element.dispatchEvent(new Event("input", { bubbles: true }));
}

/**
 * Simulates a change event on an element
 */
export function simulateChange(element: HTMLElement, value?: string): void {
  if (
    value !== undefined &&
    (element instanceof HTMLInputElement ||
      element instanceof HTMLTextAreaElement)
  ) {
    element.value = value;
  }
  element.dispatchEvent(new Event("change", { bubbles: true }));
}

/**
 * Simulates a keyboard event
 */
export function simulateKeyboard(
  element: HTMLElement | Document,
  eventType: "keydown" | "keyup" | "keypress",
  key: string,
  options: Partial<KeyboardEventInit> = {},
): void {
  const event = new KeyboardEvent(eventType, {
    key,
    bubbles: true,
    cancelable: true,
    ...options,
  });
  element.dispatchEvent(event);
}

// Global test utilities
(global as any).wait = wait;
(global as any).resetDOM = resetDOM;
