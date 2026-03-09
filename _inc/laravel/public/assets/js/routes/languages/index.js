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

  const listenerAttr = "data-lang-disable-listener";
  $(() => {
    const bind = el => {
      if (!el || el.getAttribute(listenerAttr) === "true") return;
      el.setAttribute(listenerAttr, "true");
      const $el = $(el);
      const handler = function () {
        try {
          const isChecked = this.checked ?? false;
          const mode = isChecked ? "on" : "off";
          const url = el.getAttribute("data-url");
          if (routeGuard(el, url)) {
            scheduleError(getMsg("disable_lang_failed"));
            return;
          }
          const token = utils.getCsrfToken();
          $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: {
              _token: token,
              mode,
              lang: el.getAttribute("data-lang") ?? "",
            },
          })
            .done(data => show_toastr("success", data.message, "success"))
            .fail(() => scheduleError(getMsg("disable_lang_failed")));
        } catch {
          scheduleError(getMsg("disable_lang_failed"));
        }
      };
      $el.on("pointerup.langToggle", handler);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("pointerup.langToggle", handler);
          } catch {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };
    try {
      document
        .querySelectorAll(".custom_lang.form-check-input[type=checkbox]")
        .forEach(bind);
    } catch {
      $(".custom_lang.form-check-input[type=checkbox]").toArray().forEach(bind);
    }
  });
})();
