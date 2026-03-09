/**
 * @file ac-notification.js
 * @description Notifier library demonstration controller (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Notifier demo controller
   * @class LandingNotifierDemoController
   */
  class LandingNotifierDemoController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-notifier-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-lp-notifier-listener";

    /**
     * Initialize notifier demos
     */
    init() {
      if (document.body?.hasAttribute(LandingNotifierDemoController.#DATA_INIT)) return;
      if (typeof notifier === "undefined") return console.warn("[LandingNotifierDemoController] notifier not loaded");

      document.body?.setAttribute(LandingNotifierDemoController.#DATA_INIT, "true");
      this.#setupDemos();
    }

    /**
     * Bind click handler to element
     * @private
     * @param {string} selector
     * @param {Function} handler
     */
    #bind(selector, handler) {
      const el = document.querySelector(selector);
      if (!el || el.hasAttribute(LandingNotifierDemoController.#DATA_LISTENER)) return;
      el.setAttribute(LandingNotifierDemoController.#DATA_LISTENER, "true");
      el.addEventListener("click", handler);
    }

    /**
     * Setup all demo buttons
     * @private
     */
    #setupDemos() {
      try {
        const positions = [
          { id: "ntf-demo-1", pos: "top-left" },
          { id: "ntf-demo-2", pos: "top-center" },
          { id: "ntf-demo-3", pos: "top-right" },
          { id: "ntf-demo-4", pos: "bottom-left" },
          { id: "ntf-demo-5", pos: "bottom-center" },
          { id: "ntf-demo-6", pos: "bottom-right" },
        ];
        positions.forEach(({ id, pos }) => {
          this.#bind(`#${id}`, () => notifier.show("Success", `Notification at ${pos}`, "success", "", pos, 5000));
        });
      } catch (err) {
        console.error("[LandingNotifierDemoController] Error:", err);
      }
    }

    /**
     * Destroy controller
     */
    destroy() {
      document.body?.removeAttribute(LandingNotifierDemoController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initNotifierDemo = () => {
    try {
      new LandingNotifierDemoController().init();
    } catch (err) {
      console.error("[LandingNotifierDemoController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initNotifierDemo)
    : initNotifierDemo();
})();
