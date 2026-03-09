/**
 * Vendor Library Type Declarations - Global Augmentations
 * @file ts/src/declarations/routes/vendor-libs.d.ts
 * @description Global interface augmentations for vendor libraries
 * @see vendor-libs-ambient.d.ts for the actual library declarations
 */

/**
 * Window augmentation for global variables
 */
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
    dragula?: (
      containers: Element[],
      options?: Record<string, unknown>,
    ) => {
      on: (event: string, callback: (...args: unknown[]) => void) => unknown;
      destroy: () => void;
    };
    svLang?: {
      zoomMeetings?: {
        store?: {
          routeGuardDefault?: string;
        };
      };
    };
  }

  /**
   * jQuery plugin augmentation for daterangepicker, timepicker, timeEntry
   */
  interface JQuery {
    daterangepicker(options?: Record<string, unknown>): JQuery;
    timepicker(options?: Record<string, unknown>): JQuery;
    timeEntry?(options?: { show24Hours?: boolean }): JQuery;
  }
}

export {};
