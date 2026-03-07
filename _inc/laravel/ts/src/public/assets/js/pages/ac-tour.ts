/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-tour.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-tour
 */

'use strict';
document.addEventListener("DOMContentLoaded", function (): void {
    introJs().setOptions({
        steps: [{
            intro: "Hello world!"
        }, {
            element: document.querySelector<HTMLElement>(".step1"),
            intro: "This is Card"
        }, {
            element: document.querySelector<HTMLElement>(".step2"),
            intro: "This is Card header"
        }, {
            element: document.querySelector<HTMLElement>(".step3"),
            intro: "This is Card Title"
        }, {
            element: document.querySelector<HTMLElement>(".step4"),
            intro: "This is Card Body"
        }]
    }).start();
});