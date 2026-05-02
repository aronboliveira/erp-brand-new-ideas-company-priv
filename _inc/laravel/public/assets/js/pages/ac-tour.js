/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-tour.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-tour
 */
"use strict";

document.addEventListener("DOMContentLoaded", function () {
    introJs()
        .setOptions({
        steps: [
            {
                intro: "Hello world!",
            },
            {
                element: document.querySelector(".step1"),
                intro: "This is Card",
            },
            {
                element: document.querySelector(".step2"),
                intro: "This is Card header",
            },
            {
                element: document.querySelector(".step3"),
                intro: "This is Card Title",
            },
            {
                element: document.querySelector(".step4"),
                intro: "This is Card Body",
            },
        ],
    })
        .start();
});
//# sourceMappingURL=ac-tour.js.map