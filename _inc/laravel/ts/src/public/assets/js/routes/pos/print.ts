/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/print.js
 * @generated from original JavaScript - manual review recommended
 * @module print
 */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const guardListener = "data-guard-listener";
  const getMsg = (el: HTMLElement | null) => {
    let msg = errFb;
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
      const msgKey = "print_unavailable";
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
  };
  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
  const showError = (el: HTMLElement | null) => {
    const message = getMsg(el);
    if (hasBootstrapCss() && window.bootstrap) {
      const wrapId = "toast-wrap-print-guard";
      if (!document.getElementById(wrapId)) {
        const wrap = document.createElement("div");
        wrap.id = wrapId;
        wrap.className = "position-fixed top-0 end-0 p-3";
        wrap.style.zIndex = "1080";
        document.body.appendChild(wrap);
      }
      const t = document.createElement("div");
      t.className = "toast align-items-center text-bg-danger border-0";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      t.innerHTML =
        '<div class="d-flex"><div class="toast-body">' +
        message +
        '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
      document.getElementById("toast-wrap-print-guard")?.appendChild(t);
      new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
    } else {
      alert(message);
    }
  };
  const onClick = (e: Event) => {
    const $ = window.jQuery;
    const btn = e.currentTarget as HTMLElement | null;
    if (!$) {
      showError(btn);
      return;
    }
    try {
      const $rows = $(".row");
      const $toasts = $(".toast");
      const $btn = $("#print");
      $rows.addClass("d-none");
      $toasts.addClass("d-none");
      $btn.addClass("d-none");
      window.print();
      $rows.removeClass("d-none");
      $btn.removeClass("d-none");
      $toasts.removeClass("d-none");
    } catch {
      showError(btn);
    }
  };
  const attach = (): void => {
    const $ = window.jQuery;
    if (!$) return;
    const btn = $("#print");
    if (btn.length === 0) return;
    const el = btn.get(0);
    if (el.getAttribute(guardListener) === "true") return;
    el.setAttribute(guardListener, "true");
    btn.on("click", onClick);
  };
  const detachIfGone = (): void => {
    const btn = document.getElementById("print");
    if (!btn) return;
    const observer = new MutationObserver((): void => {
      if (!document.body.contains(btn)) {
        try {
          if (window.jQuery) window.jQuery("#print").off("click", onClick);
        } catch {}
        observer.disconnect();
      }
    });
    observer.observe(document.body, { childList: true, subtree: true });
  };
  if (window.jQuery) {
    jQuery((): void => {
      attach();
      detachIfGone();
    });
  } else {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery not found while initializing print handler");
    } catch {}
  }
})();

export {};
