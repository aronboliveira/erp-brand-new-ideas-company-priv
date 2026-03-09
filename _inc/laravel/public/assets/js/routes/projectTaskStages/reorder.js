(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);
  const scheduleError = msg => {
    let errorMessage = msg;
    const handler = () => {
      if (errorMessage) {
        showError(errorMessage);
        errorMessage = null;
      }
    };
    ["pointerup", "click", "dragend"].forEach(ev => {
      document.body.addEventListener(ev, handler, { once: true });
    });
  };

  const initGuard = "data-sortable-init";
  const initSortable = () => {
    if (!$.fn?.sortable) {
      scheduleError(getMsg("plugin_unavailable"));
      return;
    }
    const $lists = $(".sortable");
    if (!$lists.length) return;
    $lists.each(function () {
      const el = this;
      if (el.getAttribute(initGuard) === "true") return;
      el.setAttribute(initGuard, "true");
      try {
        const $el = $(el);
        if (typeof $el.disableSelection === "function") {
          $el.disableSelection();
        }
        $el.sortable();
        $el.sortable({
          stop: function () {
            try {
              const order = [];
              $(this)
                .find("li")
                .each(function (i, li) {
                  order[i] = $(li).attr("data-id") ?? $(li).data("id") ?? "";
                });
              const explicit = "{{route('project-task-stages.order')}}";
              const url = el.getAttribute("data-url");
              if (!url && !explicit) {
                scheduleError(getMsg("reorder_unavailable"));
                return;
              }
              const endpoint = url && url !== "#" ? url : explicit;
              if (!endpoint || endpoint === "#") {
                scheduleError(getMsg("reorder_unavailable"));
                return;
              }
              const token = utils.getCsrfToken();
              $.ajax({
                url: endpoint,
                type: "POST",
                data: { order: order },
                headers: { "X-CSRF-TOKEN": token },
                cache: false,
                error: () => scheduleError(getMsg("ajax_unavailable")),
              });
            } catch {
              scheduleError(getMsg("reorder_unavailable"));
            }
          },
        });
        const obs = new MutationObserver(() => {
          if (!document.body.contains(el)) {
            try {
              $(el).sortable("destroy");
            } catch {}
            obs.disconnect();
          }
        });
        obs.observe(document.body, { childList: true, subtree: true });
      } catch {
        scheduleError(getMsg("plugin_unavailable"));
      }
    });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSortable, { once: true });
  } else {
    initSortable();
  }
})();
