(function () {
  const L1 = "data-client-toggle-listener";
  const L2 = "data-guard-listener";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  const ERR = "# ERROR";
  function hasBootstrapCss() {
    try {
      return !!document.querySelector(
        'link[rel~="stylesheet"][href*="bootstrap"]'
      );
    } catch (_) {
      return false;
    }
  }
  function toast(msg) {
    try {
      if (hasBootstrapCss() && window.bootstrap && window.bootstrap.Toast) {
        var c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        var t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        var b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = msg;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(msg);
      }
    } catch (_) {
      alert(msg);
    }
  }
  function getMsg(el, key) {
    try {
      let msg = ERR;
      if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true")
        msg = el.getAttribute(DGM) || ERR;
      else {
        let lang = (
          window.sessionStorage.getItem("erp-np-lang") ||
          document.documentElement.lang ||
          "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          window.translations?.[lang]?.[key] ||
          el.getAttribute(DGM) ||
          window.translations?.["en"]?.[key] ||
          ERR;
        if (msg !== ERR) {
          el.setAttribute(DGM, msg);
          el.setAttribute(DCL, "true");
        }
      }
      return msg || ERR;
    } catch (_) {
      return ERR;
    }
  }
  function toggleBlocks(isExist) {
    try {
      var $ = window.jQuery;
      if (!$) return;
      var $exist = $(".exist_client");
      var $new = $(".new_client");
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
      var $ = window.jQuery;
      if (!$) return;
      var $radios = $('input[name="client_check"]');
      if (!$radios.length) return;
      var el = $radios.get(0);
      if (el.getAttribute(L1) === "true") return;
      el.setAttribute(L1, "true");
      var initVal = $radios.filter(":checked").val();
      toggleBlocks(initVal === "exist");
      $radios.off("click.convertDeal").on("click.convertDeal", function () {
        try {
          toggleBlocks(this.value === "exist");
        } catch (_) {}
      });
      var obs = new MutationObserver(function () {
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
      var $ = window.jQuery;
      if (!$) {
        try {
          console.error("jQuery not found for convertDeal");
        } catch (_) {}
        return;
      }
      var form = document.getElementById("lead-convert-form");
      var btn = document.getElementById("lead-convert-submit");
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
              toast(getMsg(form, "action_unavailable"));
            }
          } catch (_) {
            e.preventDefault();
            toast(getMsg(form, "action_unavailable"));
          }
        });
      var obs = new MutationObserver(function () {
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
    var $ = window.jQuery;
    if (!$) {
      try {
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
      console.error("Failed to initialize convertDeal");
    } catch (__) {}
  }
})();
