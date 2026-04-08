/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/ac-datepicker.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-datepicker
 */

"use strict";
/* eslint-disable @typescript-eslint/no-unused-vars */

(function () {
  const d_week = new Datepicker(document.querySelector<HTMLElement>("#d_week")!, {
    buttonClass: "btn",
  });
})();

(function () {
  const d_highlight = new Datepicker(document.querySelector<HTMLElement>("#d_highlight")!, {
    buttonClass: "btn",
    daysOfWeekHighlighted: [1],
  });
})();

(function () {
  const d_auto = new Datepicker(document.querySelector<HTMLElement>("#d_auto")!, {
    buttonClass: "btn",
    autohide: true,
  });
})();

(function () {
  const d_disable = new Datepicker(document.querySelector<HTMLElement>("#d_disable")!, {
    buttonClass: "btn",
    datesDisabled: ["02/18/2022", "02/22/2022"],
  });
})();

(function () {
  const d_today = new Datepicker(document.querySelector<HTMLElement>("#d_today")!, {
    buttonClass: "btn",
    todayHighlight: true,
  });
})();

(function () {
  const disp_week = new Datepicker(document.querySelector<HTMLElement>("#disp_week")!, {
    buttonClass: "btn",
    calendarWeeks: true,
  });
})();

(function () {
  const datepicker_range = new DateRangePicker(document.querySelector<HTMLElement>("#datepicker_range")!, {
    buttonClass: "btn",
  });
})();

export {};
