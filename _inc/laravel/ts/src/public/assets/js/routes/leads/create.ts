/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */

/* global bootstrap, $, jQuery */
(function (): void {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataGuardListener = "data-guard-listener";
  const msgKey = "ld_unavailable";
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
  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
  const showError = (el: HTMLElement | null) => {
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
        console.error("jQuery not found for LD scripts");
      return;
    }
    jq((): void => {
      const bindClick = (el: HTMLElement | null) => {
        if (!el || el.getAttribute(dataGuardListener) === "true") return;
        el.setAttribute(dataGuardListener, "true");
        const $el = jq(el);
        const handler = (e: Event) => {
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
        $el.on("click.ldGuard", handler);
        const obs = new MutationObserver((): void => {
          if (!document.body.contains(el)) {
            try {
              $el.off("click.ldGuard", handler);
            } catch {}
            obs.disconnect();
          }
        });
        obs.observe(document.body, { childList: true, subtree: true });
      };
      const bindPointerUp = (el: HTMLElement | null) => {
        if (!el || el.getAttribute(dataGuardListener) === "true") return;
        el.setAttribute(dataGuardListener, "true");
        const $el = jq(el);
        const handler = (e: Event) => {
          try {
            const url = el.getAttribute("data-url");
            const href = (el as HTMLInputElement).form
              ? (el as HTMLInputElement).form!.getAttribute("action")
              : el.getAttribute("action");
            if ((!url || url === "#") && (!href || href === "#")) {
              e.preventDefault();
              showError(el);
            }
          } catch {
            e.preventDefault();
            showError(el);
          }
        };
        $el.on("pointerup.ldSubmitGuard", handler);
        const obs = new MutationObserver((): void => {
          if (!document.body.contains(el)) {
            try {
              $el.off("pointerup.ldSubmitGuard", handler);
            } catch {}
            obs.disconnect();
          }
        });
        obs.observe(document.body, { childList: true, subtree: true });
      };
      try {
        document
          .querySelectorAll(".ld-route-guard")
          .forEach(el => bindClick(el as HTMLElement));
      } catch {
        jq(".ld-route-guard").toArray().forEach(bindClick);
      }
      const submitEl = document.getElementById("lead-submit");
      if (submitEl) bindPointerUp(submitEl);
    });
  } catch {
    try {
      alert(errFb);
    } catch {}
  }
})();

export {};
