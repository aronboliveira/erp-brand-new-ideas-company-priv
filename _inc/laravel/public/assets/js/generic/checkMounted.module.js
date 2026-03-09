/**
 * @file checkMounted.js
 * @description Detects unexpected content (like PHP errors) at page start and shows recovery overlay
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * Recovery overlay manager for detecting and handling malformed page content
   * @class RecoveryOverlay
   */
  class RecoveryOverlay {
    /** @type {string} */
    static #OVERLAY_ID = "manual-recovery-overlay";
    /** @type {string} */
    static #OVERFLOW_CLASS = "overflow-hidden";
    /** @type {string} */
    static #BOOTSTRAP_CSS = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css";
    /** @type {string} */
    static #BOOTSTRAP_ICONS = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css";
    /** @type {string} */
    static #BOOTSTRAP_JS = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js";
    /** @type {string} */
    static #ERROR_IMAGE = "/assets/images/406-art.webp";
    /** @type {RegExp} */
    static #ERROR_PATTERN = /^[\s\n\t\r]*[0-9]+</;
    /** @type {string} */
    static #DATA_INIT = "data-recovery-init";

    /** @type {boolean} */
    #isModal = false;
    /** @type {HTMLElement|null} */
    #targetElement = null;
    /** @type {HTMLElement|null} */
    #overlay = null;
    /** @type {((e: KeyboardEvent) => void)|null} */
    #escHandler = null;

    /**
     * Check if the current context is a modal
     * @returns {boolean}
     */
    get isModalContext() {
      return this.#isModal;
    }

    /**
     * Initialize and run the check
     */
    init() {
      if (document.body?.hasAttribute(RecoveryOverlay.#DATA_INIT)) return;

      this.#isModal = !document.body || !!document.querySelector(".modal-body, .modal-header, .modal-content");
      this.#targetElement = this.#isModal
        ? document.documentElement || document.querySelector("form, div")
        : document.body;

      if (!this.#targetElement) return;

      const content = this.#targetElement.innerHTML || "";
      if (!RecoveryOverlay.#ERROR_PATTERN.test(content)) return;

      this.#ensureDependencies();

      if (document.getElementById(RecoveryOverlay.#OVERLAY_ID)) return;

      document.body?.setAttribute(RecoveryOverlay.#DATA_INIT, "true");
      this.#createOverlay();
      this.#attachOverlay();
      this.#setupEscapeHandler();
    }

    /**
     * Ensure CSS/JS dependencies are loaded
     * @private
     */
    #ensureDependencies() {
      this.#ensureStylesheet(
        RecoveryOverlay.#BOOTSTRAP_CSS,
        'link[rel="stylesheet"][href*="bootstrap"][href$=".css"]:not([href*="icons"])'
      );
      this.#ensureStylesheet(
        RecoveryOverlay.#BOOTSTRAP_ICONS,
        'link[rel="stylesheet"][href*="bootstrap-icons"]'
      );
      this.#ensureScript(
        RecoveryOverlay.#BOOTSTRAP_JS,
        'script[src*="bootstrap"][src*="bundle"]'
      );
    }

    /**
     * Ensure a stylesheet is loaded
     * @param {string} href - Stylesheet URL
     * @param {string} selector - Selector to check if already loaded
     * @private
     */
    #ensureStylesheet(href, selector) {
      if (document.querySelector(selector)) return;
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = href;
      (document.head || this.#targetElement).appendChild(link);
    }

    /**
     * Ensure a script is loaded
     * @param {string} src - Script URL
     * @param {string} selector - Selector to check if already loaded
     * @private
     */
    #ensureScript(src, selector) {
      if (document.querySelector(selector)) return;
      const script = document.createElement("script");
      script.src = src;
      script.defer = true;
      (document.head || this.#targetElement).appendChild(script);
    }

    /**
     * Create the recovery overlay element
     * @private
     */
    #createOverlay() {
      this.#overlay = document.createElement("div");
      this.#overlay.id = RecoveryOverlay.#OVERLAY_ID;
      this.#overlay.tabIndex = -1;

      this.#applyOverlayStyles();
      this.#setAccessibilityAttributes();

      const card = this.#createCard();
      this.#overlay.appendChild(card);
    }

    /**
     * Apply styles based on context (modal vs page)
     * @private
     */
    #applyOverlayStyles() {
      if (!this.#overlay) return;

      const baseClass = this.#isModal
        ? "position-relative w-100 d-flex align-items-center justify-content-center p-3"
        : "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3";

      if (this.#overlay.getAttribute("class") !== baseClass)
        this.#overlay.className = baseClass;

      const bgStyle = this.#isModal ? "rgba(255,255,255,.95)" : "rgba(0,0,0,.25)";
      if (this.#overlay.style.background !== bgStyle)
        this.#overlay.style.background = bgStyle;

      if (!this.#isModal && this.#overlay.style.zIndex !== "2147483000")
        this.#overlay.style.zIndex = "2147483000";

      if (this.#isModal && this.#overlay.style.minHeight !== "300px")
        this.#overlay.style.minHeight = "300px";
    }

    /**
     * Set ARIA accessibility attributes
     * @private
     */
    #setAccessibilityAttributes() {
      if (!this.#overlay) return;
      const attrs = {
        role: "dialog",
        "aria-modal": "true",
        "aria-labelledby": "diagTitle",
        "aria-describedby": "diagDesc",
      };
      for (const [key, value] of Object.entries(attrs))
        if (this.#overlay.getAttribute(key) !== value)
          this.#overlay.setAttribute(key, value);
    }

    /**
     * Create the card container
     * @returns {HTMLElement}
     * @private
     */
    #createCard() {
      const card = document.createElement("div");
      const cardClass = this.#isModal ? "border-0" : "card shadow-lg border-0";
      card.className = cardClass;
      card.style.maxWidth = "720px";
      card.style.width = "100%";
      card.setAttribute("role", "document");

      const cardBody = this.#createCardBody();
      card.appendChild(cardBody);
      return card;
    }

    /**
     * Create the card body with content
     * @returns {HTMLElement}
     * @private
     */
    #createCardBody() {
      const cardBody = document.createElement("div");
      cardBody.className = this.#isModal ? "p-3 text-center" : "card-body p-4 text-center";

      cardBody.appendChild(this.#createIcon());
      cardBody.appendChild(this.#createTitle());
      cardBody.appendChild(this.#createDescription());
      cardBody.appendChild(this.#createImage());
      cardBody.appendChild(this.#createActions());
      cardBody.appendChild(this.#createHint());

      return cardBody;
    }

    /**
     * Create warning icon
     * @returns {HTMLElement}
     * @private
     */
    #createIcon() {
      const wrap = document.createElement("div");
      wrap.className = "mb-3";
      wrap.innerHTML = '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning" aria-hidden="true"></i>';
      return wrap;
    }

    /**
     * Create title element
     * @returns {HTMLElement}
     * @private
     */
    #createTitle() {
      const title = document.createElement("h1");
      title.id = "diagTitle";
      title.className = this.#isModal ? "h5 fw-bold mb-2" : "h4 fw-bold mb-2";
      title.textContent = "Unexpected Output Detected";
      return title;
    }

    /**
     * Create description element
     * @returns {HTMLElement}
     * @private
     */
    #createDescription() {
      const desc = document.createElement("p");
      desc.id = "diagDesc";
      desc.className = "text-muted mb-3";
      desc.innerHTML = this.#isModal
        ? "We found unexpected content in this modal. Please try closing and reopening it."
        : "We found unexpected content at the start of this page. You can safely navigate using the options below.";
      return desc;
    }

    /**
     * Create image with fallback
     * @returns {HTMLElement}
     * @private
     */
    #createImage() {
      const wrap = document.createElement("div");
      wrap.className = "my-3";

      const img = document.createElement("img");
      img.src = RecoveryOverlay.#ERROR_IMAGE;
      img.alt = "Illustration for error/406";
      img.className = "img-fluid rounded";
      img.style.maxHeight = this.#isModal ? "180px" : "240px";
      img.loading = "lazy";
      img.setAttribute("aria-hidden", "true");

      img.onerror = () => {
        const alert = document.createElement("div");
        alert.className = "alert alert-warning d-flex align-items-center justify-content-center gap-2 mt-3 mb-0";
        alert.setAttribute("role", "alert");
        alert.setAttribute("aria-live", "assertive");
        alert.setAttribute("aria-atomic", "true");
        alert.innerHTML = '<i class="bi bi-image-alt" aria-hidden="true"></i><div>Illustration failed to load.</div>';
        img.replaceWith(alert);
      };

      wrap.appendChild(img);
      return wrap;
    }

    /**
     * Create action buttons
     * @returns {HTMLElement}
     * @private
     */
    #createActions() {
      const actions = document.createElement("div");
      actions.className = "d-grid gap-2 d-sm-flex justify-content-sm-center mt-3";

      this.#isModal
        ? this.#createModalActions(actions)
        : this.#createPageActions(actions);

      return actions;
    }

    /**
     * Create modal-specific action buttons
     * @param {HTMLElement} container
     * @private
     */
    #createModalActions(container) {
      const closeBtn = this.#createButton("btn btn-secondary", "Close this modal", "x-lg", "Close Modal");
      closeBtn.setAttribute("data-bs-dismiss", "modal");
      closeBtn.onclick = () => this.#closeModal();

      const reloadBtn = this.#createButton("btn btn-warning", "Reload this page", "arrow-repeat", "Reload Page");
      reloadBtn.style.color = "white";
      reloadBtn.onclick = () => location.reload();

      container.append(closeBtn, reloadBtn);
    }

    /**
     * Create page-level action buttons
     * @param {HTMLElement} container
     * @private
     */
    #createPageActions(container) {
      const backBtn = this.#createButton("btn btn-primary", "Go back to previous page", "arrow-left", "Go Back");
      backBtn.onclick = () => history.back();

      const homeLink = document.createElement("a");
      homeLink.className = "btn btn-info";
      homeLink.style.color = "white";
      homeLink.href = "/";
      homeLink.setAttribute("aria-label", "Return to the home page");
      homeLink.innerHTML = '<i class="bi bi-house" aria-hidden="true"></i> Home';

      const reloadBtn = this.#createButton("btn btn-warning", "Reload this page", "arrow-repeat", "Reload");
      reloadBtn.style.color = "white";
      reloadBtn.onclick = () => location.reload();

      container.append(backBtn, homeLink, reloadBtn);
    }

    /**
     * Create a button element
     * @param {string} className
     * @param {string} ariaLabel
     * @param {string} iconName
     * @param {string} text
     * @returns {HTMLButtonElement}
     * @private
     */
    #createButton(className, ariaLabel, iconName, text) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = className;
      btn.setAttribute("aria-label", ariaLabel);
      btn.innerHTML = \`<i class="bi bi-\${iconName}" aria-hidden="true"></i> \${text}\`;
      return btn;
    }

    /**
     * Create hint text
     * @returns {HTMLElement}
     * @private
     */
    #createHint() {
      const hint = document.createElement("p");
      hint.className = "mt-3 small text-muted";
      const message = this.#isModal ? "to dismiss." : "to dismiss this message.";
      hint.innerHTML = \`<i class="bi bi-info-circle" aria-hidden="true"></i> Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> \${message}\`;
      return hint;
    }

    /**
     * Attach overlay to DOM
     * @private
     */
    #attachOverlay() {
      if (!this.#overlay || !this.#targetElement) return;
      this.#targetElement.appendChild(this.#overlay);

      if (!this.#isModal && !document.body.classList.contains(RecoveryOverlay.#OVERFLOW_CLASS))
        document.body.classList.add(RecoveryOverlay.#OVERFLOW_CLASS);

      setTimeout(() => this.#overlay?.focus(), 0);
    }

    /**
     * Setup escape key handler
     * @private
     */
    #setupEscapeHandler() {
      this.#escHandler = (e) => {
        if (e.key !== "Escape") return;
        this.dismiss();
      };
      document.addEventListener("keydown", this.#escHandler, { once: true });
    }

    /**
     * Dismiss the overlay
     */
    dismiss() {
      if (this.#escHandler) {
        document.removeEventListener("keydown", this.#escHandler);
        this.#escHandler = null;
      }
      this.#overlay?.remove();
      if (!this.#isModal && document.body.classList.contains(RecoveryOverlay.#OVERFLOW_CLASS))
        document.body.classList.remove(RecoveryOverlay.#OVERFLOW_CLASS);
      document.body?.removeAttribute(RecoveryOverlay.#DATA_INIT);
    }

    /**
     * Close modal context
     * @private
     */
    #closeModal() {
      try {
        const modalElement = this.#overlay?.closest(".modal");
        if (!modalElement) return this.dismiss();

        const modal = window.bootstrap?.Modal?.getInstance(modalElement);
        modal
          ? modal.hide()
          : modalElement.querySelector('[data-bs-dismiss="modal"]')?.click();
      } catch (err) {
        console.error("[RecoveryOverlay] Error closing modal:", err);
      } finally {
        this.dismiss();
      }
    }
  }

  /**
   * Initialize recovery overlay check
   */
  const initRecoveryCheck = () => {
    try {
      new RecoveryOverlay().init();
    } catch (err) {
      console.error("[RecoveryOverlay] Initialization error:", err);
    }
  };

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", initRecoveryCheck)
    : initRecoveryCheck();
})();
