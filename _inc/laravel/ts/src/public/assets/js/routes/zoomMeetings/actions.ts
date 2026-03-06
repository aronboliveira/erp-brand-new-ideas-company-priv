/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/actions.js
 * @generated from original JavaScript - manual review recommended
 * @module actions
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataInit = "data-zoomdel-bound";
  const dataErr = "data-zoomdel-error";
  const ns = "._npZoomDel";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = (): void => {
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
  const showErrorNow = message => {
    if (hasBS()) {
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
  const schedulePointerupError = msg => {
    const host = document.body;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!host || host.getAttribute(dataErr) === "true") return;
    host.setAttribute(dataErr, "true");
    const once = (): void => {
      try {
        showErrorNow(msg);
      } finally {
        host.removeAttribute(dataErr);
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
  const localize = (el, key) => {
    const err = errFb;
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    if (
      el?.getAttribute?.("data-sv-localized") === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      return el.getAttribute(dataGuardMsg) || err;
    }
    let lang = (
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      window.sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ?? "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    const msgKey = key;
    const msg =
      window.translations?.[lang]?.[msgKey] ||
      el?.getAttribute?.(dataGuardMsg) ||
      window.translations?.en?.[msgKey] ||
      err;
    if (msg !== err && el) {
      el.setAttribute(dataGuardMsg, msg);
      el.setAttribute(dataClientLocalized, "true");
    }
    return msg;
  };
  const ensureJq = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      schedulePointerupError(localize(document.body, "plugin_unavailable"));
      return false;
    }
    return true;
  };
  const ensureConfirm = (): void => {
    const btn = $(".confirm_yes");
    if (btn.length === 0) {
      schedulePointerupError(localize(document.body, "confirm_unavailable"));
      return null;
    }
    return btn;
  };
  const buildDeleteUrl = (id, el) => {
    const url = el?.getAttribute?.("data-url");
    const href = el?.getAttribute?.("href");
    if ((!url || url === "#") && (!href || href === "#")) {
      const fallback =
        "{{ url('zoom-meeting') }}".replace(/\/$/, "") + "/" + id;
      return fallback;
    }
    return url && url !== "#" ? url : href;
  };
  const onOpenConfirm = function (): void {
    const rid = this.getAttribute("data-id") || $(this).attr("data-id") ?? "";
    const $c = ensureConfirm();
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$c) return;
    $c.removeClass("m_remove");
    $c.addClass("m_remove");
    $c.attr("uid", rid);
    try {
      $("#cModal").modal("show");
    } catch (_) {
      schedulePointerupError(localize(document.body, "confirm_unavailable"));
    }
  };
  const onConfirmDelete = function (): void {
    const id = this.getAttribute("uid") ?? "";
    const targetEl = this;
    const url = buildDeleteUrl(id, targetEl);
    const urlAttr = targetEl.getAttribute("data-url");
    const hrefAttr = targetEl.getAttribute("href");
    if (
      (!urlAttr || urlAttr === "#") &&
      (!hrefAttr || hrefAttr === "#") &&
      (!url || url === "#")
    ) {
      schedulePointerupError(localize(targetEl, "route_unavailable"));
      return;
    }
    if (typeof window.deleteAjax !== "function") {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("deleteAjax unavailable");
      } catch (_) {}
      schedulePointerupError(localize(targetEl, "plugin_unavailable"));
      return;
    }
    const data = { id: id };
    window.deleteAjax(url, data, function (res) {
      try {
        if (typeof window.toastrs === "function") {
          window.toastrs(res?.flag, res?.msg);
        }
        if (res?.flag === 1) {
          window.location.reload();
        }
        try {
          $("#cModal").modal("hide");
        } catch (_) {}
      } catch (_) {
        schedulePointerupError(localize(targetEl, "delete_unavailable"));
      }
    });
  };
  const bind = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!ensureJq()) return;
    const host = document.body;
    if (host.getAttribute(dataInit) === "true") return;
    host.setAttribute(dataInit, "true");
    $(document).on("click" + ns, ".member_remove", onOpenConfirm);
    $(document).on("click" + ns, ".confirm_yes.m_remove", onConfirmDelete);
    const mo = new MutationObserver(function (): void {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!$(".member_remove").length && !$(".confirm_yes.m_remove").length) {
        $(document).off("click" + ns, ".member_remove", onOpenConfirm);
        $(document).off("click" + ns, ".confirm_yes.m_remove", onConfirmDelete);
        host.removeAttribute(dataInit);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  } else {
    bind();
  }
})();

export {};
