(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;

  if (!$) {
    console.error("jQuery unavailable");
    return;
  }

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const init = () => {
    try {
      const $radios = $("input[name='client_check']");
      const $existWrap = $(".exist_client");
      const $newWrap = $(".new_client");
      const $name = $("#client_name");
      const $email = $("#client_email");
      const $password = $("#client_password");

      const safeToggle = mode => {
        const exist = mode === "exist";
        if ($existWrap.length) $existWrap.toggleClass("d-none", !exist);
        if ($newWrap.length) $newWrap.toggleClass("d-none", exist);
        if ($name.length)
          exist
            ? $name.removeAttr("required")
            : $name.attr("required", "required");
        if ($email.length)
          exist
            ? $email.removeAttr("required")
            : $email.attr("required", "required");
        if ($password.length)
          exist
            ? $password.removeAttr("required")
            : $password.attr("required", "required");
      };

      const current = ($radios.filter(":checked").val() || "new").toLowerCase();
      safeToggle(current);

      $(document).on("click", "input[name='client_check']", function () {
        try {
          const mode = String($(this).val() || "new").toLowerCase();
          safeToggle(mode);
        } catch (_) {
          scheduleError(getMsg("request_failed"), "click");
        }
      });
    } catch (_) {
      scheduleError(getMsg("init_failed"), "click");
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
