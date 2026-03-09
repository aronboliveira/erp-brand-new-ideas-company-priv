/**
 * Vendor Library Ambient Declarations
 * @file ts/src/declarations/routes/vendor-libs-ambient.d.ts
 * @description Ambient declarations for external vendor libraries (no imports/exports)
 */

/**
 * html2pdf library from html2pdf.js
 * Available on every page that loads html2pdf.bundle.min.js.
 */
declare const html2pdf: () => {
  set: (opt: unknown) => {
    from: (el: HTMLElement) => {
      save: () => {
        then: (fn: () => void) => {
          catch: (fn: (err: unknown) => void) => void;
        };
      };
    };
  };
};

/**
 * Toast notification utility from show_toastr.js
 */
declare const show_toastr: (type: string, msg: string, status: string) => void;

/**
 * Dragula drag-and-drop library
 */
declare const dragula:
  | ((
      containers: Element[],
      options?: Record<string, unknown>,
    ) => {
      on: (event: string, callback: (...args: unknown[]) => void) => unknown;
      destroy: () => void;
    })
  | undefined;

/**
 * Datepicker from vanillajs-datepicker
 */
declare const Datepicker: new (
  el: HTMLElement | null,
  options?: Record<string, unknown>,
) => unknown;

/**
 * DateRangePicker from vanillajs-datepicker
 */
declare const DateRangePicker: new (
  el: HTMLElement | null,
  options?: Record<string, unknown>,
) => unknown;

/**
 * Bouncer form validation library
 */
declare const Bouncer: new (
  selector: string,
  options?: Record<string, unknown>,
) => unknown;
