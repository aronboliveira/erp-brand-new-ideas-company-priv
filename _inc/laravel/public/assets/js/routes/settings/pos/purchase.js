(() => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const DATA_LISTENER_ADDED = "data-listener-added";
  const getMsg = (el, msgKey) => {
    let msg = errFb;
    if (
      el?.getAttribute("data-sv-localized") === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const key = msgKey;
      msg =
        window.translations?.[lang]?.[key] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (msg !== errFb) {
        el?.setAttribute(dataGuardMsg, msg);
        el?.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const showFeedback = (el, key, ev = "click") => {
    const text = getMsg(el || document.body, key);
    const hasBs =
      document.querySelector('link[href*="bootstrap"]') &&
      window.bootstrap?.Toast;
    if (hasBs) {
      let toast = document.querySelector("#np-error-toast");
      if (!toast) {
        toast = document.createElement("div");
        toast.id = "np-error-toast";
        toast.className = "toast align-items-center text-bg-danger border-0";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        toast.innerHTML = `<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
        document.body.appendChild(toast);
      }
      const handler = () => new bootstrap.Toast(toast).show();
      if (!toast.getAttribute(DATA_LISTENER_ADDED)) {
        toast.setAttribute(DATA_LISTENER_ADDED, "true");
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(toast)) {
            document.removeEventListener(ev, handler);
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      }
      document.addEventListener(ev, handler, { once: true });
    } else {
      const handler = () => alert(text);
      document.addEventListener(ev, handler, { once: true });
    }
  };
  const guardOnce = (el, key, ev = "click") => {
    if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
    const handler = () => showFeedback(el, key, ev);
    el.addEventListener(ev, handler, { once: true });
    el.setAttribute(DATA_LISTENER_ADDED, "true");
    const mo = new MutationObserver((_, o) => {
      if (!document.body.contains(el)) {
        el.removeEventListener(ev, handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const routeGuard = (element, alt) => {
    const url = element?.getAttribute?.("data-url");
    const href = element?.action ?? element?.href;
    return (
      (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#")
    );
  };
  try {
    if (typeof $ === "undefined") {
      console.error("jQuery failed to load");
      return;
    }
    try {
      if (window.bootstrap?.ScrollSpy) {
        new bootstrap.ScrollSpy(document.body, {
          target: "#useradd-sidenav",
          offset: 300,
        });
      } else {
        guardOnce(document.body, "scrollspy_unavailable", "click");
      }
    } catch {
      guardOnce(document.body, "scrollspy_unavailable", "click");
    }
    $(document).on(
      "change",
      "select[name='purchase_template'], input[name='purchase_color']",
      function () {
        try {
          const template = $("select[name='purchase_template']").val() ?? "";
          const color = $("input[name='purchase_color']:checked").val() ?? "";
          const $frame = $("#purchase_frame");
          const preview = `{{url('/purchase/preview')}}/${template}/${color}`;
          if (!$frame.length || routeGuard($frame.get(0), preview)) {
            guardOnce(
              $frame.get(0) || document.body,
              "purchase_preview_unavailable",
              "click"
            );
            return;
          }
          $frame.attr("src", preview);
        } catch {
          guardOnce(document.body, "purchase_preview_unavailable", "click");
        }
      }
    );
    (() => {
      const input = document.getElementById("purchase_logo");
      const img = document.getElementById("purchase_image");
      if (!input || !img) {
        guardOnce(document.body, "purchase_logo_unavailable", "click");
        return;
      }
      if (!input.getAttribute(DATA_LISTENER_ADDED)) {
        input.addEventListener(
          "change",
          () => {
            try {
              const f = input.files?.[0];
              if (!f) {
                return;
              }
              const src = URL.createObjectURL(f);
              img.src = src;
            } catch {
              guardOnce(input, "purchase_logo_unavailable", "click");
            }
          },
          { once: false }
        );
        input.setAttribute(DATA_LISTENER_ADDED, "true");
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(input)) {
            input.removeEventListener("change", () => {});
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      }
    })();
    $(document).on(
      "change",
      "select[name='pos_template'], input[name='pos_color']",
      function () {
        try {
          const template = $("select[name='pos_template']").val() ?? "";
          const color = $("input[name='pos_color']:checked").val() ?? "";
          const $frame = $("#pos_frame");
          const preview = `{{url('/pos/preview')}}/${template}/${color}`;
          if (!$frame.length || routeGuard($frame.get(0), preview)) {
            guardOnce(
              $frame.get(0) || document.body,
              "pos_preview_unavailable",
              "click"
            );
            return;
          }
          $frame.attr("src", preview);
        } catch {
          guardOnce(document.body, "pos_preview_unavailable", "click");
        }
      }
    );
    (() => {
      const input = document.getElementById("pos_logo");
      const img = document.getElementById("pos_image");
      if (!input || !img) {
        guardOnce(document.body, "pos_logo_unavailable", "click");
        return;
      }
      if (!input.getAttribute(DATA_LISTENER_ADDED)) {
        input.addEventListener(
          "change",
          () => {
            try {
              const f = input.files?.[0];
              if (!f) {
                return;
              }
              const src = URL.createObjectURL(f);
              img.src = src;
            } catch {
              guardOnce(input, "pos_logo_unavailable", "click");
            }
          },
          { once: false }
        );
        input.setAttribute(DATA_LISTENER_ADDED, "true");
        const mo = new MutationObserver((_, o) => {
          if (!document.body.contains(input)) {
            input.removeEventListener("change", () => {});
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      }
    })();
  } catch (e) {
    console.error("Initialization failed", e);
  }
})();
