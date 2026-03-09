(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);
  const scheduleError = msg => {
    document.addEventListener("pointerup", () => showError(msg), { once: true });
  };

  const routeGuard = (element, alt) => {
    const url = element?.getAttribute?.("data-url");
    const href = element?.action ?? element?.href;
    return (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#");
  };

  const initChoices = () => {
    if (!$(".multi-select").length) return;
    if (typeof window.Choices !== "function") {
      scheduleError(getMsg("choices_unavailable"));
      return;
    }
    $(".multi-select").each((_, element) => {
      const id = element?.id;
      if (!id) return;
      if (element.getAttribute("data-choices-init") === "true") return;
      try {
        new Choices(`#${id}`, { removeItemButton: true });
        element.setAttribute("data-choices-init", "true");
      } catch {
        scheduleError(getMsg("choices_unavailable"));
      }
    });
  };

  const onClientChange = e => {
    const clientId = $(e.currentTarget).val() ?? "";
    getParent(clientId, e.currentTarget);
  };

  const getParent = (bid, targetEl) => {
    const base = `{{ url('contracts/clients/select') }}`;
    const url = `${base}/${encodeURIComponent(bid ?? "")}`;
    if (!bid || routeGuard(null, url)) {
      scheduleError(getMsg("project_list_unavailable"));
      return;
    }
    $.ajax({
      url,
      type: "GET",
      success: data => {
        try {
          const $select = $("#project_id");
          if (!$select.length) {
            scheduleError(getMsg("project_list_unavailable"));
            return;
          }
          $select.empty();
          if (Array.isArray(data) && data.length) {
            data.forEach(item => {
              if (!item) return;
              const val = item.id ?? "";
              const text = item.name ?? "";
              if (String(val).length)
                $select.append(`<option value="${String(val)}">${String(text)}</option>`);
            });
          }
          if (typeof window.Choices === "function" && !$select[0].getAttribute("data-choices-init")) {
            try {
              new Choices("#project_id", { removeItemButton: true });
              $select[0].setAttribute("data-choices-init", "true");
            } catch {
              scheduleError(getMsg("choices_unavailable"));
            }
          }
        } catch {
          scheduleError(getMsg("project_list_unavailable"));
        }
      },
      error: () => scheduleError(getMsg("project_list_unavailable")),
    });
  };

  initChoices();
  $(document).on("change", ".client_select", onClientChange);
})();
