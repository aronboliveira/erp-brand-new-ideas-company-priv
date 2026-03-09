/**
 * @file ac-notification.js
 * @description Notifier demonstration page controller
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Notifier demo page manager
   * @class NotifierDemoController
   */
  class NotifierDemoController {
    /** @type {string} */
    static #DATA_INIT = "data-notifier-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-notifier-listener";

    /**
     * Initialize notifier demos
     */
    init() {
      if (document.body?.hasAttribute(NotifierDemoController.#DATA_INIT)) return;
      if (typeof notifier === "undefined") return console.warn("[NotifierDemoController] notifier not loaded");

      document.body?.setAttribute(NotifierDemoController.#DATA_INIT, "true");
      this.#setupDemos();
    }

    /**
     * Bind click handler to element with guard
     * @param {string} selector
     * @param {Function} handler
     * @private
     */
    #bind(selector, handler) {
      const el = document.querySelector(selector);
      if (!el || el.hasAttribute(NotifierDemoController.#DATA_LISTENER)) return;
      el.setAttribute(NotifierDemoController.#DATA_LISTENER, "true");
      el.addEventListener("click", handler);
    }

    /**
     * Setup notification demos
     * @private
     */
    #setupDemos() {
      this.#bind(".ntf-dflt", () => notifier.show("Default", "This is a default message", "default", "", 4000));
      this.#bind(".ntf-inf", () => notifier.show("Info", "This is an info message", "info", "", 4000));
      this.#bind(".ntf-wrng", () => notifier.show("Warning", "This is a warning message", "warning", "", 4000));
      this.#bind(".ntf-err", () => notifier.show("Error", "This is an error message", "error", "", 4000));
      this.#bind(".ntf-scs", () => notifier.show("Success", "This is a success message", "success", "", 4000));

      this.#bind(".ntf-tl", () => notifier.show("Top Left", "This is a message in top left", "success", "top-left", 4000));
      this.#bind(".ntf-tc", () => notifier.show("Top Center", "This is a message in top center", "success", "top-center", 4000));
      this.#bind(".ntf-tr", () => notifier.show("Top Right", "This is a message in top right", "success", "top-right", 4000));
      this.#bind(".ntf-bl", () => notifier.show("Bottom Left", "This is a message in bottom left", "success", "bottom-left", 4000));
      this.#bind(".ntf-bc", () => notifier.show("Bottom Center", "This is a message in bottom center", "success", "bottom-center", 4000));
      this.#bind(".ntf-br", () => notifier.show("Bottom Right", "This is a message in bottom right", "success", "bottom-right", 4000));
    }
  }

  /**
   * Initialize notifier demos when DOM ready
   */
  const initNotifierDemos = () => {
    try {
      new NotifierDemoController().init();
    } catch (err) {
      console.error("[NotifierDemoController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initNotifierDemos)
    : initNotifierDemos();
})();
