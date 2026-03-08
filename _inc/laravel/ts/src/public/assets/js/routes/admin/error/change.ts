/**
 * @fileoverview TypeScript version of public/assets/js/routes/admin/error/change.js
 * @generated from original JavaScript - manual review recommended
 * @module change
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

((): void => {
  const lang = (
    document.documentElement.getAttribute("lang") ?? "en"
  ).toLowerCase();
  const dict =
    (window.translations &&
      (window.translations[lang] || window.translations[lang.split("-")[0]])) ||
    window.translations?.en ||
    {};
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const tr = (k: string) => dict[k] || k;

  const showToastOrAlert = (msg: string): void=> {
    try {
      const hasToast = !!window.bootstrap.Toast;
      if (hasToast) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          c.style.position = "fixed";
          c.style.top = "1rem";
          c.style.right = "1rem";
          c.style.zIndex = "1080";
          document.body.appendChild(c);
        }
        const el = document.createElement("div");
        el.className = "toast align-items-center text-bg-danger border-0";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  el.setAttribute(k, v);
        {
          el.replaceChildren();
          const _d = document.createElement("div");
          _d.className = "d-flex";
          const _b = document.createElement("div");
          _b.className = "toast-body";
          _b.textContent = msg;
          const _c = document.createElement("button");
          _c.type = "button";
          _c.className = "btn-close btn-close-white me-2 m-auto";
          _c.dataset.bsDismiss = "toast";
          _c.setAttribute("aria-label", "Close");
          _d.append(_b, _c);
          el.append(_d);
        }
        c.appendChild(el);
        const t = new window.bootstrap.Toast(el, { delay: 3000 });
        t.show();
        setTimeout((): void => {
          el.remove();
        }, 3400);
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  const previewBinder = (inputId: string, imgId: string): void=> {
    try {
      const input = document.getElementById(inputId);
      const img = document.getElementById(imgId);
      if (!input || !img)
        throw new Error(
          `${tr("element_unavailable")} (${!input ? inputId : imgId})`,
        );
      input.addEventListener("change", (): void => {
        try {
          const file = (input as HTMLInputElement).files?.[0];
          if (!file) return;
          const URLAPI = window.URL || window.webkitURL;
          if (!URLAPI.createObjectURL) throw new Error(tr("request_failed"));
          const src = URLAPI.createObjectURL(file);
          (img as HTMLImageElement).src = src;
          (img as HTMLImageElement).onload = (): void => {
            try {
              URLAPI.revokeObjectURL(src);
            } catch (__err) {
    console.error(`[change] Error:`, __err);
  }
          };
        } catch (e) {
          // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument
          showToastOrAlert(e.message || tr("request_failed"));
        }
      });
    } catch (e) {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument
      showToastOrAlert(e.message || tr("request_failed"));
    }
  };

  const start = (): void => {
    previewBinder("home_banner", "image");
    previewBinder("home_logo", "image1");
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", start, { once: true });
  } else {
    start();
  }
})();

export {};
