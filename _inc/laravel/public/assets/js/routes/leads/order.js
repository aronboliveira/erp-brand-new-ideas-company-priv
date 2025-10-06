(function () {
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
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = () => {
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
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = () => {
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
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
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
  const csrf = () =>
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") ?? "";
  const verifyRoute = candidate => {
    const a = document.createElement("a");
    a.setAttribute("data-url", candidate ?? "");
    a.href = candidate ?? "";
    const url = a.getAttribute("data-url");
    const href = a.href;
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
  };
  const ensureJq = () => {
    if (!$ || !$.fn) {
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
  const ensureDragula = () => {
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
  const bindDragula = () => {
    if (!ensureJq() || !ensureDragula()) return;
    if (document.body.getAttribute(dataBindDrag) === "true") return;
    document.body.setAttribute(dataBindDrag, "true");
    $('[data-plugin="dragula"]').each(function () {
      const $root = $(this);
      const containers = $root.data("containers");
      let nodes = [];
      if (containers && containers.length) {
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
          $("#" + target.id + " > div").each(function () {
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
              order: order ?? [],
              new_status: new_status ?? "",
              old_status: old_status ?? "",
              pipeline_id: pipeline_id ?? "",
              _token: csrf(),
            },
            success: function () {},
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
    const mo = new MutationObserver(function () {
      if (!$('[data-plugin="dragula"]').length) {
        document.body.removeAttribute(dataBindDrag);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const bindPipelineChange = () => {
    if (!ensureJq()) return;
    if (document.body.getAttribute(dataBindPipe) === "true") return;
    document.body.setAttribute(dataBindPipe, "true");
    $(document).on("change" + ns, "#default_pipeline_id", function () {
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
    const mo = new MutationObserver(function () {
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
      function () {
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
