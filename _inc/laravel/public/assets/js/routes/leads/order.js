/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;

  const dataBindDrag = "data-dragula-bound";
  const dataBindPipe = "data-pipeline-bound";
  const ns = "._npLeads";
  const qs = (s, r = document) => r.querySelector(s);
  const csrf = () =>
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") ?? "";
  const bindDragula = () => {
    if (!(window.jQuery && window.jQuery.fn) || !ensureDragula()) return;
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
          if (guard.isInvalidUrl(url)) {
            guard.scheduleInteractiveError(guard.getMsg("route_unavailable"));
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
              guard.scheduleInteractiveError(
                guard.getMsg("leads_order_unavailable")
              );
            },
          });
        } catch (_) {
          guard.scheduleInteractiveError(
            guard.getMsg("leads_order_unavailable")
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
    if (!(window.jQuery && window.jQuery.fn)) return;
    if (document.body.getAttribute(dataBindPipe) === "true") return;
    document.body.setAttribute(dataBindPipe, "true");
    $(document).on("change" + ns, "#default_pipeline_id", function () {
      try {
        const $f = $("#change-pipeline");
        if ($f.length) {
          $f.trigger("submit");
        } else {
          guard.scheduleInteractiveError(guard.getMsg("form_unavailable"));
        }
      } catch (_) {
        guard.scheduleInteractiveError(guard.getMsg("form_unavailable"));
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
