/**
 * @file form-validation.js
 * @description Bouncer form validation configuration
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Form validation controller using Bouncer
   * @class FormValidationController
   */
  class FormValidationController {
    /** @type {string} */
    static #DATA_INIT = "data-validation-init";
    /** @type {Bouncer|null} */
    #bouncer = null;

    /**
     * Initialize form validation
     */
    init() {
      if (document.body?.hasAttribute(FormValidationController.#DATA_INIT)) return;
      if (typeof Bouncer === "undefined") return console.warn("[FormValidationController] Bouncer not loaded");

      document.body?.setAttribute(FormValidationController.#DATA_INIT, "true");
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
        console.error("[FormValidationController] Error initializing Bouncer:", err);
      }
    }

    /**
     * Destroy bouncer instance
     */
    destroy() {
      this.#bouncer?.destroy?.();
      document.body?.removeAttribute(FormValidationController.#DATA_INIT);
    }
  }

  /**
   * Initialize form validation when DOM ready
   */
  const initFormValidation = () => {
    try {
      new FormValidationController().init();
    } catch (err) {
      console.error("[FormValidationController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initFormValidation)
    : initFormValidation();
})();
