/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-datepicker.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-datepicker
 */

/* global Datepicker, DateRangePicker */
"use strict";
(function () {
    const _d_week = new Datepicker(document.querySelector("#d_week"), {
        buttonClass: "btn",
    });
})();
(function () {
    const _d_highlight = new Datepicker(document.querySelector("#d_highlight"), {
        buttonClass: "btn",
        daysOfWeekHighlighted: [1],
    });
})();
(function () {
    const _d_auto = new Datepicker(document.querySelector("#d_auto"), {
        buttonClass: "btn",
        autohide: true,
    });
})();
(function () {
    const _d_disable = new Datepicker(document.querySelector("#d_disable"), {
        buttonClass: "btn",
        datesDisabled: ["02/18/2022", "02/22/2022"],
    });
})();
// $('#d_toggle').datepicker({
//     keyboardNavigation: false,
//     forceParse: false,
//     toggleActive: true
// });
(function () {
    const _d_today = new Datepicker(document.querySelector("#d_today"), {
        buttonClass: "btn",
        todayHighlight: true,
    });
})();
(function () {
    const _disp_week = new Datepicker(document.querySelector("#disp_week"), {
        buttonClass: "btn",
        calendarWeeks: true,
    });
})();
(function () {
    const el = document.querySelector("#datepicker_range");
    const _datepicker_range = el
        ? new DateRangePicker(el, {
            buttonClass: "btn",
        })
        : null;
})();
//# sourceMappingURL=ac-datepicker.js.map