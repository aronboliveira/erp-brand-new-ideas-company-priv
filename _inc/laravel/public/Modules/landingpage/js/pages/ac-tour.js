/**
 * @file ac-tour.js
 * @description Intro.js guided tour configuration (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Tour controller using Intro.js
   * @class LandingTourController
   */
  class LandingTourController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-tour-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-lp-tour-listener";
    /** @type {string} */
    static #BTN_SELECTOR = "#tour-start";
    /** @type {introJs.IntroJs|null} */
    #intro = null;

    /**
     * Initialize tour
     */
    init() {
      if (document.body?.hasAttribute(LandingTourController.#DATA_INIT)) return;
      if (typeof introJs === "undefined") return console.warn("[LandingTourController] introJs not loaded");

      document.body?.setAttribute(LandingTourController.#DATA_INIT, "true");
      this.#setupTour();
    }

    /**
     * Setup tour configuration
     * @private
     */
    #setupTour() {
      try {
        this.#intro = introJs();
        this.#intro.setOptions({
          showProgress: true,
          showBullets: true,
          steps: [
            { element: "#step-1", intro: "Welcome to the application tour!" },
            { element: "#step-2", intro: "This is the second step of the tour." },
            { element: "#step-3", intro: "Final step - you're all set!" },
          ],
        });
        this.#bindTrigger();
      } catch (err) {
        console.error("[LandingTourController] Error:", err);
      }
    }

    /**
     * Bind click handler to start button
     * @private
     */
    #bindTrigger() {
      const btn = document.querySelector(LandingTourController.#BTN_SELECTOR);
      if (!btn || btn.hasAttribute(LandingTourController.#DATA_LISTENER)) return;
      btn.setAttribute(LandingTourController.#DATA_LISTENER, "true");
      btn.addEventListener("click", () => this.start());
    }

    /** Start the tour */
    start() {
      this.#intro?.start?.();
    }

    /** Exit the tour */
    exit() {
      this.#intro?.exit?.();
    }

    /** Destroy tour instance */
    destroy() {
      this.#intro = null;
      document.body?.removeAttribute(LandingTourController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initTour = () => {
    try {
      new LandingTourController().init();
    } catch (err) {
      console.error("[LandingTourController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initTour)
    : initTour();
})();
