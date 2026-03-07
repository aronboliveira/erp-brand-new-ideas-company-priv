/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/preview.js
 * @generated from original JavaScript - manual review recommended
 * @module preview
 */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const DATA_LISTENER_ADDED = "data-listener-added";

  const getMsg = (el: HTMLElement, msgKey: string) => {
    let msg = errFb;
    if (
      el?.getAttribute("data-sv-localized") === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb) {
        el?.setAttribute(dataGuardMsg, msg);
        el?.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const showFeedback = (el: HTMLElement, key: string, ev: string = "click") => {
    const text = getMsg(el ?? document.body, key);
    const hasBs =
      document.querySelector('link[href*="bootstrap"]') &&
      window.bootstrap.Toast;
    if (hasBs) {
      let toast = document.querySelector<HTMLElement>("#np-error-toast");
      if (!toast) {
        toast = document.createElement("div");
        toast.id = "np-error-toast";
        toast.className = "toast align-items-center text-bg-danger border-0";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        toast.innerHTML = `
            <div class="d-flex">
              <div class="toast-body">${text}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
        document.body.appendChild(toast);
      }
      const handler = (): void => {
        new bootstrap.Toast(toast).show();
      };
      document.addEventListener(ev, handler, { once: true });
      const mo = new MutationObserver((_, o) => {
        if (!document.body.contains(toast)) {
          document.removeEventListener(ev, handler);
          o.disconnect();
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    } else {
      const handler = (): void => {
        alert(text);
      };
      document.addEventListener(ev, handler, { once: true });
    }
  };

  const guardOnce = (el: HTMLElement, key: string, ev: string = "click") => {
    if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
    const handler = (): void => {
      showFeedback(el, key, ev);
    };
    el.addEventListener(ev, handler, { once: true });
    el.setAttribute(DATA_LISTENER_ADDED, "true");
    const mo = new MutationObserver((_, o) => {
      if (!document.body.contains(el)) {
        el.removeEventListener(ev, handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const bindImagePreview = (): void => {
    try {
      const handler = function (): void {
        try {
          const file = this?.files?.[0];
          if (!file) return;
          const $img = $("#image");
          if (!$img.length) {
            guardOnce(this, "image_preview_unavailable");
            return;
          }
          const prev = this.getAttribute("data-prev-url") ?? "";
          const url = URL.createObjectURL(file);
          $img.attr("src", url);
          if (prev) {
            try {
              URL.revokeObjectURL(prev);
            } catch {}
          }
          this.setAttribute("data-prev-url", url);
        } catch {
          guardOnce(this, "image_preview_unavailable");
        }
      };

      const $input = $("#pro_image");
      if ($input.length && $input.attr("data-np-bound") !== "true") {
        $input.on("change", handler);
        $input.attr("data-np-bound", "true");
        const el = $input.get(0);
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(el)) {
            $input.off("change", handler);
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      }

      if (document.body.getAttribute("data-np-delegate-img") !== "true") {
        $(document).on("change", "#pro_image", function (): void {
          if ($(this).attr("data-np-bound") === "true") return;
          handler.call(this);
        });
        document.body.setAttribute("data-np-delegate-img", "true");
      }
    } catch {
      guardOnce(document.body, "image_preview_unavailable");
    }
  };

  const bindQuantityToggle = (): void => {
    try {
      if (document.body.getAttribute("data-np-qty-bound") === "true") return;
      $(document).on("click", ".type", function (): void {
        try {
          const type = String($(this).val() ?? "").toLowerCase();
          const $q = $(".quantity");
          if (!$q.length) return;
          if (type === "product") {
            $q.removeClass("d-none").addClass("d-block");
          } else {
            $q.addClass("d-none").removeClass("d-block");
          }
        } catch {
          guardOnce(this, "toggle_quantity_unavailable");
        }
      });
      document.body.setAttribute("data-np-qty-bound", "true");
    } catch {
      guardOnce(document.body, "toggle_quantity_unavailable");
    }
  };

  try {
    if (typeof $ === "undefined") {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery failed to load");
      return;
    }
    bindImagePreview();
    bindQuantityToggle();
  } catch (e) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Initialization failed", e);
  }
})();

export {};
