(() => {
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

  const copyToClipboard = el => {
    if (!el?.id) return;
    try {
      navigator.clipboard.writeText(el.id);
      const msg = getLocalizedMessage(el, "copy_success");
      show_toastr("success", msg, "success");
    } catch {
      handleErrorDisplay(el, "copy_unavailable");
    }
  };

  window.copyToClipboard = copyToClipboard;
})();
(() => {
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

  const copyToClipboard = el => {
    if (!el?.id) return;
    try {
      navigator.clipboard.writeText(el.id);
      const msg = getLocalizedMessage(el, "copy_success");
      show_toastr("success", msg, "success");
    } catch {
      handleErrorDisplay(el, "copy_unavailable");
    }
  };

  window.copyToClipboard = copyToClipboard;
})();
