/**
 * @file ac-datepicker.js
 * @description Datepicker configuration with multiple variants
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Datepicker controller using Datepicker library
   * @class DatepickerController
   */
  class DatepickerController {
    /** @type {string} */
    static #DATA_INIT = "data-datepicker-init";
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
      if (document.body?.hasAttribute(DatepickerController.#DATA_INIT)) return;
      if (typeof Datepicker === "undefined") return console.warn("[DatepickerController] Datepicker not loaded");

      document.body?.setAttribute(DatepickerController.#DATA_INIT, "true");
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
        console.error("[DatepickerController] Error setting up pickers:", err);
      }
    }

    /**
     * Setup inline datepicker
     * @private
     */
    #setupInlinePicker() {
      const el = document.querySelector(DatepickerController.#SELECTOR_INLINE);
      if (!el || el.hasAttribute("data-picker-applied")) return;
      el.setAttribute("data-picker-applied", "true");
      this.#pickers.push(new Datepicker(el, { autohide: true }));
    }

    /**
     * Setup date range picker
     * @private
     */
    #setupRangePicker() {
      const el = document.querySelector(DatepickerController.#SELECTOR_RANGE);
      if (!el || el.hasAttribute("data-picker-applied")) return;
      el.setAttribute("data-picker-applied", "true");
      const range = new DateRangePicker(el, { autohide: true });
      this.#pickers.push(range);
    }

    /**
     * Setup multi-date picker
     * @private
     */
    #setupMultiPicker() {
      const el = document.querySelector(DatepickerController.#SELECTOR_MULTI);
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
      document.body?.removeAttribute(DatepickerController.#DATA_INIT);
    }
  }

  /**
   * Initialize datepickers when DOM ready
   */
  const initDatepicker = () => {
    try {
      new DatepickerController().init();
    } catch (err) {
      console.error("[DatepickerController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initDatepicker)
    : initDatepicker();
})();
