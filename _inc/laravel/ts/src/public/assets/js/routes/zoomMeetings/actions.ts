/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/actions.js
 * @generated from original JavaScript - manual review recommended
 * @module actions
 */

import type { DeleteAjaxResponse } from "../../../../../declarations/routes/ajax-responses.interfaces";

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const errFb = "# ERROR",
    _dataClientLocalized = "data-client-localized",
    _dataGuardMsg = "data-guard-msg",
    _dataSvLocalized = "data-sv-localized",
    dataInit = "data-zoomdel-bound",
    dataErr = "data-zoomdel-error",
    ns = "._npZoomDel";
  const qs = (
    s: string,
    r: Document | HTMLElement = document,
  ): HTMLElement | null => r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ??
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap.Toast;
  const ensureToastContainer = (): HTMLElement => {
    const existing = qs("#np-toast-container");
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
        const el = document.createElement("div");
        el.id = "np-toast";
        el.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          el.setAttribute(k, v);
        el.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(el);
        t = el;
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
  const schedulePointerupError = (msg: string): void => {
    const host = document.body;
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
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const localize = (el: HTMLElement, key: string) => {
    const err = errFb,
      dataClientLocalized = "data-client-localized",
      dataGuardMsg = "data-guard-msg";
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    )
      return el.getAttribute(dataGuardMsg) || err;
    let lang = (
      window.sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ??
      "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    const msgKey = key;
    const msg =
      window.translations?.[lang]?.[msgKey] ||
      el.getAttribute(dataGuardMsg) ||
      window.translations?.en?.[msgKey] ||
      err;
    if (msg !== err && el) {
      el.setAttribute(dataGuardMsg, msg);
      el.setAttribute(dataClientLocalized, "true");
    }
    return msg;
  };
  const ensureJq = (): boolean => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {
        console.error(`[actions] Error:`, _);
      }
      schedulePointerupError(localize(document.body, "plugin_unavailable"));
      return false;
    }
    return true;
  };
  const ensureConfirm = (): JQuery<HTMLElement> | null => {
    const btn = $(".confirm_yes");
    if (btn.length === 0) {
      schedulePointerupError(localize(document.body, "confirm_unavailable"));
      return null;
    }
    return btn;
  };
  const buildDeleteUrl = (id: string | number, el: HTMLElement): string => {
    const url = el.getAttribute("data-url"),
      href = el.getAttribute("href");
    if ((!url || url === "#") && (!href || href === "#")) {
      const fallback =
        "{{ url('zoom-meeting') }}".replace(/\/$/, "") + "/" + id;
      return fallback;
    }
    return url && url !== "#" ? url : (href ?? "");
  };
  const onOpenConfirm = function (this: HTMLElement): void {
    const rid = this.getAttribute("data-id") || ($(this).attr("data-id") ?? "");
    const $c = ensureConfirm();
    if (!$c) return;
    $c.removeClass("m_remove");
    $c.addClass("m_remove");
    $c.attr("uid", rid);
    try {
      (
        $("#cModal") as JQuery<HTMLElement> & {
          modal: (action: string) => void;
        }
      ).modal("show");
    } catch (_) {
      schedulePointerupError(localize(document.body, "confirm_unavailable"));
    }
  };
  const onConfirmDelete = function (this: HTMLElement): void {
    const id = String(this.getAttribute("uid") ?? ""),
      targetEl = this,
      url = buildDeleteUrl(id, targetEl),
      urlAttr = targetEl.getAttribute("data-url"),
      hrefAttr = targetEl.getAttribute("href");
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
      } catch (_) {
        console.error(`[actions] Error:`, _);
      }
      schedulePointerupError(localize(targetEl, "plugin_unavailable"));
      return;
    }
    const data = { id: id };
    window.deleteAjax(url, data, function (res: DeleteAjaxResponse) {
      try {
        if (typeof window.toastr === "object" && window.toastr)
          window.toastr.success(String(res.msg ?? ""));
        if (res.flag === 1) window.location.reload();
        try {
          (
            $("#cModal") as JQuery<HTMLElement> & {
              modal: (action: string) => void;
            }
          ).modal("hide");
        } catch (_) {
          console.error(`[actions] Error:`, _);
        }
      } catch (_) {
        schedulePointerupError(localize(targetEl, "delete_unavailable"));
      }
    });
  };
  const bind = (): void => {
    if (!ensureJq()) return;
    const host = document.body;
    if (host.getAttribute(dataInit) === "true") return;
    host.setAttribute(dataInit, "true");
    $(document).on(
      "click" + ns,
      ".member_remove",
      function (this: HTMLElement) {
        onOpenConfirm.call(this);
      },
    );
    $(document).on(
      "click" + ns,
      ".confirm_yes.m_remove",
      function (this: HTMLElement) {
        onConfirmDelete.call(this);
      },
    );
    const mo = new MutationObserver(function (): void {
      if (!$(".member_remove").length && !$(".confirm_yes.m_remove").length) {
        $(document).off("click" + ns, ".member_remove");
        $(document).off("click" + ns, ".confirm_yes.m_remove");
        host.removeAttribute(dataInit);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", bind, { once: true })
    : bind();
})();

export {};
