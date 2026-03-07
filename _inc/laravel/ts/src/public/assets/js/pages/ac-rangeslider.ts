/**
 * @fileoverview TypeScript version of public/assets/js/pages/ac-rangeslider.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-rangeslider
 */


"use strict";

interface SliderInstance {
  getValue: () => number;
  on: (event: string, callback: (value: unknown) => void) => SliderInstance;
  destroy: () => void;
  enable: () => void;
  disable: () => void;
}
declare let Slider: new (selector: string, options?: unknown) => SliderInstance;

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  try {
    // [ basic-Slider ]
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const slider = new Slider("#ex1", {
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      formatter: function (value: unknown) {
        // eslint-disable-next-line @typescript-eslint/restrict-plus-operands
        return "Current value: " + value;
      },
    });

    // [ Selector-Slider ]
    const sliderEx2 = new Slider("#ex2", {});
    const RGBChange = function (): void {
      const rgbEl = document.querySelector<HTMLElement>("#RGB");
      if (rgbEl) {
        rgbEl.style.background =
          "rgb(" + r.getValue() + "," + g.getValue() + "," + b.getValue() + ")";
      }
    };
    const r = new Slider("#R", { reversed: true }).on("slide", RGBChange);
    const g = new Slider("#G", { reversed: true }).on("slide", RGBChange);
    const b = new Slider("#B", { reversed: true }).on("slide", RGBChange);

    // [ vertical-slider ]
    const sliderEx4 = new Slider("#ex4", { reversed: true });
    void sliderEx2;
    void sliderEx4;

    // [ Destroy-Slider ]
    const sliderEx5 = new Slider("#ex5");
    document
      .querySelector<HTMLElement>("#destroyEx5Slider")
      ?.addEventListener("click", function (): void {
        sliderEx5.destroy();
      });

    // [ current-Slider ]
    const sliderEx6 = new Slider("#ex6");
    sliderEx6.on("slide", function (sliderValue) {
      const el = document.getElementById("ex6SliderVal");
      if (el) el.textContent = String(sliderValue);
    });

    // [ Enable-Slider ]
    const sliderEx7 = new Slider("#ex7");
    document
      .querySelector<HTMLInputElement>("#ex7-enabled")
      ?.addEventListener("click", function (this: HTMLInputElement): void {
        if (this.checked) sliderEx7.enable();
        else sliderEx7.disable();
      });

    // [ Tooltip-Slider ]
    const sliderEx8 = new Slider("#ex8", { tooltip: "always" });

    // [ Precision-slider ]
    const sliderEx9 = new Slider("#ex9", { precision: 2, value: 8.115 });

    // [ handlers-slider ]
    const sliderEx10 = new Slider("#ex10", {});

    // [ step-slider ]
    const sliderEx11 = new Slider("#ex11", { step: 20000, min: 0, max: 200000 });

    // [ low & high-slider ]
    const sliderA12 = new Slider("#ex12a", { id: "slider12a", min: 0, max: 10, value: 5 });
    const sliderB12 = new Slider("#ex12b", { id: "slider12b", min: 0, max: 10, range: true, value: [3, 7] });
    const sliderC12 = new Slider("#ex12c", { id: "slider12c", min: 0, max: 10, range: true, value: [3, 7] });

    // [ labels-slider ]
    const sliderEx13 = new Slider("#ex13", {
      ticks: [0, 10, 20, 30, 40],
      ticks_labels: ["$0", "$10", "$20", "$30", "$40"],
      ticks_snap_bounds: 95,
    });

    // [ positions-slider ]
    const sliderEx14 = new Slider("#ex14", {
      ticks: [0, 10, 20, 30, 40],
      ticks_positions: [0, 30, 60, 80, 100],
      ticks_labels: ["$0", "$10", "$20", "$30", "$40"],
      ticks_snap_bounds: 95,
    });

    // [ logarithmic-slider ]
    const sliderEx15 = new Slider("#ex15", { min: 1000, max: 10000000, scale: "logarithmic", step: 10 });

    // [ Focus-slider ]
    const sliderA16 = new Slider("#ex16a", { min: 0, max: 10, value: 0, focus: true });
    const sliderB16 = new Slider("#ex16b", { min: 0, max: 10, value: [0, 10], focus: true });

    // [ Unusual-slider ]
    const sliderA17 = new Slider("#ex17a", { min: 0, max: 10, value: 0, tooltip_position: "bottom" });
    const sliderB17 = new Slider("#ex17b", { min: 0, max: 10, value: 0, orientation: "vertical", tooltip_position: "left" });

    // [ Accessibility-slider ]
    const sliderA18 = new Slider("#ex18a", { min: 0, max: 10, value: 5, labelledby: "ex18-label-1" });
    const sliderB18 = new Slider("#ex18b", { min: 0, max: 10, value: [3, 6], labelledby: ["ex18-label-2a", "ex18-label-2b"] });

    // [ Highlight-slider ]
    const sliderEx22 = new Slider("#ex22", {
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

    // [ Tick-slider ]
    const sliderEx23 = new Slider("#ex23", {
      ticks: [0, 1, 2, 3, 4],
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      ticks_positions: [0, 30, 70, 90, 100],
      ticks_snap_bounds: 200,
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      formatter: function (value: unknown) {
        // eslint-disable-next-line @typescript-eslint/restrict-plus-operands
        return "Current value: " + value;
      },
      ticks_tooltip: true,
      step: 0.01,
    });

    // [ auto-slider ]
    const sliderEx24 = new Slider("#ex24");

    // Suppress unused variable warnings
    void slider;
    void sliderEx8;
    void sliderEx9;
    void sliderEx10;
    void sliderEx11;
    void sliderA12;
    void sliderB12;
    void sliderC12;
    void sliderEx13;
    void sliderEx14;
    void sliderEx15;
    void sliderA16;
    void sliderB16;
    void sliderA17;
    void sliderB17;
    void sliderA18;
    void sliderB18;
    void sliderEx22;
    void sliderEx23;
    void sliderEx24;
  } catch (__moduleErr) {
    console.error("[ac-rangeslider] failed to initialise:", __moduleErr);
  }
})();

export {};
