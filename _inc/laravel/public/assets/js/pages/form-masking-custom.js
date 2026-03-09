/**
 * @file form-masking-custom.js
 * @description Input masking configuration with IMask
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Input masking controller using IMask
   * @class MaskingController
   */
  class MaskingController {
    /** @type {string} */
    static #DATA_INIT = "data-masking-init";
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
      if (document.body?.hasAttribute(MaskingController.#DATA_INIT)) return;
      if (typeof IMask === "undefined") return console.warn("[MaskingController] IMask not loaded");

      document.body?.setAttribute(MaskingController.#DATA_INIT, "true");
      this.#setupMasks();
    }

    /**
     * Setup input masks for IP and date fields
     * @private
     */
    #setupMasks() {
      try {
        this.#setupIPMasks();
        this.#setupDateMasks();
      } catch (err) {
        console.error("[MaskingController] Error setting up masks:", err);
      }
    }

    /**
     * Setup IP address masks
     * @private
     */
    #setupIPMasks() {
      const ipElements = document.querySelectorAll(MaskingController.#SELECTOR_IP);
      ipElements.forEach((el) => {
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
      const dateElements = document.querySelectorAll(MaskingController.#SELECTOR_DATE);
      dateElements.forEach((el) => {
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
      document.body?.removeAttribute(MaskingController.#DATA_INIT);
    }
  }

  /**
   * Initialize masking when DOM ready
   */
  const initMasking = () => {
    try {
      new MaskingController().init();
    } catch (err) {
      console.error("[MaskingController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initMasking)
    : initMasking();
})();
