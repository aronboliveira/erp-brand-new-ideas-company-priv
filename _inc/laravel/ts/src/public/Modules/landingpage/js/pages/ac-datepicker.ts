/**
 * @fileoverview TypeScript version of public/Modules/landingpage/js/pages/ac-datepicker.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-datepicker
 */

/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unused-vars */
/* global $, jQuery */
"use strict";

import type {
  DatepickerConstructor as DatepickerConstructorLp,
  DateRangePickerConstructor as DateRangePickerConstructorLp,
} from "../../../../../declarations/pages/datepicker";

(function () {
  const _d_week = new Datepicker(
    document.querySelector<HTMLElement>("#d_week"),
    {
      buttonClass: "btn",
    },
  );
})();
(function () {
  const _d_highlight = new Datepicker(
    document.querySelector<HTMLElement>("#d_highlight"),
    {
      buttonClass: "btn",
      daysOfWeekHighlighted: [1],
    },
  );
})();

(function () {
  const _d_auto = new Datepicker(
    document.querySelector<HTMLElement>("#d_auto"),
    {
      buttonClass: "btn",
      autohide: true,
    },
  );
})();

(function () {
  const _d_disable = new Datepicker(
    document.querySelector<HTMLElement>("#d_disable"),
    {
      buttonClass: "btn",
      datesDisabled: ["02/18/2022", "02/22/2022"],
    },
  );
})();

// $('#d_toggle').datepicker({
//     keyboardNavigation: false,
//     forceParse: false,
//     toggleActive: true
// });

(function () {
  const _d_today = new Datepicker(
    document.querySelector<HTMLElement>("#d_today"),
    {
      buttonClass: "btn",
      todayHighlight: true,
    },
  );
})();

(function () {
  const _disp_week = new Datepicker(
    document.querySelector<HTMLElement>("#disp_week"),
    {
      buttonClass: "btn",
      calendarWeeks: true,
    },
  );
})();

(function () {
  const el = document.querySelector<HTMLElement>("#datepicker_range");
  const _datepicker_range = el
    ? new DateRangePicker(el, {
        buttonClass: "btn",
      })
    : null;
})();

export {};
