/**
 * @fileoverview TypeScript version of public/assets/js/routes/auth/login/submit.js
 * @generated from original JavaScript - manual review recommended
 * @module submit
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataSubmitGuard = "data-submit-guard";
  const qs = <T extends Element = HTMLElement>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector<T>(s);
  const ensureToastContainer = (): HTMLDivElement => {
    const id = "np-toast-container";
    let c = qs<HTMLDivElement>("#" + id);
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = id;
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string): void=> {
    const hasBootstrapLink =
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]');
    if (hasBootstrapLink && window.bootstrap.Toast) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleInteractiveError = (message: string): void=> {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("click", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
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
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    return msg;
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const bindSubmit = () => {
    const form = qs("#form_data");
    if (!form) {
      return;
    }
    if (form.getAttribute(dataSubmitGuard) === "true") {
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      return;
    }
    form.setAttribute(dataSubmitGuard, "true");
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const handler = function (_e: Event) {
      try {
        const btn = qs("#login_button");
        if (btn) {
          btn.setAttribute("disabled", "true");
          return true;
        } else {
          scheduleInteractiveError(getMsg(form, "login_submit_unavailable"));
          return true;
        }
      } catch (_) {
        scheduleInteractiveError(getMsg(form, "login_submit_unavailable"));
        return true;
      }
    };
    $(form).on("submit.loginGuard", handler);
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(form)) {
        $(form).off("submit.loginGuard");
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const init = (): void => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {
    console.error(`[submit] Error:`, _);
  }
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    if (document.readyState === "loading") {
      $(function (): void {
        bindSubmit();
      });
    } else {
      bindSubmit();
    }
  };
  init();
})();

export {};
