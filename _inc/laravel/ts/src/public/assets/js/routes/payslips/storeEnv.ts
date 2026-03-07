/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/storeEnv.js
 * @generated from original JavaScript - manual review recommended
 * @module storeEnv
 */


// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-env-error";
  const qs = <T extends Element = HTMLElement>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBootstrapUi = () =>
    !!(
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap.Toast;
  const ensureToastContainer = (): HTMLElement => {
    let c = qs("#np-toast-container");
    if (c) return c;
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string): void=> {
    if (hasBootstrapUi()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) body.textContent = message ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleClickError = (message: string): void=> {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
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
  const safeDisplay = (el: HTMLElement | null, show: boolean) => {
    if (!el) return false;
    el.style.display = show ? "block" : "none";
    return true;
  };
  const checkEnvironment = (val?: unknown): void=> {
    try {
      const el = qs("#environment_text_input");
      const ok = safeDisplay(el, val === "other");
      if (!ok)
        scheduleClickError(
          getMsg(el ?? document.body, "env_toggle_unavailable"),
        );
    } catch (_) {
      scheduleClickError(getMsg(document.body, "env_toggle_unavailable"));
    }
  };
  const showDatabaseSettings = (): void => {
    try {
      const el = qs<HTMLInputElement>("#tab2");
      if (!el) {
        scheduleClickError(getMsg(document.body, "tab_db_unavailable"));
        return;
      }
      el.checked = true;
    } catch (_) {
      scheduleClickError(getMsg(document.body, "tab_db_unavailable"));
    }
  };
  const showApplicationSettings = (): void => {
    try {
      const el = qs<HTMLInputElement>("#tab3");
      if (!el) {
        scheduleClickError(getMsg(document.body, "tab_app_unavailable"));
        return;
      }
      el.checked = true;
    } catch (_) {
      scheduleClickError(getMsg(document.body, "tab_app_unavailable"));
    }
  };
  window.checkEnvironment = checkEnvironment;
  window.showDatabaseSettings = showDatabaseSettings;
  window.showApplicationSettings = showApplicationSettings;
})();

export {};
