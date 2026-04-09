/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/ac-datepicker.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-datepicker
 */
"use strict";
/* eslint-disable @typescript-eslint/no-unused-vars */
(function () {
    const d_week = new Datepicker(document.querySelector("#d_week"), {
        buttonClass: "btn",
    });
})();
(function () {
    const d_highlight = new Datepicker(document.querySelector("#d_highlight"), {
        buttonClass: "btn",
        daysOfWeekHighlighted: [1],
    });
})();
(function () {
    const d_auto = new Datepicker(document.querySelector("#d_auto"), {
        buttonClass: "btn",
        autohide: true,
    });
})();
(function () {
    const d_disable = new Datepicker(document.querySelector("#d_disable"), {
        buttonClass: "btn",
        datesDisabled: ["02/18/2022", "02/22/2022"],
    });
})();
(function () {
    const d_today = new Datepicker(document.querySelector("#d_today"), {
        buttonClass: "btn",
        todayHighlight: true,
    });
})();
(function () {
    const disp_week = new Datepicker(document.querySelector("#disp_week"), {
        buttonClass: "btn",
        calendarWeeks: true,
    });
})();
(function () {
    const datepicker_range = new DateRangePicker(document.querySelector("#datepicker_range"), {
        buttonClass: "btn",
    });
})();
//# sourceMappingURL=ac-datepicker.js.map