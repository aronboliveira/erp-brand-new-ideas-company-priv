/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/chatify/pusher.js
 * @generated from original JavaScript - manual review recommended
 * @module pusher
 */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-pusher-error";
  const dataInitGuard = "data-pusher-initialized";
  const qs = (s: string, r: Document | HTMLElement = document) =>
    r.querySelector(s);
  const hasBootstrap = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap?.Toast;
  const ensureToastContainer = (): HTMLDivElement => {
    const existing = qs("#np-toast-container") as HTMLDivElement | null;
    if (existing) return existing;
    const c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string) => {
    if (hasBootstrap()) {
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
  const schedulePointerupError = (message: string) => {
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
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
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
  const getCsrf = (): string => {
    try {
      const t = $?.('meta[name="csrf-token"]').attr("content");
      return t ? String(t) : "";
    } catch (_) {
      const m = document.querySelector('meta[name="csrf-token"]');
      return m?.getAttribute("content") ?? "";
    }
  };
  const initPusher = (): void => {
    if (document.body.getAttribute(dataInitGuard) === "true") return;
    document.body.setAttribute(dataInitGuard, "true");
    try {
      if (!window.Pusher) {
        try {
          console.error("Pusher library unavailable");
        } catch (_) {}
        schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
        return;
      }
      try {
        (window.Pusher as Record<string, unknown>).logToConsole = true;
      } catch (_) {}
      const key: string = "{{ config('chatify.pusher.key') }}";
      const cluster: string = "{{ config('chatify.pusher.options.cluster') }}";
      const authEndpoint: string = '{{route("pusher.auth")}}';
      if (!key || key === "#" || !cluster || cluster === "#") {
        schedulePointerupError(getMsg(document.body, "pusher_unavailable"));
        return;
      }
      if (!authEndpoint || authEndpoint === "#") {
        schedulePointerupError(
          getMsg(document.body, "pusher_auth_unavailable"),
        );
        return;
      }
      const headers = { "X-CSRF-TOKEN": getCsrf() };
      const PusherCtor = window.Pusher as unknown as new (
        key: string,
        opts: Record<string, unknown>,
      ) => { connection?: { bind: (event: string, cb: () => void) => void } };
      const pusher = new PusherCtor(key, {
        encrypted: true,
        cluster: cluster,
        authEndpoint: authEndpoint,
        auth: { headers: headers },
      });
      if (!pusher?.connection) {
        schedulePointerupError(getMsg(document.body, "pusher_unavailable"));
        return;
      }
      pusher.connection.bind("error", function (): void {
        schedulePointerupError(getMsg(document.body, "pusher_connect_failed"));
      });
      window.__appPusher = pusher;
    } catch (_) {
      schedulePointerupError(getMsg(document.body, "pusher_unavailable"));
    }
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initPusher, { once: true });
  } else {
    initPusher();
  }
})();

export {};
