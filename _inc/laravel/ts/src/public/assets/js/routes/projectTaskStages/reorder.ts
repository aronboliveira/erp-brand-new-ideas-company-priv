/**
 * @fileoverview TypeScript version of public/assets/js/routes/projectTaskStages/reorder.js
 * @generated from original JavaScript - manual review recommended
 * @module reorder
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const qs = (s, r = document) => r.querySelector(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataSortGuard = "data-sort-guard";
  const ensureToastContainer = (): void => {
    const id = "np-toast-container";
    let c = qs("#" + id);
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
  const showErrorNow = message => {
    const hasBootstrapLink =
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]');
    if (hasBootstrapLink) {
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
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
        new (window.bootstrap && window.bootstrap.Toast
          ? window.bootstrap.Toast
          : function (): void {
              return {
                show: function (): void {
                  alert(message ?? errFb);
                },
              };
            })(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleInteractiveError = message => {
    const host = document.body;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
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
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute(dataSvLocalized) === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
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
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const initSortable = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    if (!$.fn.sortable) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery UI sortable unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    const $lists = $(".sortable");
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (!$lists.length) {
      return;
    }
    $lists.each(function (): void {
      const el = this;
      if (el.getAttribute(dataSortGuard) === "true") {
        return;
      }
      el.setAttribute(dataSortGuard, "true");
      try {
        const $el = $(el);
        if (typeof $el.disableSelection === "function") {
          $el.disableSelection();
        }
        $el.sortable();
        $el.sortable({
          stop: function (): void {
            try {
              const order = [];
              $(this)
                .find("li")
                .each(function (i, li) {
                  order[i] = $(li).attr("data-id") ?? $(li).data("id") ?? "";
                });
              const explicit = "{{route('project-task-stages.order')}}";
              const url = el.getAttribute("data-url");
              const href =
                el.tagName === "FORM"
                  ? el.getAttribute("action") ?? ""
                  : el.getAttribute("href") ?? "";
              if (
                (!url || url === "#") &&
                (!href || href === "#") &&
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
                (!explicit || explicit === "#")
              ) {
                scheduleInteractiveError(getMsg(el, "reorder_unavailable"));
                return;
              }
              const endpoint =
                url && url !== "#"
                  ? url
                  : href && href !== "#"
                  ? href
                  : explicit;
              const token = $('meta[name="csrf-token"]').attr("content") ?? "";
              $.ajax({
                url: endpoint,
                type: "POST",
                data: { order: order },
                // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
                headers: token ? { "X-CSRF-TOKEN": token } : undefined,
                cache: false,
                success: function (): void {},
                error: function (): void {
                  scheduleInteractiveError(getMsg(el, "ajax_unavailable"));
                },
              });
            } catch (_) {
              scheduleInteractiveError(getMsg(el, "reorder_unavailable"));
            }
          },
        });
        const mo = new MutationObserver((m, o) => {
          if (!document.body.contains(el)) {
            try {
              $(el).sortable("destroy");
            } catch (_) {}
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      } catch (_) {
        scheduleInteractiveError(getMsg(el, "plugin_unavailable"));
      }
    });
  };
  const init = (): void => {
    initSortable();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
