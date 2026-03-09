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
    document.body.addEventListener("pointerup", handler, { once: true });
  };
  const routeGuard = (el, url) => {
    if (!url || url === "#") return true;
    return false;
  };

  const listenerAttr = "data-shipping-listener";
  $(() => {
    const bind = el => {
      if (!el || el.getAttribute(listenerAttr) === "true") return;
      el.setAttribute(listenerAttr, "true");
      const $el = $(el);
      const handler = function () {
        try {
          const url = el.getAttribute("data-url");
          if (routeGuard(el, url)) {
            scheduleError(getMsg("shipping_unavailable"));
            return;
          }
          const is_display = $("#shipping").is(":checked");
          $.ajax({
            url: url,
            type: "get",
            data: { is_display: is_display },
            success: data => {
              if (!data) {
                scheduleError(getMsg("shipping_unavailable"));
                return;
              }
              $("#shipping_name").val(data.name ?? "");
              $("#shipping_country")
                .val(data.country ?? "")
                .trigger("change");
              $("#shipping_state")
                .val(data.state ?? "")
                .trigger("change");
              $("#shipping_city")
                .val(data.city ?? "")
                .trigger("change");
              $("#shipping_phone").val(data.phone ?? "");
              $("#shipping_zip").val(data.zip ?? "");
              $("#shipping_address").val(data.address ?? "");
              $("#billing_name").val(data.billing_name ?? "");
              $("#billing_country")
                .val(data.billing_country ?? "")
                .trigger("change");
              $("#billing_state")
                .val(data.billing_state ?? "")
                .trigger("change");
              $("#billing_city")
                .val(data.billing_city ?? "")
                .trigger("change");
              $("#billing_phone").val(data.billing_phone ?? "");
              $("#billing_zip").val(data.billing_zip ?? "");
              $("#billing_address").val(data.billing_address ?? "");
            },
            error: () => scheduleError(getMsg("shipping_unavailable")),
          });
        } catch {
          scheduleError(getMsg("shipping_unavailable"));
        }
      };
      $el.on("pointerup.shipGuard", handler);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("pointerup.shipGuard", handler);
          } catch {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };
    try {
      document.querySelectorAll("#same-as-billing, #shipping").forEach(bind);
    } catch {
      $("#same-as-billing, #shipping").toArray().forEach(bind);
    }
  });
})();
