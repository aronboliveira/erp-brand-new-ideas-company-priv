/**
 * @fileoverview TypeScript version of public/assets/js/routes/users/profiles/scroll.js
 * @generated from original JavaScript - manual review recommended
 * @module scroll
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBindGuard = "data-scrollspy-bound";
  const dataClickGuard = "data-listgroup-click-bound";
  const dataErrGuard = "data-scrollspy-error";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToast = (): void => {
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
  const showErrorNow = msg => {
    if (hasBS()) {
      const container = ensureToast();
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
      const body = t.querySelector(".toast-body");
      if (body) body.textContent = msg ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(msg ?? errFb);
      }
    } else {
      alert(msg ?? errFb);
    }
  };
  const scheduleClickError = msg => {
    const host = document.body;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(msg);
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
  };
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const initScrollSpy = (): void => {
    const target = qs("#useradd-sidenav") || document.body;
    if (target.getAttribute(dataBindGuard) === "true") return;
    target.setAttribute(dataBindGuard, "true");
    try {
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      if (!window.bootstrap.ScrollSpy) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("bootstrap.ScrollSpy unavailable");
        } catch (_) {}
        scheduleClickError(getMsg(target, "scrollspy_unavailable"));
        return;
      }
      new window.bootstrap.ScrollSpy(document.body, {
        target: "#useradd-sidenav",
        offset: 300,
      });
    } catch (_) {
      scheduleClickError(getMsg(target, "scrollspy_unavailable"));
    }
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(target)) {
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const bindListClicks = (): void => {
    const root = document.body;
    if (root.getAttribute(dataClickGuard) === "true") return;
    root.setAttribute(dataClickGuard, "true");
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      return;
    }
    const ns = ".lgitem";
    $(document).on("click" + ns, ".list-group-item", function (): void {
      try {
        const href = this.getAttribute("href") ?? "";
        const $all = $(".list-group-item");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
        if ($all && $all.length) {
          $all
            .filter(function (): void {
              return (this.getAttribute("href") ?? "") === href;
            })
            .parent()
            .removeClass("text-primary");
        }
      } catch (_) {}
    });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(root)) {
        $(document).off(ns);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const init = (): void => {
    initScrollSpy();
    bindListClicks();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
