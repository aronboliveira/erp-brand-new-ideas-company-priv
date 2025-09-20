(function () {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataGuardListener = "data-guard-listener";
  const msgKey = "pos_unavailable";
  const getMsg = el => {
    let msg = errFb;
    try {
      if (!el) return msg;
      if (
        el.getAttribute("data-sv-localized") === "true" ||
        el.getAttribute(dataClientLocalized) === "true"
      ) {
        msg = el.getAttribute(dataGuardMsg) || errFb;
      } else {
        let lang = (
          window.sessionStorage.getItem("erp-np-lang") ||
          document.documentElement.lang ||
          "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          window.translations?.[lang]?.[msgKey] ||
          el.getAttribute(dataGuardMsg) ||
          window.translations?.en?.[msgKey] ||
          errFb;
        if (msg !== errFb) {
          el.setAttribute(dataGuardMsg, msg);
          el.setAttribute(dataClientLocalized, "true");
        }
      }
      return msg;
    } catch {
      return errFb;
    }
  };
  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
  const showError = el => {
    try {
      const message = getMsg(el);
      if (hasBootstrapCss() && window.bootstrap?.Toast) {
        let wrap = document.getElementById("toast-container");
        if (!wrap) {
          wrap = document.createElement("div");
          wrap.id = "toast-container";
          document.body.appendChild(wrap);
        }
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = message;
        t.appendChild(b);
        wrap.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t, {
          autohide: true,
          delay: 4000,
        }).show();
      } else {
        alert(message);
      }
    } catch {
      alert(errFb);
    }
  };
  try {
    const jq = window.jQuery || (window.$?.fn ? window.$ : null);
    if (!jq) {
      console.error("jQuery not found for POS guard");
      return;
    }
    jq(() => {
      const bind = el => {
        if (!el || el.getAttribute(dataGuardListener) === "true") return;
        el.setAttribute(dataGuardListener, "true");
        const $el = jq(el);
        const handler = e => {
          try {
            const url = el.getAttribute("data-url");
            const href =
              el.getAttribute("href") ||
              (el.form ? el.form.getAttribute("action") : null);
            if ((!url || url === "#") && (!href || href === "#")) {
              e.preventDefault();
              showError(el);
            }
          } catch {
            e.preventDefault();
            showError(el);
          }
        };
        $el.on("click.posGuard", handler);
        const obs = new MutationObserver(() => {
          if (!document.body.contains(el)) {
            try {
              $el.off("click.posGuard", handler);
            } catch {}
            obs.disconnect();
          }
        });
        obs.observe(document.body, { childList: true, subtree: true });
      };
      try {
        const nodes = document.querySelectorAll(".payment-done-btn");
        nodes.forEach(bind);
      } catch {
        const nodes = jq(".payment-done-btn").toArray();
        nodes.forEach(bind);
      }
    });
  } catch {
    try {
      alert(errFb);
    } catch {}
  }
})();
