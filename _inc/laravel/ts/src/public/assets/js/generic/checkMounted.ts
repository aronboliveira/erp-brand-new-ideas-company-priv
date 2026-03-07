/**
 * @fileoverview Recovery overlay for pages/modals with unexpected numeric content.
 * Detects raw number-prefixed HTML and shows a user-friendly diagnostic UI.
 * @module checkMounted
 */

declare const bootstrap: {
  Modal: { getInstance(el: Element): { hide(): void } | null };
};
((): void => {
  try {
    const infoIcon = '<i class="bi bi-info-circle" aria-hidden="true"></i>',
      reloadIcon = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i>',
      BTN_WARNING = "btn btn-warning",
      RELOAD_LABEL = "Reload this page",
      COLOR_WHITE = "white",
      NUM_PREFIX_RE = /^[\s\n\t\r]*[0-9]+</;
    /** Checks the DOM for unexpected raw-number output and overlays a recovery dialog. */
    const checkMounted = (): void => {
      const isModal = document.querySelector<HTMLElement>(
          ".modal-body, .modal-header, .modal-content",
        ),
        targetElement = isModal
          ? document.documentElement.querySelector<HTMLFormElement>("form, div")
          : document.body;
      if (!targetElement) return;
      const m = targetElement.innerHTML.match(NUM_PREFIX_RE);
      if (!m) return;
      /** Appends a stylesheet if not already present. */
      const ensureCss = (href: string, selector: string): void => {
        if (!document.querySelector(selector)) {
          const l = document.createElement("link");
          l.rel = "stylesheet";
          l.href = href;
          document.head.appendChild(l);
        }
      };
      /** Appends a deferred script if not already present. */
      const ensureJs = (src: string, selector: string): void => {
        if (!document.querySelector(selector)) {
          const s = document.createElement("script");
          s.src = src;
          s.defer = true;
          document.head.appendChild(s);
        }
      };
      ensureCss(
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
        'link[rel="stylesheet"][href*="bootstrap"][href$=".css"]:not([href*="icons"])',
      );
      ensureCss(
        "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css",
        'link[rel="stylesheet"][href*="bootstrap-icons"]',
      );
      ensureJs(
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
        'script[src*="bootstrap"][src*="bundle"]',
      );
      if (document.getElementById("manual-recovery-overlay")) return;
      const overlay = document.createElement("div");
      overlay.id = "manual-recovery-overlay";
      if (isModal) {
        overlay.className =
          "position-relative w-100 d-flex align-items-center justify-content-center p-3";
        Object.assign(overlay.style, {
          background: "rgba(255,255,255,.95)",
          minHeight: "300px",
        });
      } else {
        overlay.className =
          "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3";
        Object.assign(overlay.style, {
          zIndex: "2147483000",
          background: "rgba(0,0,0,.25)",
        });
      }
      for (const [k, v] of Object.entries({
        role: "dialog",
        "aria-modal": "true",
        "aria-labelledby": "diagTitle",
        "aria-describedby": "diagDesc",
      }))
        overlay.setAttribute(k, v);
      overlay.tabIndex = -1;
      const card = document.createElement("div");
      card.className = isModal ? "border-0" : "card shadow-lg border-0";
      for (const [k, v] of Object.entries({ maxWidth: "720px", width: "100%" }))
        (card.style as unknown as Record<string, string>)[k] = v;
      card.setAttribute("role", "document");
      const cardBody = document.createElement("div");
      cardBody.className = isModal
        ? "p-3 text-center"
        : "card-body p-4 text-center";
      const iconWrap = document.createElement("div");
      for (const [k, v] of Object.entries({
        className: "mb-3",
        innerHTML:
          '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning" aria-hidden="true"></i>',
      }))
        (iconWrap as unknown as Record<string, unknown>)[k] = v;
      const title = document.createElement("h1");
      for (const [k, v] of Object.entries({
        id: "diagTitle",
        className: isModal ? "h5 fw-bold mb-2" : "h4 fw-bold mb-2",
        textContent: "Unexpected Output Detected",
      }))
        (title as unknown as Record<string, unknown>)[k] = v;
      const desc = document.createElement("p");
      for (const [k, v] of Object.entries({
        id: "diagDesc",
        className: "text-muted mb-3",
        innerHTML: isModal
          ? "We found unexpected content in this modal. Please try closing and reopening it."
          : "We found unexpected content at the start of this page. You can safely navigate using the options below.",
      }))
        (desc as unknown as Record<string, unknown>)[k] = v;
      const imgWrap = document.createElement("div");
      imgWrap.className = "my-3";
      const img = document.createElement("img");
      for (const [k, v] of Object.entries({
        src: "/assets/images/406-art.webp",
        alt: "Illustration for error/406",
        className: "img-fluid rounded",
        loading: "lazy",
      }))
        (img as unknown as Record<string, unknown>)[k] = v;
      img.style.maxHeight = isModal ? "180px" : "240px";
      img.setAttribute("aria-hidden", "true");
      img.onerror = function (this: HTMLImageElement): void {
        const alert = document.createElement("div");
        alert.className =
          "alert alert-warning d-flex align-items-center justify-content-center gap-2 mt-3 mb-0";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          alert.setAttribute(k, v);
        alert.innerHTML =
          '<i class="bi bi-image-alt" aria-hidden="true"></i><div>Illustration failed to load.</div>';
        this.replaceWith(alert);
      };
      imgWrap.appendChild(img);
      const actions = document.createElement("div");
      actions.className =
        "d-grid gap-2 d-sm-flex justify-content-sm-center mt-3";
      /** Creates a button element with common defaults. */
      const mkBtn = (
        cls: string,
        label: string,
        html: string,
      ): HTMLButtonElement => {
        const b = document.createElement("button");
        b.type = "button";
        b.className = cls;
        b.setAttribute("aria-label", label);
        b.innerHTML = html;
        return b;
      };
      if (isModal) {
        const closeBtn = mkBtn(
          "btn btn-secondary",
          "Close this modal",
          '<i class="bi bi-x-lg" aria-hidden="true"></i> Close Modal',
        );
        closeBtn.setAttribute("data-bs-dismiss", "modal");
        closeBtn.onclick = (): void => {
          const modalElement = overlay.closest(".modal");
          if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
            else
              modalElement
                .querySelector<HTMLElement>('[data-bs-dismiss="modal"]')
                ?.click();
          }
          overlay.remove();
        };
        const reloadBtn = mkBtn(
          BTN_WARNING,
          RELOAD_LABEL,
          `${reloadIcon} Reload Page`,
        );
        reloadBtn.style.color = COLOR_WHITE;
        reloadBtn.onclick = (): void => location.reload();
        actions.append(closeBtn, reloadBtn);
      } else {
        const backBtn = mkBtn(
          "btn btn-primary",
          "Go back to previous page",
          '<i class="bi bi-arrow-left" aria-hidden="true"></i> Go Back',
        );
        backBtn.onclick = (): void => history.back();
        const homeA = document.createElement("a");
        for (const [k, v] of Object.entries({
          className: "btn btn-info",
          href: "/",
          innerHTML: '<i class="bi bi-house" aria-hidden="true"></i> Home',
        }))
          (homeA as unknown as Record<string, unknown>)[k] = v;
        homeA.style.color = COLOR_WHITE;
        homeA.setAttribute("aria-label", "Return to the home page");
        const reloadBtn = mkBtn(
          BTN_WARNING,
          RELOAD_LABEL,
          `${reloadIcon} Reload`,
        );
        reloadBtn.style.color = COLOR_WHITE;
        reloadBtn.onclick = (): void => location.reload();
        actions.append(backBtn, homeA, reloadBtn);
      }
      const hint = document.createElement("p");
      for (const [k, v] of Object.entries({
        className: "mt-3 small text-muted",
        innerHTML: isModal
          ? `${infoIcon} Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> to dismiss.`
          : `${infoIcon} Press <kbd style="margin-inline: 0.15rem;">Esc</kbd> to dismiss this message.`,
      }))
        (hint as unknown as Record<string, unknown>)[k] = v;
      cardBody.append(iconWrap, title, desc, imgWrap, actions, hint);
      for (const [p, c] of [
        [card, cardBody],
        [overlay, card],
        [targetElement, overlay],
      ] as [Node, Node][])
        p.appendChild(c);
      !isModal && document.body.classList.add("overflow-hidden");
      setTimeout((): void => overlay.focus(), 0);
      const onEsc = (e: KeyboardEvent): void => {
        if (e.key === "Escape") {
          document.removeEventListener("keydown", onEsc);
          overlay.remove();
          !isModal && document.body.classList.remove("overflow-hidden");
        }
      };
      document.addEventListener("keydown", onEsc, { once: true });
    };
    if (document.documentElement.dataset.checkMountedInit) return;
    document.documentElement.dataset.checkMountedInit = "1";
    document.readyState === "loading"
      ? document.addEventListener("DOMContentLoaded", checkMounted)
      : checkMounted();
  } catch (__moduleErr) {
    console.error("[checkMounted] failed to initialise:", __moduleErr);
  }
})();

export {};
