/**
 * @file ac-tour.js
 * @description Intro.js guided tour configuration
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Tour controller using Intro.js
   * @class TourController
   */
  class TourController {
    /** @type {string} */
    static #DATA_INIT = "data-tour-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-tour-listener";
    /** @type {string} */
    static #BTN_SELECTOR = "#tour-start";
    /** @type {introJs.IntroJs|null} */
    #intro = null;

    /**
     * Initialize tour controller
     */
    init() {
      if (document.body?.hasAttribute(TourController.#DATA_INIT)) return;
      if (typeof introJs === "undefined") return console.warn("[TourController] introJs not loaded");

      document.body?.setAttribute(TourController.#DATA_INIT, "true");
      this.#setupTour();
    }

    /**
     * Setup tour configuration and trigger
     * @private
     */
    #setupTour() {
      try {
        this.#intro = introJs();
        this.#intro.setOptions({
          showProgress: true,
          showBullets: true,
          steps: this.#getSteps(),
        });
        this.#bindTrigger();
      } catch (err) {
        console.error("[TourController] Error setting up tour:", err);
      }
    }

    /**
     * Get tour step definitions
     * @private
     * @returns {Object[]}
     */
    #getSteps() {
      return [
        { element: "#step-1", intro: "Welcome to the application tour!" },
        { element: "#step-2", intro: "This is the second step of the tour." },
        { element: "#step-3", intro: "Final step - you're all set!" },
      ];
    }

    /**
     * Bind click handler to start button
     * @private
     */
    #bindTrigger() {
      const btn = document.querySelector(TourController.#BTN_SELECTOR);
      if (!btn || btn.hasAttribute(TourController.#DATA_LISTENER)) return;
      btn.setAttribute(TourController.#DATA_LISTENER, "true");
      btn.addEventListener("click", () => this.start());
    }

    /**
     * Start the tour
     */
    start() {
      this.#intro?.start?.();
    }

    /**
     * Exit the tour
     */
    exit() {
      this.#intro?.exit?.();
    }

    /**
     * Destroy tour instance
     */
    destroy() {
      this.#intro = null;
      document.body?.removeAttribute(TourController.#DATA_INIT);
    }
  }

  /**
   * Initialize tour when DOM ready
   */
  const initTour = () => {
    try {
      new TourController().init();
    } catch (err) {
      console.error("[TourController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initTour)
    : initTour();
})();
