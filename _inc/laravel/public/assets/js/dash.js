/**
 * @file dash.js
 * @description Dashboard menu management, navigation, and UI controls
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Dashboard menu and navigation controller
   * @class DashboardController
   */
  class DashboardController {
    /** @type {string} */
    static #DATA_INIT = "data-dash-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-dash-listener";
    /** @type {string} */
    static #OVERLAY_HTML = '<div class="dash-menu-overlay"></div>';
    /** @type {string} */
    static #MINIMENU_CLASS = "minimenu";
    /** @type {string} */
    static #NO_SCROLL_CLASS = "no-scroll";
    /** @type {string} */
    static #ACTIVE_CLASS = "active";
    /** @type {string} */
    static #TRIGGER_CLASS = "dash-trigger";
    /** @type {string} */
    static #MOB_ACTIVE_CLASS = "mob-sidebar-active";
    /** @type {string} */
    static #OVER_MENU_CLASS = "dash-over-menu-active";

    /** @type {PerfectScrollbar|null} */
    #scrollbar = null;
    /** @type {string} */
    #miniMenuFlag = "0";
    /** @type {HTMLElement|null} */
    #body = null;
    /** @type {HTMLElement|null} */
    #sidebar = null;
    /** @type {HTMLElement|null} */
    #topbar = null;

    /**
     * Check if body is available
     * @returns {HTMLElement|null}
     */
    get body() {
      return this.#body;
    }

    /**
     * Check if horizontal layout
     * @returns {boolean}
     */
    get isHorizontal() {
      return this.#body?.classList.contains("dash-horizontal") ?? false;
    }

    /**
     * Check if minimenu layout
     * @returns {boolean}
     */
    get isMinimenu() {
      return this.#body?.classList.contains(DashboardController.#MINIMENU_CLASS) ?? false;
    }

    /**
     * Initialize dashboard controller
     */
    init() {
      if (document.body?.hasAttribute(DashboardController.#DATA_INIT)) return;
      document.body?.setAttribute(DashboardController.#DATA_INIT, "true");

      this.#body = document.body;
      this.#sidebar = document.querySelector(".dash-sidebar");
      this.#topbar = document.querySelector(".topbar");

      this.#initFeatherIcons();
      this.#removePreloader();
      this.#setupScrollbar();
      this.#setupHamburger();
      this.#setupOverlayMenu();
      this.#setupVerticalNavToggle();
      this.#setupMobileCollapse();
      this.#setupHorizontalMenu();
      this.#setupNotificationScrollbar();
      this.#setupActiveMenuItem();
      this.#setupTabLayout();
      this.#setupNestedLayout();
      this.#setupTopbarLayout();
      this.#setupHorizontalSubmenuEdge();
      this.#setupProdLikes();
      this.#setupWindowListeners();

      if (this.isMinimenu) this.#collapseedge();
    }

    /**
     * Initialize feather icons if available
     * @private
     */
    #initFeatherIcons() {
      if (typeof feather !== "undefined" && typeof feather.replace === "function")
        feather.replace();
    }

    /**
     * Remove pre-loader element
     * @private
     */
    #removePreloader() {
      setTimeout(() => document.querySelector(".loader-bg")?.remove(), 400);
    }

    /**
     * Setup sidebar scrollbar
     * @private
     */
    #setupScrollbar() {
      if (this.isHorizontal && !this.#body?.classList.contains("navbar-overlay")) return;
      this.#addScroller();
    }

    /**
     * Add PerfectScrollbar and menu click handlers
     * @private
     */
    #addScroller() {
      this.#handleMiniMenu();
      this.#setupMenuClick();

      const navContent = document.querySelector(".navbar-content");
      if (!navContent || navContent.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      navContent.setAttribute(DashboardController.#DATA_LISTENER, "true");
      if (typeof PerfectScrollbar !== "undefined") {
        this.#scrollbar = new PerfectScrollbar(".navbar-content", {
          wheelSpeed: 0.5,
          swipeEasing: 0,
          suppressScrollX: true,
          wheelPropagation: 1,
          minScrollbarLength: 40,
        });
      }
    }

    /**
     * Setup hamburger menu toggle
     * @private
     */
    #setupHamburger() {
      const hamburger = document.querySelector(".hamburger:not(.is-active)");
      if (!hamburger || hamburger.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      hamburger.setAttribute(DashboardController.#DATA_LISTENER, "true");
      hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("is-active");
      });
    }

    /**
     * Setup overlay menu functionality
     * @private
     */
    #setupOverlayMenu() {
      const overlayMenu = document.querySelector("#overlay-menu");
      if (!overlayMenu || overlayMenu.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      overlayMenu.setAttribute(DashboardController.#DATA_LISTENER, "true");
      overlayMenu.addEventListener("click", () => {
        this.#setupMenuClick();
        if (this.#sidebar?.classList.contains(DashboardController.#OVER_MENU_CLASS)) {
          this.#removeOverMenu();
        } else {
          this.#sidebar?.classList.add(DashboardController.#OVER_MENU_CLASS);
          this.#insertOverlay(this.#sidebar, () => {
            this.#removeOverMenu();
            document.querySelector(".hamburger")?.classList.remove("is-active");
          });
        }
      });
    }

    /**
     * Setup vertical nav toggle
     * @private
     */
    #setupVerticalNavToggle() {
      const toggle = document.querySelector("#vertical-nav-toggle");
      if (!toggle || toggle.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      toggle.setAttribute(DashboardController.#DATA_LISTENER, "true");
      toggle.addEventListener("click", () => {
        if (this.isMinimenu) {
          this.#body?.classList.remove(DashboardController.#MINIMENU_CLASS);
          document.querySelectorAll(".dash-navbar li:not(.dash-trigger) .dash-submenu")
            .forEach(el => el.style.display = "none");
        } else {
          this.#body?.classList.add(DashboardController.#MINIMENU_CLASS);
          document.querySelectorAll(".dash-navbar li .dash-submenu")
            .forEach(el => el.removeAttribute("style"));
          this.#collapseedge();
        }
      });
    }

    /**
     * Setup mobile collapse button
     * @private
     */
    #setupMobileCollapse() {
      const mobileCollapse = document.querySelector("#mobile-collapse");
      if (!mobileCollapse || mobileCollapse.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      mobileCollapse.setAttribute(DashboardController.#DATA_LISTENER, "true");

      if (this.isHorizontal) {
        mobileCollapse.addEventListener("click", () => this.#handleHorizontalMobileCollapse());
      } else {
        mobileCollapse.addEventListener("click", () => this.#handleVerticalMobileCollapse());
      }
    }

    /**
     * Handle horizontal layout mobile collapse
     * @private
     */
    #handleHorizontalMobileCollapse() {
      if (this.#topbar?.classList.contains(DashboardController.#MOB_ACTIVE_CLASS)) {
        this.#removeMenu();
      } else {
        this.#topbar?.classList.add(DashboardController.#MOB_ACTIVE_CLASS);
        this.#insertOverlay(this.#topbar, () => {
          this.#removeMenu();
          document.querySelector(".hamburger")?.classList.remove("is-active");
        });
      }
    }

    /**
     * Handle vertical layout mobile collapse
     * @private
     */
    #handleVerticalMobileCollapse() {
      if (this.#body?.classList.contains(DashboardController.#NO_SCROLL_CLASS)) {
        this.#removeMenu();
      } else {
        this.#body?.classList.add(DashboardController.#NO_SCROLL_CLASS);
      }

      if (this.#sidebar?.classList.contains(DashboardController.#MOB_ACTIVE_CLASS)) {
        this.#removeMenu();
      } else {
        this.#sidebar?.classList.add(DashboardController.#MOB_ACTIVE_CLASS);
        this.#insertOverlay(this.#sidebar, () => {
          document.querySelector(".hamburger")?.classList.remove("is-active");
          this.#removeMenu();
        });
      }
    }

    /**
     * Setup horizontal menu click handlers
     * @private
     */
    #setupHorizontalMenu() {
      if (!this.isHorizontal) return;
      this.#horizontalMobileMenuClick();

      const topbarLink = document.querySelector(".dash-horizontal .topbar .dash-navbar>li>a");
      if (!topbarLink || topbarLink.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      topbarLink.setAttribute(DashboardController.#DATA_LISTENER, "true");
      topbarLink.addEventListener("click", (e) => {
        const target = e.target;
        setTimeout(() => target?.parentNode?.children[1]?.removeAttribute("style"), 1000);
      });
    }

    /**
     * Setup notification scrollbar
     * @private
     */
    #setupNotificationScrollbar() {
      const notiBody = document.querySelector(".drp-notification .noti-body");
      if (!notiBody || notiBody.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      notiBody.setAttribute(DashboardController.#DATA_LISTENER, "true");
      if (typeof PerfectScrollbar !== "undefined") {
        new PerfectScrollbar(".drp-notification .noti-body", {
          wheelSpeed: 0.5,
          swipeEasing: 0,
          suppressScrollX: true,
          wheelPropagation: 1,
          minScrollbarLength: 40,
        });
      }
    }

    /**
     * Setup active menu item highlighting
     * @private
     */
    #setupActiveMenuItem() {
      const pageUrl = window.location.href.split(/[?#]/)[0];
      const links = document.querySelectorAll(".dash-sidebar .dash-navbar a");

      links.forEach(link => {
        if (link.href !== pageUrl || link.getAttribute("href") === "") return;

        const li = link.parentNode;
        li?.classList.add(DashboardController.#ACTIVE_CLASS);
        this.#scrollToMenuItem(li);

        const parent1 = li?.parentNode?.parentNode;
        parent1?.classList.add(DashboardController.#ACTIVE_CLASS, DashboardController.#TRIGGER_CLASS);
        if (li?.parentNode instanceof HTMLElement)
          li.parentNode.style.display = "block";

        const parent2 = parent1?.parentNode?.parentNode;
        parent2?.classList.add(DashboardController.#ACTIVE_CLASS, DashboardController.#TRIGGER_CLASS);
        if (parent1?.parentNode instanceof HTMLElement)
          parent1.parentNode.style.display = "block";
      });

      this.#setupTabLayoutActive();
    }

    /**
     * Scroll navbar to active menu item
     * @param {HTMLElement|null} el
     * @private
     */
    #scrollToMenuItem(el) {
      if (!el) return;
      const navContent = document.querySelector(".navbar-content");
      if (!navContent) return;

      const rect = el.getBoundingClientRect();
      if (rect.top > 300) navContent.scrollTop = rect.top - 300;
    }

    /**
     * Setup tab layout active state
     * @private
     */
    #setupTabLayoutActive() {
      if (!this.#body?.classList.contains("tab-layout")) return;

      const activeTab = document.querySelector(".dash-tabcontent.active");
      if (!activeTab) return;

      const dataValue = activeTab.getAttribute("data-value");
      document.querySelector(".tab-sidemenu > ul > li")?.classList.remove(DashboardController.#ACTIVE_CLASS);
      document.querySelector(`.tab-sidemenu > ul > li > a[data-cont="${dataValue}"]`)
        ?.parentNode?.classList.add(DashboardController.#ACTIVE_CLASS);
    }

    /**
     * Setup tab layout click handlers
     * @private
     */
    #setupTabLayout() {
      const tabItems = document.querySelectorAll(".tab-sidemenu > ul > li");
      
      tabItems.forEach(item => {
        if (item.hasAttribute(DashboardController.#DATA_LISTENER)) return;
        item.setAttribute(DashboardController.#DATA_LISTENER, "true");

        item.addEventListener("click", (e) => {
          let target = e.target;
          if (target.tagName === "A") target = target.parentNode;
          if (target.tagName === "I") target = target.parentNode.parentNode;

          const dataCont = target.children[0]?.getAttribute("data-cont");
          document.querySelector(".navbar-content .dash-tabcontent.active")?.classList.remove(DashboardController.#ACTIVE_CLASS);
          document.querySelector(".tab-sidemenu > ul > li.active")?.classList.remove(DashboardController.#ACTIVE_CLASS);
          target.classList.add(DashboardController.#ACTIVE_CLASS);
          document.querySelector(`.navbar-content .dash-tabcontent[data-value="${dataCont}"]`)
            ?.classList.add(DashboardController.#ACTIVE_CLASS);
        });
      });
    }

    /**
     * Setup nested layout functionality
     * @private
     */
    #setupNestedLayout() {
      const toggleBtn = document.querySelector(".dash-toggle-sidemenu");
      if (!toggleBtn || toggleBtn.hasAttribute(DashboardController.#DATA_LISTENER)) return;

      toggleBtn.setAttribute(DashboardController.#DATA_LISTENER, "true");
      toggleBtn.addEventListener("click", () => {
        const isActive = toggleBtn.classList.contains(DashboardController.#ACTIVE_CLASS);
        const overlay = document.querySelector(".dash-sideoverlay");
        const sidebar = document.querySelector(".page-sidebar");

        [overlay, sidebar, toggleBtn].forEach(el => {
          isActive
            ? el?.classList.remove(DashboardController.#ACTIVE_CLASS)
            : el?.classList.add(DashboardController.#ACTIVE_CLASS);
        });
      });

      const sideOverlay = document.querySelector(".dash-sideoverlay");
      if (sideOverlay && !sideOverlay.hasAttribute(DashboardController.#DATA_LISTENER)) {
        sideOverlay.setAttribute(DashboardController.#DATA_LISTENER, "true");
        sideOverlay.addEventListener("click", () => {
          [sideOverlay, document.querySelector(".page-sidebar"), toggleBtn]
            .forEach(el => el?.classList.remove(DashboardController.#ACTIVE_CLASS));
        });
      }
    }

    /**
     * Setup topbar layout hover handlers
     * @private
     */
    #setupTopbarLayout() {
      if (!this.#body?.classList.contains("layout-topbar")) return;

      const dropdowns = document.querySelectorAll(".dash-header .list-unstyled > .dropdown");
      dropdowns.forEach(dropdown => {
        if (dropdown.hasAttribute(DashboardController.#DATA_LISTENER)) return;
        dropdown.setAttribute(DashboardController.#DATA_LISTENER, "true");

        dropdown.addEventListener("mouseenter", (e) => {
          e.target.children[1]?.classList.add("show");
        });
        dropdown.addEventListener("mouseleave", (e) => {
          e.target.children[1]?.classList.remove("show");
        });
      });
    }

    /**
     * Setup horizontal submenu edge detection
     * @private
     */
    #setupHorizontalSubmenuEdge() {
      if (!this.isHorizontal || window.innerWidth <= 1024) return;

      const hasMenuItems = document.querySelectorAll(".dash-horizontal .topbar .dash-submenu .dash-hasmenu");
      hasMenuItems.forEach(item => {
        if (item.hasAttribute(DashboardController.#DATA_LISTENER)) return;
        item.setAttribute(DashboardController.#DATA_LISTENER, "true");

        item.addEventListener("mouseenter", (e) => this.#handleSubmenuEdge(e.target));
        item.addEventListener("mouseleave", (e) => this.#cleanupSubmenuEdge(e.target));
      });
    }

    /**
     * Handle submenu edge positioning
     * @param {HTMLElement} target
     * @private
     */
    #handleSubmenuEdge(target) {
      const submenu = target.children[1];
      if (!submenu) return;

      const rect = submenu.getBoundingClientRect();
      const docW = window.innerWidth;
      const docH = window.innerHeight;

      if (rect.left + rect.width > docW)
        submenu.classList.add("edge");

      if (rect.top + rect.height > docH) {
        const scrollTop = document.documentElement.scrollTop;
        const maxH = docH - (rect.top - scrollTop);
        submenu.classList.add("scroll-menu");
        submenu.style.maxHeight = `calc(100vh - ${rect.top - scrollTop}px)`;

        if (typeof PerfectScrollbar !== "undefined")
          new PerfectScrollbar(submenu, { wheelSpeed: 0.5, suppressScrollX: true });
      }
    }

    /**
     * Cleanup submenu edge positioning
     * @param {HTMLElement} target
     * @private
     */
    #cleanupSubmenuEdge(target) {
      const scrollMenu = target.querySelector(".scroll-menu");
      scrollMenu?.removeAttribute("style");
      scrollMenu?.classList.remove("scroll-menu", "edge");
    }

    /**
     * Setup product likes animation
     * @private
     */
    #setupProdLikes() {
      const likeInputs = document.querySelectorAll(".prod-likes .form-check-input");
      likeInputs.forEach(input => {
        if (input.hasAttribute(DashboardController.#DATA_LISTENER)) return;
        input.setAttribute(DashboardController.#DATA_LISTENER, "true");

        input.addEventListener("change", (e) => {
          const checkbox = e.target;
          const parent = checkbox.parentNode;

          if (checkbox.checked) {
            const likeHtml = `<div class="dash-like"><div class="like-wrapper"><span><span class="dash-group"><span class="dash-dots"></span><span class="dash-dots"></span><span class="dash-dots"></span><span class="dash-dots"></span></span></span></div></div>`;
            parent.insertAdjacentHTML("beforeend", likeHtml);
            parent.querySelector(".dash-like")?.classList.add("dash-like-animate");
            setTimeout(() => parent.querySelector(".dash-like")?.remove(), 3000);
          } else {
            parent.querySelector(".dash-like")?.remove();
          }
        });
      });
    }

    /**
     * Setup window resize and load listeners
     * @private
     */
    #setupWindowListeners() {
      if (window.hasAttribute?.call(document.body, "data-window-listeners")) return;
      document.body?.setAttribute("data-window-listeners", "true");

      window.addEventListener("resize", () => {
        if (!this.isHorizontal) this.#handleMiniMenu();
        else this.#removeActive();
      });

      window.addEventListener("load", () => {
        this.#initBootstrapComponents();
      });
    }

    /**
     * Initialize Bootstrap components
     * @private
     */
    #initBootstrapComponents() {
      if (typeof bootstrap === "undefined") return;

      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        if (!el.hasAttribute(DashboardController.#DATA_LISTENER)) {
          el.setAttribute(DashboardController.#DATA_LISTENER, "true");
          new bootstrap.Tooltip(el);
        }
      });

      document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        if (!el.hasAttribute(DashboardController.#DATA_LISTENER)) {
          el.setAttribute(DashboardController.#DATA_LISTENER, "true");
          new bootstrap.Popover(el);
        }
      });

      document.querySelectorAll(".toast").forEach(el => {
        if (!el.hasAttribute(DashboardController.#DATA_LISTENER)) {
          el.setAttribute(DashboardController.#DATA_LISTENER, "true");
          new bootstrap.Toast(el);
        }
      });
    }

    /**
     * Setup menu click handlers
     * @private
     */
    #setupMenuClick() {
      if (this.isMinimenu) return;

      document.querySelectorAll(".dash-navbar li:not(.dash-trigger) .dash-submenu")
        .forEach(el => el.style.display = "none");

      this.#attachMenuClickHandlers(".dash-navbar > li:not(.dash-caption)", false);
      this.#attachMenuClickHandlers(".dash-navbar > li:not(.dash-caption) li", true);
    }

    /**
     * Attach click handlers to menu items
     * @param {string} selector
     * @param {boolean} isSubMenu
     * @private
     */
    #attachMenuClickHandlers(selector, isSubMenu) {
      document.querySelectorAll(selector).forEach(item => {
        if (item.hasAttribute(DashboardController.#DATA_LISTENER)) return;
        item.setAttribute(DashboardController.#DATA_LISTENER, "true");

        item.addEventListener("click", (e) => {
          e.stopPropagation();
          let target = e.target;
          if (target.tagName === "SPAN") target = target.parentNode;

          const parent = target.parentNode;
          if (parent.classList.contains(DashboardController.#TRIGGER_CLASS)) {
            parent.classList.remove(DashboardController.#TRIGGER_CLASS);
            this.#slideUp(parent.children[1], 200);
          } else {
            const siblings = isSubMenu ? parent.parentNode?.children : document.querySelectorAll("li.dash-trigger");
            this.#closeSiblingMenus(siblings);
            parent.classList.add(DashboardController.#TRIGGER_CLASS);
            const submenu = isSubMenu ? parent.children[1] : target.children[1];
            if (submenu) {
              submenu.removeAttribute?.("style");
              this.#slideDown(parent.children[1], 200);
            }
          }
        });
      });
    }

    /**
     * Close sibling menus
     * @param {NodeListOf<Element>|HTMLCollection|null} siblings
     * @private
     */
    #closeSiblingMenus(siblings) {
      if (!siblings) return;
      Array.from(siblings).forEach(sibling => {
        sibling.classList.remove(DashboardController.#TRIGGER_CLASS);
        let target = sibling;
        if (target.tagName === "LI") target = sibling.children[0];
        if (target?.parentNode?.classList.contains("dash-hasmenu"))
          this.#slideUp(target.parentNode.children[1], 200);
      });
    }

    /**
     * Horizontal mobile menu click handler
     * @private
     */
    #horizontalMobileMenuClick() {
      this.#attachHorizontalMenuHandlers(".dash-navbar > li:not(.dash-caption)");
      this.#attachHorizontalMenuHandlers(".dash-navbar > li:not(.dash-caption) > .dash-submenu > li");
      this.#attachHorizontalMenuHandlers(".dash-navbar > li:not(.dash-caption) > .dash-submenu > li > .dash-submenu > li");
    }

    /**
     * Attach horizontal menu handlers
     * @param {string} selector
     * @private
     */
    #attachHorizontalMenuHandlers(selector) {
      document.querySelectorAll(selector).forEach(item => {
        if (item.hasAttribute(`${DashboardController.#DATA_LISTENER}-horiz`)) return;
        item.setAttribute(`${DashboardController.#DATA_LISTENER}-horiz`, "true");

        item.addEventListener("click", (e) => {
          e.stopPropagation();
          let target = e.target;
          if (target.tagName === "SPAN") target = target.parentNode;

          target.parentNode?.children[1]?.removeAttribute("style");
          const parent = target.parentNode;

          if (parent.classList.contains(DashboardController.#TRIGGER_CLASS)) {
            parent.classList.remove(DashboardController.#TRIGGER_CLASS);
          } else {
            const triggerSelector = selector.includes(".dash-submenu > li > .dash-submenu")
              ? ".dash-submenu .dash-submenu li.dash-trigger"
              : selector.includes(".dash-submenu > li")
                ? ".dash-submenu li.dash-trigger"
                : "li.dash-trigger";

            document.querySelectorAll(triggerSelector)
              .forEach(el => el.classList.remove(DashboardController.#TRIGGER_CLASS));
            parent.classList.add(DashboardController.#TRIGGER_CLASS);
          }
        });
      });
    }

    /**
     * Handle mini menu state based on viewport
     * @private
     */
    #handleMiniMenu() {
      const vw = window.innerWidth;
      if (vw <= 1024 && this.isMinimenu) {
        this.#body?.classList.remove(DashboardController.#MINIMENU_CLASS);
        this.#miniMenuFlag = "1";
      } else if (vw > 1024 && this.#miniMenuFlag === "1") {
        this.#body?.classList.add(DashboardController.#MINIMENU_CLASS);
        this.#miniMenuFlag = "0";
      }
    }

    /**
     * Handle collapse edge for minimenu
     * @private
     */
    #collapseedge() {
      if (window.innerWidth <= 1024) return;

      const hasMenuItems = document.querySelectorAll(".minimenu .dash-sidebar .dash-submenu .dash-hasmenu");
      hasMenuItems.forEach(item => {
        if (item.hasAttribute(`${DashboardController.#DATA_LISTENER}-edge`)) return;
        item.setAttribute(`${DashboardController.#DATA_LISTENER}-edge`, "true");

        item.addEventListener("mouseenter", (e) => this.#handleSubmenuEdge(e.target));
        item.addEventListener("mouseleave", (e) => this.#cleanupSubmenuEdge(e.target));
      });
    }

    /**
     * Insert overlay and attach click handler
     * @param {HTMLElement|null} container
     * @param {Function} callback
     * @private
     */
    #insertOverlay(container, callback) {
      if (!container) return;
      container.insertAdjacentHTML("beforeend", DashboardController.#OVERLAY_HTML);
      container.querySelector(".dash-menu-overlay")?.addEventListener("click", callback);
    }

    /**
     * Remove menu overlay and classes
     * @private
     */
    #removeMenu() {
      this.#body?.classList.remove(DashboardController.#NO_SCROLL_CLASS);
      this.#sidebar?.classList.remove(DashboardController.#MOB_ACTIVE_CLASS);
      this.#topbar?.classList.remove(DashboardController.#MOB_ACTIVE_CLASS);
      document.querySelector(".dash-sidebar .dash-menu-overlay")?.remove();
    }

    /**
     * Remove over menu state
     * @private
     */
    #removeOverMenu() {
      this.#sidebar?.classList.remove(DashboardController.#OVER_MENU_CLASS);
      this.#topbar?.classList.remove(DashboardController.#MOB_ACTIVE_CLASS);
      document.querySelector(".dash-sidebar .dash-menu-overlay")?.remove();
      document.querySelector(".topbar .dash-menu-overlay")?.remove();
    }

    /**
     * Remove active states
     * @private
     */
    #removeActive() {
      try {
        document.querySelector(".dash-sidebar .dash-navbar li")?.classList.remove(DashboardController.#ACTIVE_CLASS, DashboardController.#TRIGGER_CLASS);
        document.querySelector(".topbar .dropdown")?.classList.remove("show");
        document.querySelector(".topbar .dropdown-menu")?.classList.remove("show");
        document.querySelector(".dash-sidebar .dash-menu-overlay")?.remove();
        document.querySelector(".topbar .dash-menu-overlay")?.remove();
      } catch (err) {
        console.error("[DashboardController] Error removing active states:", err);
      }
    }

    /**
     * Slide up animation
     * @param {HTMLElement|null} target
     * @param {number} duration
     * @private
     */
    #slideUp(target, duration = 0) {
      if (!target) return;
      target.style.transitionProperty = "height, margin, padding";
      target.style.transitionDuration = `${duration}ms`;
      target.style.boxSizing = "border-box";
      target.style.height = `${target.offsetHeight}px`;
      target.offsetHeight;
      target.style.overflow = "hidden";
      target.style.height = "0";
      target.style.paddingTop = "0";
      target.style.paddingBottom = "0";
      target.style.marginTop = "0";
      target.style.marginBottom = "0";
    }

    /**
     * Slide down animation
     * @param {HTMLElement|null} target
     * @param {number} duration
     * @private
     */
    #slideDown(target, duration = 0) {
      if (!target) return;
      target.style.removeProperty("display");
      let display = window.getComputedStyle(target).display;
      if (display === "none") display = "block";
      target.style.display = display;

      const height = target.offsetHeight;
      target.style.overflow = "hidden";
      target.style.height = "0";
      target.style.paddingTop = "0";
      target.style.paddingBottom = "0";
      target.style.marginTop = "0";
      target.style.marginBottom = "0";
      target.offsetHeight;
      target.style.boxSizing = "border-box";
      target.style.transitionProperty = "height, margin, padding";
      target.style.transitionDuration = `${duration}ms`;
      target.style.height = `${height}px`;
      target.style.removeProperty("padding-top");
      target.style.removeProperty("padding-bottom");
      target.style.removeProperty("margin-top");
      target.style.removeProperty("margin-bottom");

      setTimeout(() => {
        target.style.removeProperty("height");
        target.style.removeProperty("overflow");
        target.style.removeProperty("transition-duration");
        target.style.removeProperty("transition-property");
      }, duration);
    }

    /**
     * Cleanup controller instance
     */
    destroy() {
      this.#scrollbar?.destroy?.();
      document.body?.removeAttribute(DashboardController.#DATA_INIT);
    }
  }

  /**
   * Initialize dashboard when DOM is ready
   */
  const initDashboard = () => {
    try {
      new DashboardController().init();
    } catch (err) {
      console.error("[DashboardController] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initDashboard)
    : initDashboard();
})();
