/**
 * @file dash.js
 * @description Landing page dashboard controller - extends main DashboardController pattern
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Landing page dashboard controller
   * @class LandingDashboardController
   */
  class LandingDashboardController {
    /** @type {string} */
    static #DATA_INIT = "data-landing-dash-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-landing-dash-listener";
    /** @type {string} */
    static #OVERLAY_HTML = '<div class="menu-styler"><div class="style-toggler"><a href="#!"></a></div></div>';
    /** @type {string} */
    static #MINIMENU_CLASS = "minimenu";
    /** @type {string} */
    static #NO_SCROLL_CLASS = "no-scroll";
    /** @type {string} */
    static #ACTIVE_CLASS = "active";
    /** @type {string} */
    static #MOB_ACTIVE_CLASS = "mob-sidebar-active";
    /** @type {string} */
    static #OVER_MENU_CLASS = "pc-over-menu-active";
    /** @type {PerfectScrollbar|null} */
    #scrollbar = null;
    /** @type {number} */
    #miniMenuFlag = 0;
    /** @type {HTMLElement|null} */
    #body = null;
    /** @type {HTMLElement|null} */
    #sidebar = null;
    /** @type {HTMLElement|null} */
    #topbar = null;

    /**
     * Initialize dashboard
     */
    init() {
      if (document.body?.hasAttribute(LandingDashboardController.#DATA_INIT)) return;
      document.body?.setAttribute(LandingDashboardController.#DATA_INIT, "true");

      this.#body = document.body;
      this.#sidebar = document.querySelector(".pc-sidebar");
      this.#topbar = document.querySelector(".topbar");
      this.#setupComponents();
    }

    /**
     * Setup all dashboard components
     * @private
     */
    #setupComponents() {
      try {
        this.#setupScrollbar();
        this.#setupOverlay();
        this.#setupHamburger();
        this.#setupMenuClick();
        this.#setupMiniMenu();
        this.#setupMobileCollapse();
        this.#setupOverlayMenu();
        this.#initFeather();
      } catch (err) {
        console.error("[LandingDashboardController] Setup error:", err);
      }
    }

    /**
     * Initialize feather icons
     * @private
     */
    #initFeather() {
      if (typeof feather !== "undefined") feather.replace();
    }

    /**
     * Setup PerfectScrollbar for sidebar
     * @private
     */
    #setupScrollbar() {
      const navbar = document.querySelector(".navbar-content");
      if (!navbar || typeof PerfectScrollbar === "undefined" || navbar.hasAttribute("data-ps-applied")) return;
      navbar.setAttribute("data-ps-applied", "true");
      this.#scrollbar = new PerfectScrollbar(navbar);
    }

    /**
     * Setup overlay element
     * @private
     */
    #setupOverlay() {
      if (document.querySelector(".menu-styler")) return;
      document.body?.insertAdjacentHTML("beforeend", LandingDashboardController.#OVERLAY_HTML);
    }

    /**
     * Setup hamburger menu toggle
     * @private
     */
    #setupHamburger() {
      const toggler = document.querySelector(".mobile-menu");
      if (!toggler || toggler.hasAttribute(LandingDashboardController.#DATA_LISTENER)) return;
      toggler.setAttribute(LandingDashboardController.#DATA_LISTENER, "true");
      toggler.addEventListener("click", (e) => {
        e.preventDefault();
        this.#handleMiniMenu();
      });
    }

    /**
     * Handle mini menu toggle logic
     * @private
     */
    #handleMiniMenu() {
      if (!this.#body) return;
      this.#miniMenuFlag = this.#body.classList.contains(LandingDashboardController.#MINIMENU_CLASS) ? 0 : 1;
      this.#miniMenuFlag
        ? this.#body.classList.add(LandingDashboardController.#MINIMENU_CLASS)
        : this.#body.classList.remove(LandingDashboardController.#MINIMENU_CLASS);
    }

    /**
     * Setup sidebar menu click handlers
     * @private
     */
    #setupMenuClick() {
      const menuItems = document.querySelectorAll(".pc-navbar > li");
      menuItems.forEach((item) => {
        if (item.hasAttribute(LandingDashboardController.#DATA_LISTENER)) return;
        item.setAttribute(LandingDashboardController.#DATA_LISTENER, "true");
        this.#attachMenuClickHandlers(item);
      });
    }

    /**
     * Attach click handlers to menu item
     * @private
     * @param {HTMLElement} item
     */
    #attachMenuClickHandlers(item) {
      const trigger = item.querySelector(":scope > a");
      const submenu = item.querySelector(":scope > ul");
      if (!trigger || !submenu) return;

      trigger.addEventListener("click", (e) => {
        e.preventDefault();
        const isOpen = submenu.classList.contains(LandingDashboardController.#ACTIVE_CLASS);
        isOpen ? this.#slideUp(submenu, item) : this.#slideDown(submenu, item);
      });
    }

    /**
     * Slide up animation for submenu
     * @private
     * @param {HTMLElement} el
     * @param {HTMLElement} parent
     */
    #slideUp(el, parent) {
      el.style.height = `${el.scrollHeight}px`;
      requestAnimationFrame(() => {
        el.style.transition = "height 0.3s ease";
        el.style.height = "0";
        el.style.overflow = "hidden";
      });
      el.addEventListener(
        "transitionend",
        () => {
          el.classList.remove(LandingDashboardController.#ACTIVE_CLASS);
          parent.classList.remove(LandingDashboardController.#ACTIVE_CLASS);
          el.style.removeProperty("height");
          el.style.removeProperty("transition");
          el.style.removeProperty("overflow");
        },
        { once: true }
      );
    }

    /**
     * Slide down animation for submenu
     * @private
     * @param {HTMLElement} el
     * @param {HTMLElement} parent
     */
    #slideDown(el, parent) {
      el.classList.add(LandingDashboardController.#ACTIVE_CLASS);
      parent.classList.add(LandingDashboardController.#ACTIVE_CLASS);
      const height = el.scrollHeight;
      el.style.height = "0";
      el.style.overflow = "hidden";
      requestAnimationFrame(() => {
        el.style.transition = "height 0.3s ease";
        el.style.height = `${height}px`;
      });
      el.addEventListener(
        "transitionend",
        () => {
          el.style.removeProperty("height");
          el.style.removeProperty("transition");
          el.style.removeProperty("overflow");
        },
        { once: true }
      );
    }

    /**
     * Setup mini menu persistence
     * @private
     */
    #setupMiniMenu() {
      if (!this.#body) return;
      const storedMini = localStorage.getItem("mini_menu");
      if (storedMini === "true") this.#body.classList.add(LandingDashboardController.#MINIMENU_CLASS);
    }

    /**
     * Setup mobile collapse toggle
     * @private
     */
    #setupMobileCollapse() {
      const collapseBtn = document.querySelector("#mobile-collapse");
      if (!collapseBtn || collapseBtn.hasAttribute(LandingDashboardController.#DATA_LISTENER)) return;
      collapseBtn.setAttribute(LandingDashboardController.#DATA_LISTENER, "true");
      collapseBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.#sidebar?.classList.toggle(LandingDashboardController.#MOB_ACTIVE_CLASS);
        this.#body?.classList.toggle(LandingDashboardController.#NO_SCROLL_CLASS);
      });
    }

    /**
     * Setup overlay menu toggle
     * @private
     */
    #setupOverlayMenu() {
      const overlayBtn = document.querySelector("#overlay-menu");
      if (!overlayBtn || overlayBtn.hasAttribute(LandingDashboardController.#DATA_LISTENER)) return;
      overlayBtn.setAttribute(LandingDashboardController.#DATA_LISTENER, "true");
      overlayBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.#body?.classList.toggle(LandingDashboardController.#OVER_MENU_CLASS);
      });
    }

    /**
     * Destroy controller and cleanup
     */
    destroy() {
      this.#scrollbar?.destroy?.();
      document.body?.removeAttribute(LandingDashboardController.#DATA_INIT);
    }
  }

  /**
   * Initialize dashboard when DOM ready
   */
  const initDashboard = () => {
    try {
      new LandingDashboardController().init();
    } catch (err) {
      console.error("[LandingDashboardController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initDashboard)
    : initDashboard();
})();
