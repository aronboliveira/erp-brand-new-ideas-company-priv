/**
 * @file ac-rangeslider.js
 * @description Bootstrap Slider range slider configurations (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Range slider controller using Bootstrap Slider
   * @class LandingRangeSliderController
   */
  class LandingRangeSliderController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-rangeslider-init";
    /** @type {string} */
    static #DATA_APPLIED = "data-slider-applied";
    /** @type {Object.<string, Object>} */
    static #CONFIGS = {
      "#st1": { value: 5, tooltip: "hide" },
      "#st2": { min: -5, max: 20, value: 5, formatter: (v) => `Current value: ${v}` },
      "#st3": { value: [14, 75], range: true, tooltip_split: true },
      "#st4": { min: 0, max: 10, value: [3, 7], range: true },
      "#st5": { ticks: [0, 100, 200, 300, 400], ticks_labels: ["$0", "$100", "$200", "$300", "$400"], ticks_snap_bounds: 30 },
      "#st6": { ticks: [0, 1, 2, 3, 4], ticks_positions: [0, 30, 60, 70, 90, 100], ticks_labels: ["0", "1", "2", "3", "4"], ticks_snap_bounds: 20 },
      "#st7": { reversed: true },
      "#st8": { reversed: true },
      "#st9": { tooltip: "always" },
      "#st10": { tooltip: "always", orientation: "vertical" },
      "#st11": { min: 0, max: 10, step: 0.1, value: 5.5, precision: 2 },
      "#st12": { tooltip: "always", orientation: "vertical" },
      "#st13": { id: "slider12a", min: 0, max: 10, value: 5, tooltip_position: "left", orientation: "vertical" },
      "#st14": { min: 0, max: 10, value: [3, 7], tooltip: "always", tooltip_position: "bottom" },
      "#st15": { value: 5, enabled: false },
      "#st16": { value: [2, 4, 6, 8], ticks: [0, 2, 4, 6, 8, 10] },
      "#st17": { natural_arrow_keys: true, tooltip: "always" },
    };
    /** @type {Slider[]} */
    #sliders = [];

    /**
     * Initialize range sliders
     */
    init() {
      if (document.body?.hasAttribute(LandingRangeSliderController.#DATA_INIT)) return;
      if (typeof Slider === "undefined") return console.warn("[LandingRangeSliderController] Slider not loaded");

      document.body?.setAttribute(LandingRangeSliderController.#DATA_INIT, "true");
      this.#setupSliders();
    }

    /**
     * Setup all slider configurations
     * @private
     */
    #setupSliders() {
      try {
        Object.entries(LandingRangeSliderController.#CONFIGS).forEach(([selector, config]) => {
          this.#createSlider(selector, config);
        });
      } catch (err) {
        console.error("[LandingRangeSliderController] Error:", err);
      }
    }

    /**
     * Create single slider instance
     * @private
     * @param {string} selector
     * @param {Object} config
     */
    #createSlider(selector, config) {
      const el = document.querySelector(selector);
      if (!el || el.hasAttribute(LandingRangeSliderController.#DATA_APPLIED)) return;
      el.setAttribute(LandingRangeSliderController.#DATA_APPLIED, "true");
      this.#sliders.push(new Slider(selector, config));
    }

    /**
     * Destroy all slider instances
     */
    destroy() {
      this.#sliders.forEach((s) => s?.destroy?.());
      this.#sliders = [];
      document.body?.removeAttribute(LandingRangeSliderController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initRangeSliders = () => {
    try {
      new LandingRangeSliderController().init();
    } catch (err) {
      console.error("[LandingRangeSliderController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initRangeSliders)
    : initRangeSliders();
})();
