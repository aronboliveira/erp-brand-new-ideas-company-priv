/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/order.js
 * @generated from original JavaScript - manual review recommended
 * @module order
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars, @typescript-eslint/prefer-for-of */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-leads-error";
  const dataBindDrag = "data-dragula-bound";
  const dataBindPipe = "data-pipeline-bound";
  const ns = "._npLeads";
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
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(msg);
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
  const csrf = () =>
    document
      .querySelector('meta[name="csrf-token"]')
      .getAttribute("content") ?? "";
  const verifyRoute = candidate => {
    const a = document.createElement("a");
    a.setAttribute("data-url", candidate ?? "");
    a.href = candidate ?? "";
    const url = a.getAttribute("data-url");
    const href = a.href;
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
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
      schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
      return false;
    }
    return true;
  };
  const ensureDragula = (): void => {
    if (typeof window.dragula === "function") return true;
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Dragula unavailable");
    } catch (_) {}
    schedulePointerupError(getMsg(document.body, "dragula_unavailable"));
    return false;
  };
  const bindDragula = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!ensureJq() || !ensureDragula()) return;
    if (document.body.getAttribute(dataBindDrag) === "true") return;
    document.body.setAttribute(dataBindDrag, "true");
    $('[data-plugin="dragula"]').each(function (): void {
      const $root = $(this);
      const containers = $root.data("containers");
      let nodes = [];
      if (containers?.length) {
        for (let i = 0; i < containers.length; i++) {
          const el = document.getElementById(containers[i]);
          if (el) nodes.push(el);
        }
      } else {
        nodes = [$root.get(0)];
      }
      const handleCls = $root.data("handleclass");
      const drake = handleCls
        ? window.dragula(nodes, {
            moves: function (el, src, handle) {
              return handle?.classList?.contains(handleCls);
            },
          })
        : window.dragula(nodes);
      drake.on("drop", function (el, target, source) {
        try {
          const order = [];
          $("#" + target.id + " > div").each(function (): void {
            order[$(this).index()] = $(this).attr("data-id");
          });
          const id = $(el).attr("data-id");
          const old_status = $("#" + source.id).data("status");
          const new_status = $("#" + target.id).data("status");
          const stage_id = $(target).attr("data-id");
          const pipeline_id = "{{$pipeline->id}}";
          $("#" + source.id)
            .parent()
            .find(".count")
            .text($("#" + source.id + " > div").length);
          $("#" + target.id)
            .parent()
            .find(".count")
            .text($("#" + target.id + " > div").length);
          const url = "{{route('leads.order')}}";
          if (!verifyRoute(url)) {
            schedulePointerupError(getMsg(document.body, "route_unavailable"));
            return;
          }
          $.ajax({
            url: url,
            type: "POST",
            data: {
              lead_id: id ?? "",
              stage_id: stage_id ?? "",
              order: order,
              new_status: new_status ?? "",
              old_status: old_status ?? "",
              pipeline_id: pipeline_id,
              _token: csrf(),
            },
            success: function (): void {},
            error: function (xhr) {
              schedulePointerupError(
                getMsg(document.body, "leads_order_unavailable")
              );
            },
          });
        } catch (_) {
          schedulePointerupError(
            getMsg(document.body, "leads_order_unavailable")
          );
        }
      });
    });
    const mo = new MutationObserver(function (): void {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!$('[data-plugin="dragula"]').length) {
        document.body.removeAttribute(dataBindDrag);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const bindPipelineChange = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!ensureJq()) return;
    if (document.body.getAttribute(dataBindPipe) === "true") return;
    document.body.setAttribute(dataBindPipe, "true");
    $(document).on("change" + ns, "#default_pipeline_id", function (): void {
      try {
        const $f = $("#change-pipeline");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if ($f.length) {
          $f.trigger("submit");
        } else {
          schedulePointerupError(getMsg(document.body, "form_unavailable"));
        }
      } catch (_) {
        schedulePointerupError(getMsg(document.body, "form_unavailable"));
      }
    });
    const mo = new MutationObserver(function (): void {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!$("#default_pipeline_id").length) {
        $(document).off("change" + ns, "#default_pipeline_id");
        document.body.removeAttribute(dataBindPipe);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener(
      "DOMContentLoaded",
      function (): void {
        bindDragula();
        bindPipelineChange();
      },
      { once: true }
    );
  } else {
    bindDragula();
    bindPipelineChange();
  }
})();

export {};
