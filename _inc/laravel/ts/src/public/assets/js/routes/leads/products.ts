/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/products.js
 * @generated from original JavaScript - manual review recommended
 * @module products
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const L = "data-guard-listener",
    DCL = "data-client-localized",
    DGM = "data-guard-msg",
    DSL = "data-sv-localized";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const ERR = "# ERROR";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  function hasBootstrapCss() {
    try {
      return !!document.querySelector(
        'link[rel~="stylesheet"][href*="bootstrap"]',
      );
    } catch (_) {
      return false;
    }
  }
  function toast(msg: string): void {
    try {
      if (hasBootstrapCss() && window.bootstrap.Toast) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast";
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = msg;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(msg);
      }
    } catch (_) {
      alert(msg);
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    }
  }
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  function getMsg(el: HTMLElement, key: string) {
    try {
      let msg = ERR;
      if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true")
        msg = el.getAttribute(DGM) || ERR;
      else {
        let lang = (
          window.sessionStorage.getItem("erp-np-lang") ??
          document.documentElement.lang ??
          "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          window.translations?.[lang]?.[key] ||
          el.getAttribute(DGM) ||
          window.translations?.en?.[key] ||
          ERR;
        if (msg !== ERR) {
          el.setAttribute(DGM, msg);
          el.setAttribute(DCL, "true");
        }
      }
      return msg || ERR;
    } catch (_) {
      return ERR;
    }
  }
  function bindSubmitGuard(): void {
    try {
      const $ = window.jQuery;
      if (!$) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("jQuery not found for leads/productsUpdate");
        } catch (_) {
          console.error(`[products] Error:`, _);
        }
        return;
      }
      const form = document.getElementById(
          "lead-products-update-form",
        ) as HTMLFormElement | null,
        btn = document.getElementById("lead-products-update-submit");
      if (!form || !btn) return;
      if (form.getAttribute(L) === "true") return;
      form.setAttribute(L, "true");
      $(btn)
        .off("click.leadsProductsUpdate")
        .on("click.leadsProductsUpdate", function (e: Event) {
          try {
            const url = form.getAttribute("data-url"),
              href = form.action;
            if ((!url || url === "#") && (!href || href === "#")) {
              e.preventDefault();
              toast(getMsg(form as HTMLElement, "action_unavailable"));
            }
          } catch (_) {
            e.preventDefault();
            toast(getMsg(form as HTMLElement, "action_unavailable"));
          }
        });
      const obs = new MutationObserver(function (): void {
        if (!document.body.contains(form) || !document.body.contains(btn)) {
          try {
            $(btn).off("click.leadsProductsUpdate");
          } catch (_) {
            console.error(`[products] Error:`, _);
          }
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    } catch (_) {
      console.error(`[products] Error:`, _);
    }
  }
  try {
    const $ = window.jQuery;
    if (!$) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Failed to initialize leads/productsUpdate");
      } catch (_) {
        console.error(`[products] Error:`, _);
      }
      return;
    }
    $(function (): void {
      bindSubmitGuard();
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to run leads/productsUpdate");
    } catch (__) {
      console.error(`[products] Error:`, __);
    }
  }
})();

export {};
