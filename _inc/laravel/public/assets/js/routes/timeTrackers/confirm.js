(function () {
  const $ = window.jQuery;
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("click", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";
  const dataBound = "data-confirm-bound";
  const dataFallbackBound = "data-confirm-fallback";

  const bindConfirmModals = () => {
    if (!$ || !$.fn) {
      document
        .querySelectorAll("[data-confirm-delete]")
        .forEach(el => scheduleError(getMsg("plugin_unavailable")));
      return;
    }
    const hasFireModal = typeof $.fn.fireModal === "function";
    document.querySelectorAll("[data-confirm-delete]").forEach(el => {
      const me = $(el);
      if (el.getAttribute(dataBound) === "true") {
        return;
      }
      el.setAttribute(dataBound, "true");
      let meData = me.data("confirm-delete");
      meData = meData == null ? "" : String(meData);
      const parts = meData.split("|");
      const title = parts[0] ?? "";
      const body = parts.slice(1).join("|") ?? "";
      if (hasFireModal) {
        try {
          me.fireModal({
            title: title,
            body: body,
            buttons: [
              {
                text: me.data("confirm-text-yes") || "Yes",
                class: "btn btn-sm btn-danger rounded-pill",
                handler: function (modal) {
                  try {
                    const yesCode = me.data("confirm-yes");
                    if (yesCode) {
                      eval(yesCode);
                    }
                  } catch (_) {}
                  try {
                    if ($.destroyModal) {
                      $.destroyModal(modal);
                    } else {
                      $(modal).remove();
                    }
                  } catch (_) {}
                },
              },
              {
                text: me.data("confirm-text-cancel") || "Cancel",
                class: "btn btn-sm btn-secondary rounded-pill",
                handler: function (modal) {
                  try {
                    if ($.destroyModal) {
                      $.destroyModal(modal);
                    } else {
                      $(modal).remove();
                    }
                  } catch (_) {}
                  try {
                    const noCode = me.data("confirm-no");
                    if (noCode) {
                      eval(noCode);
                    }
                  } catch (_) {}
                },
              },
            ],
          });
        } catch (_) {
          scheduleError(getMsg("confirm_unavailable"));
        }
      } else {
        if (el.getAttribute(dataFallbackBound) !== "true") {
          el.setAttribute(dataFallbackBound, "true");
          const handler = function () {
            scheduleError(getMsg("confirm_unavailable"));
          };
          $(el).on("click.confirmFallback", handler);
        }
      }
    });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bindConfirmModals, {
      once: true,
    });
  } else {
    bindConfirmModals();
  }
})();
