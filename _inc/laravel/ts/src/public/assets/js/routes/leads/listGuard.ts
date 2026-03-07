/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/listGuard.js
 * @generated from original JavaScript - manual review recommended
 * @module listGuard
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataGuardListener = "data-guard-listener";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const msgKey = "leads_unavailable";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement | null) => {
    let msg = errFb;
    try {
      if (!el) return msg;
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
    } catch {
      return errFb;
    }
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
  const showError = (el: HTMLElement | null): void=> {
    try {
      const message = getMsg(el);
      if (hasBootstrapCss() && window.bootstrap.Toast) {
        let wrap = document.getElementById("toast-container");
        if (!wrap) {
          wrap = document.createElement("div");
          wrap.id = "toast-container";
          document.body.appendChild(wrap);
        }
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = message;
        t.appendChild(b);
        wrap.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t, {
          autohide: true,
          delay: 4000,
        }).show();
      } else {
        alert(message);
      }
    } catch {
      alert(errFb);
    }
  };
  try {
    const jq = window.jQuery ?? (window.$?.fn ? window.$ : null);
    if (!jq) {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery not found for leads list");
      return;
    }
    jq((): void => {
      const bind = (el: HTMLElement | null): void=> {
        if (!el || el.getAttribute(dataGuardListener) === "true") return;
        el.setAttribute(dataGuardListener, "true");
        const $el = jq(el);
        const onClick = (e: Event): void=> {
          try {
            const url = el.getAttribute("data-url");
            const href = el.getAttribute("href");
            if ((!url || url === "#") && (!href || href === "#")) {
              e.preventDefault();
              showError(el);
            }
          } catch {
            e.preventDefault();
            showError(el);
          }
        };
        $el.on("click.leadsGuard", onClick);
        const obs = new MutationObserver((): void => {
          if (!document.body.contains(el)) {
            try {
              $el.off("click.leadsGuard", onClick);
            } catch {}
            obs.disconnect();
          }
        });
        obs.observe(document.body, { childList: true, subtree: true });
      };
      try {
        document
          .querySelectorAll(".lead-route-guard")
          .forEach(el => bind(el as HTMLElement));
      } catch {
        jq(".lead-route-guard").toArray().forEach(bind);
      }
    });
  } catch {
    try {
      alert(errFb);
    } catch {}
  }
})();

export {};
