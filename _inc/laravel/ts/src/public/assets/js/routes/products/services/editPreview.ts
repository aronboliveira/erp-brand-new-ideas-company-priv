/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/editPreview.js
 * @generated from original JavaScript - manual review recommended
 * @module editPreview
 */


((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const DATA_LISTENER_ADDED = "data-listener-added";

  const getMsg = (el: HTMLElement, msgKey: string): string=> {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
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
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const showFeedback = (el: HTMLElement, key: string, ev = "click"): void=> {
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

  const guardOnce = (el: HTMLElement, key: string, ev = "click"): void=> {
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

  try {
    if (typeof $ === "undefined") {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery failed to load");
      return;
    }

    const $imgInput = $("#pro_image");
    const $img = $("#image");
    if ($imgInput.length) {
      const onImgChange = function (): void {
        try {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-assignment
          const file = this?.files?.[0];
          if (!file || !$img.length) {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            guardOnce(this, "image_preview_unavailable");
            return;
          }
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          // eslint-disable-next-line @typescript-eslint/no-unsafe-call
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-assignment
          const prev = this.getAttribute("data-prev-url") ?? "";
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          const url = URL.createObjectURL(file);
          $img.attr("src", url);
          if (prev) {
            try {
              // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
              URL.revokeObjectURL(prev);
            } catch {}
          }
          // eslint-disable-next-line @typescript-eslint/no-unsafe-call
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
          this.setAttribute("data-prev-url", url);
        } catch {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          guardOnce(this, "image_preview_unavailable");
        }
      };
      if ($imgInput.attr("data-np-bound") !== "true") {
        $imgInput.on("change", onImgChange);
        $imgInput.attr("data-np-bound", "true");
        const el = $imgInput.get(0);
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(el)) {
            $imgInput.off("change", onImgChange);
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      }
    }

    if (document.body.getAttribute("data-np-qty-bound") !== "true") {
      $(document).on("click", ".type", function (): void {
        try {
          const isProduct =
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            String($(this).val() ?? "").toLowerCase() === "product";
          const $qty = $(".quantity");
          if (!$qty.length) {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            guardOnce(this, "toggle_quantity_unavailable");
            return;
          }
          $qty
            .toggleClass("d-none", !isProduct)
            .toggleClass("d-block", isProduct);
        } catch {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          guardOnce(this, "toggle_quantity_unavailable");
        }
      });
      document.body.setAttribute("data-np-qty-bound", "true");
    }
  } catch (e) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Initialization failed", e);
  }
})();

export {};
