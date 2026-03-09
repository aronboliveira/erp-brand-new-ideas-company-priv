/**
 * @file form-validation.js
 * @description Bouncer form validation configuration (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Form validation controller using Bouncer
   * @class LandingFormValidationController
   */
  class LandingFormValidationController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-validation-init";
    /** @type {Bouncer|null} */
    #bouncer = null;

    /**
     * Initialize form validation
     */
    init() {
      if (document.body?.hasAttribute(LandingFormValidationController.#DATA_INIT)) return;
      if (typeof Bouncer === "undefined") return console.warn("[LandingFormValidationController] Bouncer not loaded");

      document.body?.setAttribute(LandingFormValidationController.#DATA_INIT, "true");
      this.#setupBouncer();
    }

    /**
     * Setup Bouncer validation
     * @private
     */
    #setupBouncer() {
      try {
        this.#bouncer = new Bouncer("[data-bouncer]", {
          disableSubmit: true,
          customValidations: {
            valueMismatch: (field) => {
              const selector = field.getAttribute("data-bouncer-match");
              if (!selector) return false;
              const otherField = field.form?.querySelector(selector);
              return !otherField || otherField.value !== field.value;
            },
          },
        });
      } catch (err) {
        console.error("[LandingFormValidationController] Error:", err);
      }
    }

    /**
     * Destroy bouncer instance
     */
    destroy() {
      this.#bouncer?.destroy?.();
      document.body?.removeAttribute(LandingFormValidationController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initFormValidation = () => {
    try {
      new LandingFormValidationController().init();
    } catch (err) {
      console.error("[LandingFormValidationController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initFormValidation)
    : initFormValidation();
})();
