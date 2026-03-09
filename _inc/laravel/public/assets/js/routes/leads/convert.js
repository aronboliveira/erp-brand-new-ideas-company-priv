(function () {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    return;
  }

  const L1 = "data-client-toggle-listener";
  const L2 = "data-guard-listener";

  function toggleBlocks(isExist) {
    try {
      const $ = window.jQuery;
      if (!$) return;
      const $exist = $(".exist_client");
      const $new = $(".new_client");
      if (isExist) {
        $exist.removeClass("d-none");
        $new.addClass("d-none");
        $new.find("input").removeAttr("required");
      } else {
        $exist.addClass("d-none");
        $new.removeClass("d-none");
        $new.find("input").attr("required", "required");
      }
    } catch (_) {}
  }
  function bindToggle() {
    try {
      const $ = window.jQuery;
      if (!$) return;
      const $radios = $('input[name="client_check"]');
      if (!$radios.length) return;
      const el = $radios.get(0);
      if (el.getAttribute(L1) === "true") return;
      el.setAttribute(L1, "true");
      const initVal = $radios.filter(":checked").val();
      toggleBlocks(initVal === "exist");
      $radios.off("click.convertDeal").on("click.convertDeal", function () {
        try {
          toggleBlocks(this.value === "exist");
        } catch (_) {}
      });
      let obs = new MutationObserver(function () {
        if (!document.body.contains(el)) {
          try {
            $radios.off("click.convertDeal");
          } catch (_) {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    } catch (_) {}
  }
  function bindSubmitGuard() {
    try {
      const $ = window.jQuery;
      if (!$) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("jQuery not found for convertDeal");
        } catch (_) {}
        return;
      }
      const form = document.getElementById("lead-convert-form");
      const btn = document.getElementById("lead-convert-submit");
      if (!form || !btn) return;
      if (form.getAttribute(L2) === "true") return;
      form.setAttribute(L2, "true");
      $(btn)
        .off("click.convertDealGuard")
        .on("click.convertDealGuard", function (e) {
          try {
            const url = form.getAttribute("data-url");
            const href = form.action;
            if ((!url || url === "#") && (!href || href === "#")) {
              e.preventDefault();
              const msg =
                form.getAttribute("data-guard-msg") ||
                getMsg("action_unavailable");
              scheduleError(msg, "click");
            }
          } catch (_) {
            e.preventDefault();
            const msg =
              form.getAttribute("data-guard-msg") ||
              getMsg("action_unavailable");
            scheduleError(msg, "click");
          }
        });
      let obs = new MutationObserver(function () {
        if (!document.body.contains(form) || !document.body.contains(btn)) {
          try {
            $(btn).off("click.convertDealGuard");
          } catch (_) {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    } catch (_) {}
  }
  try {
    const $ = window.jQuery;
    if (!$) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Failed to initialize convertDeal: jQuery missing");
      } catch (_) {}
      return;
    }
    $(function () {
      bindToggle();
      bindSubmitGuard();
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to initialize convertDeal");
    } catch (__) {}
  }
})();
