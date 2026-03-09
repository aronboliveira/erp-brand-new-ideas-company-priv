/**
 * @file ac-alert.js
 * @description SweetAlert2 demonstration controller (Landing Page)
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * SweetAlert2 demo controller
   * @class LandingSwalDemoController
   */
  class LandingSwalDemoController {
    /** @type {string} */
    static #DATA_INIT = "data-lp-swal-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-lp-swal-listener";
    /** @type {string} */
    static #BTN_DANGER = "btn btn-danger";
    /** @type {string} */
    static #BTN_SUCCESS = "btn btn-success";
    /** @type {string} */
    static #BTN_PRIMARY = "btn btn-primary";

    /**
     * Initialize SweetAlert2 demos
     */
    init() {
      if (document.body?.hasAttribute(LandingSwalDemoController.#DATA_INIT)) return;
      if (typeof Swal === "undefined") return console.warn("[LandingSwalDemoController] Swal not loaded");

      document.body?.setAttribute(LandingSwalDemoController.#DATA_INIT, "true");
      this.#setupDemos();
    }

    /**
     * Setup all demo buttons
     * @private
     */
    #setupDemos() {
      try {
        this.#setupBasicDemos();
        this.#setupPositionDemos();
        this.#setupAnimationDemos();
        this.#setupConfirmDemos();
        this.#setupInputDemos();
        this.#setupAdvancedDemos();
      } catch (err) {
        console.error("[LandingSwalDemoController] Error:", err);
      }
    }

    /**
     * Bind click handler to element
     * @private
     * @param {string} selector
     * @param {Function} handler
     */
    #bind(selector, handler) {
      const el = document.querySelector(selector);
      if (!el || el.hasAttribute(LandingSwalDemoController.#DATA_LISTENER)) return;
      el.setAttribute(LandingSwalDemoController.#DATA_LISTENER, "true");
      el.addEventListener("click", handler);
    }

    /**
     * Setup basic alert demos
     * @private
     */
    #setupBasicDemos() {
      this.#bind("#alert-demo-1", () => Swal.fire("Any fool can use a computer"));
      this.#bind("#alert-demo-2", () => Swal.fire("The Internet?", "That thing is still around?")));
      this.#bind("#alert-demo-3", () => Swal.fire({ title: "Error!", text: "Something went wrong!", icon: "error", confirmButtonClass: LandingSwalDemoController.#BTN_DANGER }));
      this.#bind("#alert-demo-4", () => Swal.fire({ title: "Good job!", text: "You clicked the button!", icon: "success", confirmButtonClass: LandingSwalDemoController.#BTN_SUCCESS }));
      this.#bind("#alert-demo-5", () => Swal.fire({ title: "Custom HTML!", html: "You can use <b>bold text</b>, <a href='#'>links</a>", icon: "info" }));
    }

    /**
     * Setup position demos
     * @private
     */
    #setupPositionDemos() {
      const positions = ["top", "top-start", "top-end", "center", "center-start", "center-end", "bottom", "bottom-start", "bottom-end"];
      positions.forEach((pos, i) => {
        this.#bind(`#alert-pos-${i + 1}`, () => Swal.fire({ position: pos, icon: "success", title: `Position: ${pos}`, showConfirmButton: false, timer: 1500 }));
      });
    }

    /**
     * Setup animation demos
     * @private
     */
    #setupAnimationDemos() {
      this.#bind("#alert-anim-1", () => Swal.fire({ title: "Bounce!", showClass: { popup: "animate__animated animate__bounceIn" }, hideClass: { popup: "animate__animated animate__bounceOut" } }));
      this.#bind("#alert-anim-2", () => Swal.fire({ title: "Fade!", showClass: { popup: "animate__animated animate__fadeIn" }, hideClass: { popup: "animate__animated animate__fadeOut" } }));
    }

    /**
     * Setup confirm demos
     * @private
     */
    #setupConfirmDemos() {
      this.#bind("#alert-confirm-1", () => {
        Swal.fire({ title: "Are you sure?", text: "You won't be able to revert this!", icon: "warning", showCancelButton: true, confirmButtonColor: "#3085d6", cancelButtonColor: "#d33", confirmButtonText: "Yes, delete it!" }).then((result) => {
          if (result.isConfirmed) Swal.fire("Deleted!", "Your file has been deleted.", "success");
        });
      });
    }

    /**
     * Setup input demos
     * @private
     */
    #setupInputDemos() {
      this.#bind("#alert-input-1", async () => {
        const { value: email } = await Swal.fire({ title: "Input email address", input: "email", inputPlaceholder: "Enter your email address" });
        if (email) Swal.fire(`Entered email: ${email}`);
      });
      this.#bind("#alert-input-2", async () => {
        const { value: text } = await Swal.fire({ input: "textarea", inputPlaceholder: "Type your message here...", showCancelButton: true });
        if (text) Swal.fire(text);
      });
    }

    /**
     * Setup advanced demos
     * @private
     */
    #setupAdvancedDemos() {
      this.#bind("#alert-timer", () => {
        let timerInterval;
        Swal.fire({
          title: "Auto close alert!",
          html: "I will close in <b></b> milliseconds.",
          timer: 2000,
          timerProgressBar: true,
          didOpen: () => {
            Swal.showLoading();
            const b = Swal.getHtmlContainer().querySelector("b");
            timerInterval = setInterval(() => { b.textContent = Swal.getTimerLeft(); }, 100);
          },
          willClose: () => clearInterval(timerInterval),
        });
      });
    }

    /**
     * Destroy controller
     */
    destroy() {
      document.body?.removeAttribute(LandingSwalDemoController.#DATA_INIT);
    }
  }

  /**
   * Initialize when DOM ready
   */
  const initSwalDemo = () => {
    try {
      new LandingSwalDemoController().init();
    } catch (err) {
      console.error("[LandingSwalDemoController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initSwalDemo)
    : initSwalDemo();
})();
