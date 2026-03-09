/**
 * @file ac-datepicker.js
 * @description Datepicker configuration (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Datepicker controller
   * @class LandingDatepickerController
   */
  class LandingDatepickerController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-datepicker-init";
    /** @type {string} */
    static #SELECTOR_INLINE = "#inline-datepicker";
    /** @type {string} */
    static #SELECTOR_RANGE = "#date-range";
    /** @type {string} */
    static #SELECTOR_MULTI = "#multi-date";
    /** @type {Object[]} */
    #pickers = [];

    /**
     * Initialize datepickers
     */
    init() {
      if (document.body?.hasAttribute(LandingDatepickerController.#DATA_INIT)) return;
      if (typeof Datepicker === "undefined") return console.warn("[LandingDatepickerController] Datepicker not loaded");

      document.body?.setAttribute(LandingDatepickerController.#DATA_INIT, "true");
      this.#setupPickers();
    }

    /**
     * Setup all datepicker variants
     * @private
     */
    #setupPickers() {
      try {
        this.#setupInlinePicker();
        this.#setupRangePicker();
        this.#setupMultiPicker();
      } catch (err) {
        console.error("[LandingDatepickerController] Error:", err);
      }
    }

    /**
     * Setup inline datepicker
     * @private
     */
    #setupInlinePicker() {
      const el = document.querySelector(LandingDatepickerController.#SELECTOR_INLINE);
      if (!el || el.hasAttribute("data-picker-applied")) return;
      el.setAttribute("data-picker-applied", "true");
      this.#pickers.push(new Datepicker(el, { autohide: true }));
    }

    /**
     * Setup range picker
     * @private
     */
    #setupRangePicker() {
      const el = document.querySelector(LandingDatepickerController.#SELECTOR_RANGE);
      if (!el || el.hasAttribute("data-picker-applied")) return;
      el.setAttribute("data-picker-applied", "true");
      this.#pickers.push(new DateRangePicker(el, { autohide: true }));
    }

    /**
     * Setup multi-date picker
     * @private
     */
    #setupMultiPicker() {
      const el = document.querySelector(LandingDatepickerController.#SELECTOR_MULTI);
      if (!el || el.hasAttribute("data-picker-applied")) return;
      el.setAttribute("data-picker-applied", "true");
      this.#pickers.push(new Datepicker(el, { autohide: true, maxNumberOfDates: 4 }));
    }

    /**
     * Destroy all pickers
     */
    destroy() {
      this.#pickers.forEach((p) => p?.destroy?.());
      this.#pickers = [];
      document.body?.removeAttribute(LandingDatepickerController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initDatepicker = () => {
    try {
      new LandingDatepickerController().init();
    } catch (err) {
      console.error("[LandingDatepickerController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initDatepicker)
    : initDatepicker();
})();
