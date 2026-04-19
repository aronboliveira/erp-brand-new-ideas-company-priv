/**
 * @fileoverview TypeScript version of Modules/LandingPage/Resources/assets/js/pages/ac-rangeslider.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-rangeslider
 */

"use strict";
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

// [ basic-Slider ]
(function () {
  const slider = new Slider("#ex1", {
    formatter: function (value: number | [number, number]): string {
      return "Current value: " + String(value);
    },
  });
})();

// [ Selector-Slider ]
(function () {
  const slider = new Slider("#ex2", {});
  const RGBChange = function (): void {
    const rgb = document.querySelector<HTMLElement>("#RGB");
    if (rgb) {
      rgb.style.background = "rgb(" + r.getValue() + "," + g.getValue() + "," + b.getValue() + ")";
    }
  };
  const r = new Slider("#R", { reversed: true }).on("slide", RGBChange);
  const g = new Slider("#G", { reversed: true }).on("slide", RGBChange);
  const b = new Slider("#B", { reversed: true }).on("slide", RGBChange);

  // [ vertical-slider ]
  const sliderV = new Slider("#ex4", { reversed: true });
})();

// [ Destroy-Slider ]
(function () {
  const slider = new Slider("#ex5");
  const destroyBtn = document.querySelector<HTMLElement>("#destroyEx5Slider");
  if (destroyBtn) {
    destroyBtn.addEventListener("click", function () {
      slider.destroy();
    });
  }
})();

// [ current-Slider ]
(function () {
  const slider = new Slider("#ex6");
  slider.on("slide", function (sliderValue: number | [number, number]): void {
    const el = document.getElementById("ex6SliderVal");
    if (el) el.textContent = String(sliderValue);
  });
})();

// [ Enable-Slider ]
(function () {
  const slider = new Slider("#ex7");
  const enabledBtn = document.querySelector<HTMLInputElement>("#ex7-enabled");
  if (enabledBtn) {
    enabledBtn.addEventListener("click", function () {
      if ((this as HTMLInputElement).checked) {
        slider.enable();
      } else {
        slider.disable();
      }
    });
  }
})();

// [ Tooltip-Slider ]
(function () {
  const slider = new Slider("#ex8", { tooltip: "always" });
})();

// [ Precision-slider ]
(function () {
  const slider = new Slider("#ex9", {
    precision: 2,
    value: 8.115,
  });
})();

// [ handlers-slider ]
(function () {
  const slider = new Slider("#ex10", {});
})();

// [ step-slider ]
(function () {
  const slider = new Slider("#ex11", {
    step: 20000,
    min: 0,
    max: 200000,
  });
})();

// [ low & high-slider ]
(function () {
  const sliderA = new Slider("#ex12a", {
    id: "slider12a",
    min: 0,
    max: 10,
    value: 5,
  });
  const sliderB = new Slider("#ex12b", {
    id: "slider12b",
    min: 0,
    max: 10,
    range: true,
    value: [3, 7],
  });
  const sliderC = new Slider("#ex12c", {
    id: "slider12c",
    min: 0,
    max: 10,
    range: true,
    value: [3, 7],
  });
})();

// [ labels-slider ]
(function () {
  const slider = new Slider("#ex13", {
    ticks: [0, 10, 20, 30, 40],
    ticks_labels: ["$0", "$10", "$20", "$30", "$40"],
    ticks_snap_bounds: 95,
  });
})();

// [ positions-slider ]
(function () {
  const slider = new Slider("#ex14", {
    ticks: [0, 10, 20, 30, 40],
    ticks_positions: [0, 30, 60, 80, 100],
    ticks_labels: ["$0", "$10", "$20", "$30", "$40"],
    ticks_snap_bounds: 95,
  });
})();

// [ logarithmic-slider ]
(function () {
  const slider = new Slider("#ex15", {
    min: 1000,
    max: 10000000,
    scale: "logarithmic",
    step: 10,
  });
})();

// [ Focus-slider ]
(function () {
  const sliderA = new Slider("#ex16a", {
    min: 0,
    max: 10,
    value: 0,
    focus: true,
  });
  const sliderB = new Slider("#ex16b", {
    min: 0,
    max: 10,
    value: [0, 10],
    focus: true,
  });
})();

// [ Unusual-slider ]
(function () {
  const sliderA = new Slider("#ex17a", {
    min: 0,
    max: 10,
    value: 0,
    tooltip_position: "bottom",
  });
  const sliderB = new Slider("#ex17b", {
    min: 0,
    max: 10,
    value: 0,
    orientation: "vertical",
    tooltip_position: "left",
  });
})();

// [ Accessibility-slider ]
(function () {
  const sliderA = new Slider("#ex18a", {
    min: 0,
    max: 10,
    value: 5,
    labelledby: "ex18-label-1",
  });
  const sliderB = new Slider("#ex18b", {
    min: 0,
    max: 10,
    value: [3, 6],
    labelledby: ["ex18-label-2a", "ex18-label-2b"],
  });
})();

// [ Highlight-slider ]
(function () {
  const slider = new Slider("#ex22", {
    id: "slider22",
    min: 0,
    max: 20,
    step: 1,
    value: 14,
    rangeHighlights: [
      { start: 2, end: 5, class: "category1" },
      { start: 7, end: 8, class: "category2" },
      { start: 17, end: 19 },
      { start: 17, end: 24 },
      { start: -3, end: 19 },
    ],
  });
})();

// [ Tick-slider ]
(function () {
  const slider = new Slider("#ex23", {
    ticks: [0, 1, 2, 3, 4],
    ticks_positions: [0, 30, 70, 90, 100],
    ticks_snap_bounds: 200,
    formatter: function (value: number | [number, number]): string {
      return "Current value: " + String(value);
    },
    ticks_tooltip: true,
    step: 0.01,
  });
})();

// [ auto-slider ]
(function () {
  const slider = new Slider("#ex24");
})();

export {};
