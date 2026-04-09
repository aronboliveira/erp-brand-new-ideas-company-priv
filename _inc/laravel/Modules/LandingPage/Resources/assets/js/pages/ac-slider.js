/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/ac-slider.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-slider
 */
"use strict";
/* eslint-disable @typescript-eslint/no-unused-vars */
setTimeout(function () {
    // [ slider ]
    const slider1 = tns({
        container: ".slider1",
        items: 1,
        slideBy: "page",
        autoplay: true,
    });
    // [ Only-Nav slider ]
    const slider2 = tns({
        container: ".slider2",
        items: 1,
        axis: "vertical",
        slideBy: "page",
        autoplay: true,
    });
    // [ Only-Dots slider ]
    const slider3 = tns({
        container: "#customize",
        items: 3,
        center: true,
        gutter: 10,
        controlsContainer: "#customize-controls",
        navContainer: "#customize-thumbnails",
        navAsThumbnails: true,
        autoplay: true,
        autoplayTimeout: 1000,
        autoplayButton: "#customize-toggle",
    });
});
//# sourceMappingURL=ac-slider.js.map