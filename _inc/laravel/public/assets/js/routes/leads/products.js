(function () {
  const L = "data-guard-listener";
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
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast";
        const b = document.createElement("div");
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
  function bindSubmitGuard() {
    try {
      const $ = window.jQuery;
      if (!$) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("jQuery not found for leads/productsUpdate");
        } catch (_) {}
        return;
      }
      const form = document.getElementById("lead-products-update-form");
      const btn = document.getElementById("lead-products-update-submit");
      if (!form || !btn) return;
      if (form.getAttribute(L) === "true") return;
      form.setAttribute(L, "true");
      $(btn)
        .off("click.leadsProductsUpdate")
        .on("click.leadsProductsUpdate", function (e) {
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
      const obs = new MutationObserver(function () {
        if (!document.body.contains(form) || !document.body.contains(btn)) {
          try {
            $(btn).off("click.leadsProductsUpdate");
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
          console.error("Failed to initialize leads/productsUpdate");
      } catch (_) {}
      return;
    }
    $(function () {
      bindSubmitGuard();
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to run leads/productsUpdate");
    } catch (__) {}
  }
})();
