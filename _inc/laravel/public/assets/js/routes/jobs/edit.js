(() => {
  const DATA_LISTENER_ADDED = "data-listener-added";
  const ERR_FB = "# ERROR";
  const DATA_CLIENT_LOCALIZED = "data-client-localized";
  const DATA_GUARD_MSG = "data-guard-msg";

  const getLocalizedMessage = (el, key) => {
    let msg = ERR_FB;
    if (
      el?.getAttribute("data-sv-localized") === "true" ||
      el?.getAttribute(DATA_CLIENT_LOCALIZED) === "true"
    ) {
      msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
    } else {
      let lang = (
        sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(DATA_GUARD_MSG) ||
        window.translations?.["en"]?.[key] ||
        ERR_FB;
      if (msg !== ERR_FB) {
        el.setAttribute(DATA_GUARD_MSG, msg);
        el.setAttribute(DATA_CLIENT_LOCALIZED, "true");
      }
    }
    return msg;
  };

  const handleErrorDisplay = (el, key) => {
    const message = el ? getLocalizedMessage(el, key) : ERR_FB;
    const hasBootstrap =
      document.querySelector('link[href*="bootstrap"]') &&
      window.bootstrap?.Toast;
    if (hasBootstrap) {
      if (!document.querySelector("#error-toast")) {
        const toast = document.createElement("div");
        toast.id = "error-toast";
        toast.className = "toast align-items-center text-bg-danger border-0";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        toast.innerHTML = `
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button"
                                        class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"
                                        aria-label="Close"></button>
                            </div>`;
        document.body.appendChild(toast);
      }
      new bootstrap.Toast(document.querySelector("#error-toast")).show();
    } else {
      alert(message);
    }
  };

  try {
    if (typeof $ === "undefined" || !$.fn.tagsinput) throw new Error();
    const $els = $('[data-toggle="tags"]');
    if (!$els.length) return;
    $els.each(function () {
      try {
        $(this).tagsinput({ tagClass: "badge badge-primary" });
      } catch {
        const el = this;
        if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
          el.addEventListener("click", () =>
            handleErrorDisplay(el, "tags_unavailable")
          );
          el.setAttribute(DATA_LISTENER_ADDED, "true");
          const obs = new MutationObserver((_, o) => {
            if (!document.body.contains(el)) {
              el.removeEventListener("click", () =>
                handleErrorDisplay(el, "tags_unavailable")
              );
              o.disconnect();
            }
          });
          obs.observe(document.body, { childList: true, subtree: true });
        }
      }
    });
  } catch {
    const el = document.body;
    if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
      el.addEventListener("click", () =>
        handleErrorDisplay(el, "tags_unavailable")
      );
      el.setAttribute(DATA_LISTENER_ADDED, "true");
      const obs = new MutationObserver((_, o) => {
        if (!document.body.contains(el)) {
          el.removeEventListener("click", () =>
            handleErrorDisplay(el, "tags_unavailable")
          );
          o.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    } else {
      handleErrorDisplay(el, "tags_unavailable");
    }
  }
})();
