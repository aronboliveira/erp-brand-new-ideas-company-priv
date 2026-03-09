/**
 * @file ac-slider.js
 * @description Tiny Slider (tns) configuration (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Slider controller using Tiny Slider
   * @class LandingSliderController
   */
  class LandingSliderController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-slider-init";
    /** @type {string} */
    static #CONTAINER_BASIC = ".basic-slider";
    /** @type {string} */
    static #CONTAINER_NAV = ".nav-slider";
    /** @type {string} */
    static #CONTAINER_PREVIEW = ".preview-slider";
    /** @type {Object[]} */
    #sliders = [];

    /**
     * Initialize sliders
     */
    init() {
      if (document.body?.hasAttribute(LandingSliderController.#DATA_INIT)) return;
      if (typeof tns === "undefined") return console.warn("[LandingSliderController] tns not loaded");

      document.body?.setAttribute(LandingSliderController.#DATA_INIT, "true");
      this.#setupSliders();
    }

    /**
     * Setup all slider configurations
     * @private
     */
    #setupSliders() {
      try {
        this.#setupBasicSlider();
        this.#setupNavSlider();
        this.#setupPreviewSlider();
      } catch (err) {
        console.error("[LandingSliderController] Error:", err);
      }
    }

    /**
     * Setup basic slider
     * @private
     */
    #setupBasicSlider() {
      const container = document.querySelector(LandingSliderController.#CONTAINER_BASIC);
      if (!container || container.hasAttribute("data-tns-applied")) return;
      container.setAttribute("data-tns-applied", "true");
      this.#sliders.push(tns({ container: LandingSliderController.#CONTAINER_BASIC, items: 1, slideBy: "page", autoplay: true }));
    }

    /**
     * Setup navigation slider
     * @private
     */
    #setupNavSlider() {
      const container = document.querySelector(LandingSliderController.#CONTAINER_NAV);
      if (!container || container.hasAttribute("data-tns-applied")) return;
      container.setAttribute("data-tns-applied", "true");
      this.#sliders.push(tns({ container: LandingSliderController.#CONTAINER_NAV, items: 1, slideBy: "page", autoplay: true, controls: true, nav: true }));
    }

    /**
     * Setup preview slider with responsive config
     * @private
     */
    #setupPreviewSlider() {
      const container = document.querySelector(LandingSliderController.#CONTAINER_PREVIEW);
      if (!container || container.hasAttribute("data-tns-applied")) return;
      container.setAttribute("data-tns-applied", "true");
      this.#sliders.push(tns({
        container: LandingSliderController.#CONTAINER_PREVIEW,
        items: 1,
        slideBy: "page",
        autoplay: true,
        responsive: { 640: { edgePadding: 20, gutter: 20, items: 2 }, 700: { gutter: 30 }, 900: { items: 3 } },
      }));
    }

    /**
     * Destroy all sliders
     */
    destroy() {
      this.#sliders.forEach((s) => s?.destroy?.());
      this.#sliders = [];
      document.body?.removeAttribute(LandingSliderController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initSliders = () => {
    try {
      new LandingSliderController().init();
    } catch (err) {
      console.error("[LandingSliderController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initSliders)
    : initSliders();
})();
