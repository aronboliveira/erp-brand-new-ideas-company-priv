/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/list.js
 * @generated from original JavaScript - manual review recommended
 * @module list
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery;
  const errFb = "# ERROR",
    dataClientLocalized = "data-client-localized",
    dataGuardMsg = "data-guard-msg",
    dataSvLocalized = "data-sv-localized",
    dataErrArmed = "data-pipeline-error-armed",
    dataBound = "data-pipeline-bound",
    selector = '.change-pipeline select[name="default_pipeline_id"]';
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const qs = (s: string, r: Document | Element = document) =>
    r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBS = () =>
    !!(
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      (
        qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')
      )
    ) && !!window.bootstrap.Toast;
  const ensureToastContainer = (): HTMLDivElement => {
    const existing = qs("#np-toast-container") as HTMLDivElement | null;
    if (existing) return existing;
    const c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string): void => {
    if (hasBS()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = t.querySelector(".toast-body");
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
  const scheduleClickError = (msg: string): void => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrArmed) === "true") return;
    host.setAttribute(dataErrArmed, "true");
    const once = (): void => {
      try {
        showErrorNow(msg);
      } finally {
        host.removeAttribute(dataErrArmed);
      }
    };
    document.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((_m, o) => {
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
  const verifyRoute = (form: HTMLFormElement | null) => {
    const url = form?.getAttribute("data-url") ?? "",
      href = form?.action ?? "";
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
  };
  const bind = (): void => {
    if (!$?.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {
        console.error(`[list] Error:`, _);
      }
      scheduleClickError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    const body = document.body;
    if (body.getAttribute(dataBound) === "true") return;
    body.setAttribute(dataBound, "true");
    const handler = function (): void {
      try {
        const formEl = qs("#change-pipeline");
        if (!formEl) {
          scheduleClickError(getMsg(body, "pipeline_unavailable"));
          return;
        }
        if (!verifyRoute(formEl as HTMLFormElement)) {
          scheduleClickError(getMsg(body, "route_unavailable"));
          return;
        }
        $(formEl).trigger("submit");
      } catch (_) {
        scheduleClickError(getMsg(body, "pipeline_unavailable"));
      }
    };
    $(document).on("change", selector, handler);
    const mo = new MutationObserver(function (): void {
      if (!document.querySelector(selector)) {
        try {
          $(document).off("change", selector);
        } catch (_) {
          console.error(`[list] Error:`, _);
        }
        body.removeAttribute(dataBound);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", bind, { once: true })
    : bind();
})();

export {};
