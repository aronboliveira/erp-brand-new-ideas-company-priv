/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-slider.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-slider
 */
"use strict";

setTimeout(function () {
  // [ slider ]
  var _slider = tns({
    container: ".slider1",
    items: 1,
    slideBy: "page",
    autoplay: true,
  });
  // [ Only-Nav slider ]
  var _slider = tns({
    container: ".slider2",
    items: 1,
    axis: "vertical",
    slideBy: "page",
    autoplay: true,
  });
  // [ Only-Dots slider ]
  var _slider = tns({
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
