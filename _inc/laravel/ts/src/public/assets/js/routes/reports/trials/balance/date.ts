/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/trials/balance/date.js
 * @generated from original JavaScript - manual review recommended
 * @module date
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

(function (): void {
  const $ = window.jQuery;
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const qs = (s: string, r: Document | Element = document) =>
      r.querySelector(s),
    errFb = "# ERROR",
    dataClientLocalized = "data-client-localized",
    dataGuardMsg = "data-guard-msg",
    dataSvLocalized = "data-sv-localized",
    dataErrGuard = "data-error-guard",
    dataInitGuard = "data-date-sync-init",
    dataListenerGuard = "data-date-sync-listener";
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery unavailable");
    } catch (_) {
      console.error(`[date] Error:`, _);
    }
    scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
    return;
  }
  const ensureToastContainer = (): HTMLDivElement => {
    const id = "np-toast-container";
    let c = qs("#" + id) as HTMLDivElement | null;
    if (c) return c;
    c = document.createElement("div");
    c.id = id;
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string): void => {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer(),
        tid = "np-toast";
      let t = qs("#" + tid, container);
      if (!t) {
        t = document.createElement("div");
        t.id = tid;
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
  function scheduleInteractiveError(message: string): void {
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
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("click", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  }
  function getMsg(el: HTMLElement, key: string): string {
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
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  }
  const bindWithObserver = (
    el: HTMLElement,
    evt: string,
    handler: (e: Event) => void,
    flag: string,
  ): void => {
    if (!el || el.getAttribute(flag) === "true") return;
    el.setAttribute(flag, "true");
    $(el).on(evt, handler);
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(el)) {
        $(el).off(evt, handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const syncDates = (): void => {
    try {
      const startVal = $(".startDate").val() ?? "",
        endVal = $(".endDate").val() ?? "";
      if (!($(".start_date").length + $(".end_date").length > 0)) {
        scheduleInteractiveError(getMsg(document.body, "date_sync_failed"));
        return;
      }
      $(".start_date").val(startVal);
      $(".end_date").val(endVal);
    } catch (_) {
      scheduleInteractiveError(getMsg(document.body, "date_sync_failed"));
    }
  };
  const init = (): void => {
    const root = document.documentElement;
    if (root.getAttribute(dataInitGuard) === "true") return;
    root.setAttribute(dataInitGuard, "true");
    syncDates();
    document.querySelectorAll(".startDate, .endDate").forEach(function (
      el: Element,
    ) {
      bindWithObserver(
        el as HTMLElement,
        "change",
        syncDates,
        dataListenerGuard,
      );
    });
  };
  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", init, { once: true })
    : init();
})();

export {};
