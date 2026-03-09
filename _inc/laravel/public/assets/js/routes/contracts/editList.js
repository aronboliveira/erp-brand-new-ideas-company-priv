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
    const $ms = $(".multi-select");
    if (!$ms.length) return;
    if (typeof window.Choices !== "function") {
      scheduleError(getMsg("choices_unavailable"));
      return;
    }
    $ms.each((_, el) => {
      const id = el?.id;
      if (!id) return;
      if (el.getAttribute("data-choices-init") === "true") return;
      try {
        new Choices(`#${id}`, { removeItemButton: true });
        el.setAttribute("data-choices-init", "true");
      } catch {
        scheduleError(getMsg("choices_unavailable"));
      }
    });
  };

  const getParent = (bid, sourceEl) => {
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
          const $sel = $("#project_id");
          if (!$sel.length) {
            scheduleError(getMsg("project_list_unavailable"));
            return;
          }
          $sel.empty();
          if (Array.isArray(data) && data.length) {
            data.forEach(it => {
              if (!it) return;
              const val = String(it.id ?? "");
              const text = String(it.name ?? "");
              if (val.length) $sel.append(`<option value="${val}">${text}</option>`);
            });
          }
          if (typeof window.Choices === "function" && !$sel[0].getAttribute("data-choices-init")) {
            try {
              new Choices("#project_id", { removeItemButton: true });
              $sel[0].setAttribute("data-choices-init", "true");
            } catch {
              scheduleError(getMsg("choices_unavailable"));
            }
          }
          if (!Array.isArray(data) || !data.length) $sel.empty();
        } catch {
          scheduleError(getMsg("project_list_unavailable"));
        }
      },
      error: () => scheduleError(getMsg("project_list_unavailable")),
    });
  };

  initChoices();
  $(document).on("change", ".client_select", function () {
    const client_id = $(this).val();
    getParent(client_id, this);
  });
})();
