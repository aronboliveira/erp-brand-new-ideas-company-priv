(() => {
  const ERR_FB = "# ERROR";
  const CLIENT_FLAG = "data-client-localized";
  const GUARD_MSG = "data-guard-msg";
  const LANG_KEY = "erp-np-lang";

  const getMsg = (key, el) => {
    let msg = ERR_FB;
    if (el.getAttribute(CLIENT_FLAG) === "true") {
      msg = el.getAttribute(GUARD_MSG) || msg;
    } else {
      let lang = (
        sessionStorage.getItem(LANG_KEY) ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(GUARD_MSG) ||
        window.translations?.["en"]?.[key] ||
        msg;
      if (msg !== ERR_FB) {
        el.setAttribute(GUARD_MSG, msg);
        el.setAttribute(CLIENT_FLAG, "true");
      }
    }
    return msg;
  };

  const showError = message => {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      const hasBS =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap?.Toast;
      if (hasBS) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = message;
        toast.appendChild(body);
        container.appendChild(toast);
        bootstrap.Toast.getOrCreateInstance(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };

  document.addEventListener("DOMContentLoaded", () => {
    try {
      if (
        typeof jQuery === "undefined" ||
        typeof jQuery.fn.daterangepicker !== "function"
      ) {
        throw new Error("datepicker_plugin_unavailable");
      }
      const els = document.querySelectorAll(".datepicker");
      if (!els.length) return;
      els.forEach(el => {
        const locale = window.date_picker_locale || {};
        jQuery(el).daterangepicker({
          singleDatePicker: true,
          locale,
          locale: { ...locale, format: "YYYY-MM-DD" },
        });
      });
    } catch (e) {
      const key =
        e.message === "datepicker_plugin_unavailable"
          ? "datepicker_plugin_unavailable"
          : "datepicker_init_failed";
      const msg = getMsg(key, document.documentElement);
      showError(msg);
    }
  });
})();
