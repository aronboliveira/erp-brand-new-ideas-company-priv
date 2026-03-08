/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/order.js
 * @generated from original JavaScript - manual review recommended
 * @module order
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-leads-error";
  const dataBindDrag = "data-dragula-bound";
  const dataBindPipe = "data-pipeline-bound";
  const ns = "._npLeads";
  const qs = (
    s: string,
    r: Document | HTMLElement = document,
  ): HTMLElement | null => r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBS = () =>
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
    if (hasBS()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
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
  const schedulePointerupError = (msg: string): void=> {
    const host = document.body;
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
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
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
    return msg;
  };
  const csrf = (): string => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    return meta?.getAttribute("content") ?? "";
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const verifyRoute = (candidate: string) => {
    const a = document.createElement("a");
    a.setAttribute("data-url", candidate ?? "");
    a.href = candidate ?? "";
    const url = a.getAttribute("data-url");
    const href = a.href;
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
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
    console.error(`[order] Error:`, _);
  }
      schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
      return false;
    }
    return true;
  };
  const ensureDragula = (): boolean => {
    if (typeof window.dragula === "function") return true;
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Dragula unavailable");
    } catch (_) {
    console.error(`[order] Error:`, _);
  }
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    schedulePointerupError(getMsg(document.body, "dragula_unavailable"));
    return false;
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const bindDragula = () => {
    if (!ensureJq() || !ensureDragula()) return;
    if (document.body.getAttribute(dataBindDrag) === "true") return;
    document.body.setAttribute(dataBindDrag, "true");
    $('[data-plugin="dragula"]').each(function () {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
      const $root = $(this);
      const containers = $root.data("containers") as string[] | undefined;
      let nodes: HTMLElement[] = [];
      if (containers?.length) {
        // eslint-disable-next-line @typescript-eslint/prefer-for-of
        for (let i = 0; i < containers.length; i++) {
          const el = document.getElementById(containers[i]);
          if (el) nodes.push(el);
        }
      } else {
        const rootEl = $root.get(0);
        if (rootEl) nodes = [rootEl];
      }
      const handleCls = $root.data("handleclass") as string | undefined;
      const dragulaFn = window.dragula;
      if (typeof dragulaFn !== "function") return;
      const drake = handleCls
        ? dragulaFn(nodes, {
            moves: function (
              el: HTMLElement,
              src: HTMLElement,
              handle: HTMLElement | undefined,
            ) {
              return handle?.classList.contains(handleCls) ?? false;
            },
          })
        : dragulaFn(nodes);
      drake.on(
        "drop",
        function (el: HTMLElement, target: HTMLElement, source: HTMLElement) {
          try {
            const order: (string | undefined)[] = [];
            $("#" + target.id + " > div").each(function (): void {
              // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
              order[$(this).index()] = $(this).attr("data-id");
            });
            const id = $(el).attr("data-id");
            const old_status = $("#" + source.id).data("status") as
              | string
              | undefined;
            const new_status = $("#" + target.id).data("status") as
              | string
              | undefined;
            const stage_id = $(target).attr("data-id");
            const pipeline_id = "{{$pipeline->id}}";
            $("#" + source.id)
              .parent()
              .find(".count")
              .text(String($("#" + source.id + " > div").length));
            $("#" + target.id)
              .parent()
              .find(".count")
              .text(String($("#" + target.id + " > div").length));
            const url = "{{route('leads.order')}}";
            if (!verifyRoute(url)) {
              schedulePointerupError(
                getMsg(document.body, "route_unavailable"),
              );
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
              error: function (_xhr: JQueryXHR): void {
                schedulePointerupError(
                  getMsg(document.body, "leads_order_unavailable"),
                );
              },
            });
          } catch (_) {
            schedulePointerupError(
              getMsg(document.body, "leads_order_unavailable"),
            );
          }
        },
      );
    });
    const mo = new MutationObserver(function (): void {
      if (!$('[data-plugin="dragula"]').length) {
        document.body.removeAttribute(dataBindDrag);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const bindPipelineChange = (): void => {
    if (!ensureJq()) return;
    if (document.body.getAttribute(dataBindPipe) === "true") return;
    document.body.setAttribute(dataBindPipe, "true");
    $(document).on("change" + ns, "#default_pipeline_id", function (): void {
      try {
        const $f = $("#change-pipeline");
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
      { once: true },
    );
  } else {
    bindDragula();
    bindPipelineChange();
  }
})();

export {};
