/**
 * @file form-masking-custom.js
 * @description Input masking configuration with IMask (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Input masking controller using IMask
   * @class LandingMaskingController
   */
  class LandingMaskingController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-masking-init";
    /** @type {string} */
    static #SELECTOR_IP = ".ip-mask";
    /** @type {string} */
    static #SELECTOR_DATE = ".date-mask";
    /** @type {IMask.InputMask[]} */
    #masks = [];

    /**
     * Initialize input masks
     */
    init() {
      if (document.body?.hasAttribute(LandingMaskingController.#DATA_INIT)) return;
      if (typeof IMask === "undefined") return console.warn("[LandingMaskingController] IMask not loaded");

      document.body?.setAttribute(LandingMaskingController.#DATA_INIT, "true");
      this.#setupMasks();
    }

    /**
     * Setup input masks
     * @private
     */
    #setupMasks() {
      try {
        this.#setupIPMasks();
        this.#setupDateMasks();
      } catch (err) {
        console.error("[LandingMaskingController] Error:", err);
      }
    }

    /**
     * Setup IP address masks
     * @private
     */
    #setupIPMasks() {
      document.querySelectorAll(LandingMaskingController.#SELECTOR_IP).forEach((el) => {
        if (el.hasAttribute("data-mask-applied")) return;
        el.setAttribute("data-mask-applied", "true");
        this.#masks.push(IMask(el, { mask: "000.000.000.000" }));
      });
    }

    /**
     * Setup date masks
     * @private
     */
    #setupDateMasks() {
      document.querySelectorAll(LandingMaskingController.#SELECTOR_DATE).forEach((el) => {
        if (el.hasAttribute("data-mask-applied")) return;
        el.setAttribute("data-mask-applied", "true");
        this.#masks.push(IMask(el, { mask: "00-00-0000" }));
      });
    }

    /**
     * Destroy all masks
     */
    destroy() {
      this.#masks.forEach((m) => m?.destroy?.());
      this.#masks = [];
      document.body?.removeAttribute(LandingMaskingController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initMasking = () => {
    try {
      new LandingMaskingController().init();
    } catch (err) {
      console.error("[LandingMaskingController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initMasking)
    : initMasking();
})();
