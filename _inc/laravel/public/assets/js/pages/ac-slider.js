/**
 * @file ac-slider.js
 * @description Tiny Slider (tns) configuration
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Slider controller using Tiny Slider
   * @class SliderController
   */
  class SliderController {
    /** @type {string} */
    static #DATA_INIT = "data-slider-init";
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
      if (document.body?.hasAttribute(SliderController.#DATA_INIT)) return;
      if (typeof tns === "undefined") return console.warn("[SliderController] tns not loaded");

      document.body?.setAttribute(SliderController.#DATA_INIT, "true");
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
        console.error("[SliderController] Error setting up sliders:", err);
      }
    }

    /**
     * Setup basic slider
     * @private
     */
    #setupBasicSlider() {
      const container = document.querySelector(SliderController.#CONTAINER_BASIC);
      if (!container || container.hasAttribute("data-tns-applied")) return;
      container.setAttribute("data-tns-applied", "true");
      this.#sliders.push(
        tns({
          container: SliderController.#CONTAINER_BASIC,
          items: 1,
          slideBy: "page",
          autoplay: true,
        })
      );
    }

    /**
     * Setup navigation slider
     * @private
     */
    #setupNavSlider() {
      const container = document.querySelector(SliderController.#CONTAINER_NAV);
      if (!container || container.hasAttribute("data-tns-applied")) return;
      container.setAttribute("data-tns-applied", "true");
      this.#sliders.push(
        tns({
          container: SliderController.#CONTAINER_NAV,
          items: 1,
          slideBy: "page",
          autoplay: true,
          controls: true,
          nav: true,
        })
      );
    }

    /**
     * Setup preview slider with responsive configuration
     * @private
     */
    #setupPreviewSlider() {
      const container = document.querySelector(SliderController.#CONTAINER_PREVIEW);
      if (!container || container.hasAttribute("data-tns-applied")) return;
      container.setAttribute("data-tns-applied", "true");
      this.#sliders.push(
        tns({
          container: SliderController.#CONTAINER_PREVIEW,
          items: 1,
          slideBy: "page",
          autoplay: true,
          responsive: {
            640: { edgePadding: 20, gutter: 20, items: 2 },
            700: { gutter: 30 },
            900: { items: 3 },
          },
        })
      );
    }

    /**
     * Destroy all sliders
     */
    destroy() {
      this.#sliders.forEach((s) => s?.destroy?.());
      this.#sliders = [];
      document.body?.removeAttribute(SliderController.#DATA_INIT);
    }
  }

  /**
   * Initialize sliders when DOM ready
   */
  const initSliders = () => {
    try {
      new SliderController().init();
    } catch (err) {
      console.error("[SliderController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initSliders)
    : initSliders();
})();
