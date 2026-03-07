/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-datepicker.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-datepicker
 */


"use strict";

declare let Datepicker: new (el: Element | null, options?: unknown) => unknown;
declare let DateRangePicker: new (
  el: Element | null,
  options?: unknown,
) => unknown;

((): void => {
  try {
    const d_week = new Datepicker(
      document.querySelector<HTMLElement>("#d_week"),
      { buttonClass: "btn" },
    );
    const d_highlight = new Datepicker(
      document.querySelector<HTMLElement>("#d_highlight"),
      { buttonClass: "btn", daysOfWeekHighlighted: [1] },
    );
    const d_auto = new Datepicker(
      document.querySelector<HTMLElement>("#d_auto"),
      { buttonClass: "btn", autohide: true },
    );
    const d_disable = new Datepicker(
      document.querySelector<HTMLElement>("#d_disable"),
      { buttonClass: "btn", datesDisabled: ["02/18/2022", "02/22/2022"] },
    );
    const d_today = new Datepicker(
      document.querySelector<HTMLElement>("#d_today"),
      { buttonClass: "btn", todayHighlight: true },
    );
    const disp_week = new Datepicker(
      document.querySelector<HTMLElement>("#disp_week"),
      { buttonClass: "btn", calendarWeeks: true },
    );
    const datepicker_range = new DateRangePicker(
      document.querySelector<HTMLElement>("#datepicker_range"),
      { buttonClass: "btn" },
    );
    // Suppress unused variable warnings
    void d_week;
    void d_highlight;
    void d_auto;
    void d_disable;
    void d_today;
    void disp_week;
    void datepicker_range;
  } catch (__moduleErr) {
    console.error("[ac-datepicker] failed to initialise:", __moduleErr);
  }
})();

export {};
