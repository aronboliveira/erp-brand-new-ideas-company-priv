(() => {
  const { guard, utils } = window.ERPBootstrap.require("ERPGuard", "ERPUtils");
  if (!guard) return;
  const dataBindGuard = "data-copy-billing-bound";

  const showErrorNow = message => {
    guard.showToast(message);
  };

  const getMsg = (el, key) => {
    return utils.getTranslation(key) || "# ERROR";
  };
  const copyValue = (from, to) => {
    const $from = $(`[name='${from}']`);
    const $to = $(`[name='${to}']`);
    if (!$from.length || !$to.length) return false;
    const v = ($from.val() ?? "").toString();
    $to.val(v);
    return true;
  };
  const handler = function () {
    try {
      if (!$ || !$.fn) {
        try {
          console.error("jQuery unavailable");
        } catch (_) {}
        guard.error(guard.getMsg("plugin_unavailable"));
        return;
      }
      const ok = [
        ["billing_name", "shipping_name"],
        ["billing_country", "shipping_country"],
        ["billing_state", "shipping_state"],
        ["billing_city", "shipping_city"],
        ["billing_phone", "shipping_phone"],
        ["billing_zip", "shipping_zip"],
        ["billing_address", "shipping_address"],
      ]
        .map(function (p) {
          return copyValue(p[0], p[1]);
        })
        .every(function (x) {
          return x;
        });
      if (!ok) guard.error(guard.getMsg("copy_billing_unavailable"));
    } catch (_) {
      guard.error(guard.getMsg("copy_billing_unavailable"));
    }
  };
  const bind = () => {
    const host = document.body;
    if (host.getAttribute(dataBindGuard) === "true") return;
    host.setAttribute(dataBindGuard, "true");
    if ($ && $.fn) {
      $(document).on("click._npCopy", "#billing_data", handler);
    } else {
      document.addEventListener("click", function (e) {
        const t = e.target;
        if (t && (t.id === "billing_data" || t.closest?.("#billing_data")))
          handler.call(t);
      });
    }
    const mo = new MutationObserver(function () {
      if (!document.querySelector("#billing_data")) {
        if ($ && $.fn) {
          $(document).off("click._npCopy", "#billing_data");
        }
        host.removeAttribute(dataBindGuard);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  } else {
    bind();
  }
})();
