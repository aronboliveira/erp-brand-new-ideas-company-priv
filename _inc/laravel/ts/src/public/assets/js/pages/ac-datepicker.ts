/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-datepicker.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-datepicker
 */
/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unused-vars */

/* global $, jQuery */
'use strict';

(function (): void {
    const d_week = new Datepicker(document.querySelector<HTMLElement>("#d_week"), {
        buttonClass: 'btn',
    });
})();
(function (): void {
    const d_highlight = new Datepicker(document.querySelector<HTMLElement>("#d_highlight"), {
        buttonClass: 'btn',
        daysOfWeekHighlighted: [1],
    });
})();

(function (): void {
    const d_auto = new Datepicker(document.querySelector<HTMLElement>("#d_auto"), {
        buttonClass: 'btn',
        autohide: true
    });
})();

(function (): void {
    const d_disable = new Datepicker(document.querySelector<HTMLElement>("#d_disable"), {
        buttonClass: 'btn',
        datesDisabled: ["02/18/2022", "02/22/2022"]
    });
})();

// $('#d_toggle').datepicker({
//     keyboardNavigation: false,
//     forceParse: false,
//     toggleActive: true
// });

(function (): void {
    const d_today = new Datepicker(document.querySelector<HTMLElement>("#d_today"), {
        buttonClass: 'btn',
        todayHighlight: true
    });
})();

(function (): void {
    const disp_week = new Datepicker(document.querySelector<HTMLElement>("#disp_week"), {
        buttonClass: 'btn',
        calendarWeeks: true
    });
})();

(function (): void {
    const datepicker_range = new DateRangePicker(document.querySelector<HTMLElement>("#datepicker_range"), {
        buttonClass: 'btn',
    });
})();
