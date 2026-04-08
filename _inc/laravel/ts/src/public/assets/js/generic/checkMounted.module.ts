/**
 * checkMounted.module.ts — RecoveryOverlay for detecting unexpected
 * content (e.g. PHP error output) at page start.
 *
 * Mirror of public/assets/js/generic/checkMounted.module.js
 * @module generic/checkMounted.module
 */

const _emitted: Record<string, true> = {};

const devError = (tag: string, err: unknown): void => {
  if (location.hostname !== "localhost" && location.hostname !== "127.0.0.1") return;
  const msg = err instanceof Error ? err.message : String(err);
  const key = `${tag}:${msg}`;
  if (_emitted[key]) return;
  _emitted[key] = true;
  console.error(`[${tag}]`, msg);
};

((): void => {
  "use strict";

  class RecoveryOverlay {
    static readonly #OVERLAY_ID = "manual-recovery-overlay";
    static readonly #OVERFLOW_CLASS = "overflow-hidden";
    static readonly #BOOTSTRAP_CSS = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css";
    static readonly #BOOTSTRAP_ICONS = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css";
    static readonly #BOOTSTRAP_JS = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js";
    static readonly #ERROR_IMAGE = "/assets/images/406-art.webp";
    static readonly #ERROR_PATTERN = /^[\s\n\t\r]*[0-9]+</;
    static readonly #DATA_INIT = "data-recovery-init";

    #isModal = false;
    #targetElement: HTMLElement | null = null;
    #overlay: HTMLElement | null = null;
    #escHandler: ((e: KeyboardEvent) => void) | null = null;

    get isModalContext(): boolean {
      return this.#isModal;
    }

    init(): void {
      if (document.body?.hasAttribute(RecoveryOverlay.#DATA_INIT)) return;

      this.#isModal = !document.body || !!document.querySelector(".modal-body, .modal-header, .modal-content");
      this.#targetElement = this.#isModal ? document.documentElement || document.querySelector("form, div") : document.body;

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

    #ensureDependencies(): void {
      this.#ensureStylesheet(RecoveryOverlay.#BOOTSTRAP_CSS, 'link[rel="stylesheet"][href*="bootstrap"][href$=".css"]:not([href*="icons"])');
      this.#ensureStylesheet(RecoveryOverlay.#BOOTSTRAP_ICONS, 'link[rel="stylesheet"][href*="bootstrap-icons"]');
      this.#ensureScript(RecoveryOverlay.#BOOTSTRAP_JS, 'script[src*="bootstrap"][src*="bundle"]');
    }

    #ensureStylesheet(href: string, selector: string): void {
      if (document.querySelector(selector)) return;
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = href;
      (document.head || this.#targetElement!).appendChild(link);
    }

    #ensureScript(src: string, selector: string): void {
      if (document.querySelector(selector)) return;
      const script = document.createElement("script");
      script.src = src;
      script.defer = true;
      (document.head || this.#targetElement!).appendChild(script);
    }

    #createOverlay(): void {
      this.#overlay = document.createElement("div");
      this.#overlay.id = RecoveryOverlay.#OVERLAY_ID;
      this.#overlay.tabIndex = -1;

      this.#applyOverlayStyles();
      this.#setAccessibilityAttributes();

      const card = this.#createCard();
      this.#overlay.appendChild(card);
    }

    #applyOverlayStyles(): void {
      if (!this.#overlay) return;
      const baseClass = this.#isModal ? "position-relative w-100 d-flex align-items-center justify-content-center p-3" : "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3";
      if (this.#overlay.getAttribute("class") !== baseClass) this.#overlay.className = baseClass;

      const bgStyle = this.#isModal ? "rgba(255,255,255,.95)" : "rgba(0,0,0,.25)";
      if (this.#overlay.style.background !== bgStyle) this.#overlay.style.background = bgStyle;
      if (!this.#isModal && this.#overlay.style.zIndex !== "2147483000") this.#overlay.style.zIndex = "2147483000";
      if (this.#isModal && this.#overlay.style.minHeight !== "300px") this.#overlay.style.minHeight = "300px";
    }

    #setAccessibilityAttributes(): void {
      if (!this.#overlay) return;
      const attrs: Record<string, string> = {
        role: "dialog",
        "aria-modal": "true",
        "aria-labelledby": "diagTitle",
        "aria-describedby": "diagDesc",
      };
      for (const [key, value] of Object.entries(attrs)) if (this.#overlay.getAttribute(key) !== value) this.#overlay.setAttribute(key, value);
    }

    #createCard(): HTMLElement {
      const card = document.createElement("div"),
        cardClass = this.#isModal ? "border-0" : "card shadow-lg border-0";
      card.className = cardClass;
      card.style.maxWidth = "720px";
      card.style.width = "100%";
      card.setAttribute("role", "document");
      card.appendChild(this.#createCardBody());
      return card;
    }

    #createCardBody(): HTMLElement {
      const body = document.createElement("div");
      body.className = this.#isModal ? "p-3 text-center" : "card-body p-4 text-center";
      body.appendChild(this.#createIcon());
      body.appendChild(this.#createTitle());
      body.appendChild(this.#createDescription());
      body.appendChild(this.#createImage());
      body.appendChild(this.#createActions());
      body.appendChild(this.#createHint());
      return body;
    }

    #createIcon(): HTMLElement {
      const wrap = document.createElement("div");
      wrap.className = "mb-3";
      wrap.innerHTML = '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning" aria-hidden="true"></i>';
      return wrap;
    }

    #createTitle(): HTMLElement {
      const title = document.createElement("h1");
      title.id = "diagTitle";
      title.className = this.#isModal ? "h5 fw-bold mb-2" : "h4 fw-bold mb-2";
      title.textContent = "Unexpected Output Detected";
      return title;
    }

    #createDescription(): HTMLElement {
      const desc = document.createElement("p");
      desc.id = "diagDesc";
      desc.className = "text-muted mb-3";
      desc.innerHTML = this.#isModal ? "We found unexpected content in this modal. Please try closing and reopening it." : "We found unexpected content at the start of this page. You can safely navigate using the options below.";
      return desc;
    }

    #createImage(): HTMLElement {
      const wrap = document.createElement("div");
      wrap.className = "my-3";
      const img = document.createElement("img");
      img.src = RecoveryOverlay.#ERROR_IMAGE;
      img.alt = "Illustration for error/406";
      img.className = "img-fluid rounded";
      img.style.maxHeight = this.#isModal ? "180px" : "240px";
      img.loading = "lazy";
      img.setAttribute("aria-hidden", "true");
      img.onerror = (): void => {
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

    #createActions(): HTMLElement {
      const actions = document.createElement("div");
      actions.className = "d-grid gap-2 d-sm-flex justify-content-sm-center mt-3";
      this.#isModal ? this.#createModalActions(actions) : this.#createPageActions(actions);
      return actions;
    }

    #createModalActions(container: HTMLElement): void {
      const closeBtn = this.#createButton("btn btn-secondary", "Close this modal", "x-lg", "Close Modal");
      closeBtn.setAttribute("data-bs-dismiss", "modal");
      closeBtn.onclick = (): void => {
        this.#closeModal();
      };

      const reloadBtn = this.#createButton("btn btn-warning", "Reload this page", "arrow-repeat", "Reload Page");
      reloadBtn.style.color = "white";
      reloadBtn.onclick = (): void => {
        location.reload();
      };
      container.append(closeBtn, reloadBtn);
    }

    #createPageActions(container: HTMLElement): void {
      const backBtn = this.#createButton("btn btn-primary", "Go back to previous page", "arrow-left", "Go Back");
      backBtn.onclick = (): void => {
        history.back();
      };

      const homeLink = document.createElement("a");
      homeLink.className = "btn btn-info";
      homeLink.style.color = "white";
      homeLink.href = "/";
      homeLink.setAttribute("aria-label", "Return to the home page");
      homeLink.innerHTML = '<i class="bi bi-house" aria-hidden="true"></i> Home';

      const reloadBtn = this.#createButton("btn btn-warning", "Reload this page", "arrow-repeat", "Reload");
      reloadBtn.style.color = "white";
      reloadBtn.onclick = (): void => {
        location.reload();
      };
      container.append(backBtn, homeLink, reloadBtn);
    }

    #createButton(className: string, ariaLabel: string, iconName: string, text: string): HTMLButtonElement {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = className;
      btn.setAttribute("aria-label", ariaLabel);
      btn.innerHTML = `<i class="bi bi-${iconName}" aria-hidden="true"></i> ${text}`;
      return btn;
    }

    #createHint(): HTMLElement {
      const hint = document.createElement("p");
      hint.className = "mt-3 small text-muted";
      const message = this.#isModal ? "to dismiss." : "to dismiss this message.";
      hint.innerHTML = `<i class="bi bi-info-circle" aria-hidden="true"></i> Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> ${message}`;
      return hint;
    }

    #attachOverlay(): void {
      if (!this.#overlay || !this.#targetElement) return;
      this.#targetElement.appendChild(this.#overlay);
      if (!this.#isModal && !document.body.classList.contains(RecoveryOverlay.#OVERFLOW_CLASS)) document.body.classList.add(RecoveryOverlay.#OVERFLOW_CLASS);
      setTimeout((): void => {
        this.#overlay?.focus();
      }, 0);
    }

    #setupEscapeHandler(): void {
      this.#escHandler = (e: KeyboardEvent): void => {
        if (e.key !== "Escape") return;
        this.dismiss();
      };
      document.addEventListener("keydown", this.#escHandler, { once: true });
    }

    dismiss(): void {
      if (this.#escHandler) {
        document.removeEventListener("keydown", this.#escHandler);
        this.#escHandler = null;
      }
      this.#overlay?.remove();
      if (!this.#isModal && document.body.classList.contains(RecoveryOverlay.#OVERFLOW_CLASS)) document.body.classList.remove(RecoveryOverlay.#OVERFLOW_CLASS);
      document.body?.removeAttribute(RecoveryOverlay.#DATA_INIT);
    }

    #closeModal(): void {
      try {
        const modalElement = this.#overlay?.closest(".modal");
        if (!modalElement) return this.dismiss();
        const modal = window.bootstrap?.Modal?.getInstance(modalElement);
        modal ? modal.hide() : (modalElement.querySelector('[data-bs-dismiss="modal"]') as HTMLElement | null)?.click();
      } catch (err) {
        devError("RecoveryOverlay.closeModal", err);
      } finally {
        this.dismiss();
      }
    }
  }

  const initRecoveryCheck = (): void => {
    try {
      new RecoveryOverlay().init();
    } catch (err) {
      devError("RecoveryOverlay.init", err);
    }
  };

  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", initRecoveryCheck) : initRecoveryCheck();
})();

export {};
