(() => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const defaultLangSessionKey = "erp-np-lang";
  const getLocalizedMessage = (msgKey, el) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) ?? errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem(defaultLangSessionKey) ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ??
        el.getAttribute(dataGuardMsg) ??
        window.translations?.["en"]?.[msgKey] ??
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const el = document.querySelector("#disable_lang");
  if (!el || el.getAttribute("data-listener-attached") === "true") return;
  const observer = new MutationObserver((mutations, obs) => {
    for (const m of mutations) {
      for (const node of m.removedNodes) {
        if (node === el) {
          el.removeEventListener("pointerup", handler);
          obs.disconnect();
        }
      }
    }
  });
  observer.observe(document.body, { childList: true, subtree: true });
  el.setAttribute("data-listener-attached", "true");
  el.addEventListener("pointerup", handler);
  function handler() {
    try {
      const isChecked = el.checked ?? false;
      const mode = isChecked ? "on" : "off";
      const url = el.getAttribute("data-url");
      const href = el.form?.action ?? el.getAttribute("href");
      if ((!url || url === "#") && (!href || href === "#")) {
        showError(getLocalizedMessage("disable_lang_unavailable", el));
        return;
      }
      const requestUrl = url || href;
      const token =
        window.csrfToken ??
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") ??
        "";
      if (!token) console.log("CSRF token missing");
      $.ajax({
        type: "POST",
        url: requestUrl,
        dataType: "json",
        data: { _token: token, mode, lang: el.getAttribute("data-lang") ?? "" },
      })
        .done(data => show_toastr("success", data.message, "success"))
        .fail(() => showError(getLocalizedMessage("disable_lang_failed", el)));
    } catch {
      showError(getLocalizedMessage("disable_lang_failed", el));
    }
  }
  function showError(message) {
    try {
      let container = document.querySelector("#bootstrap-toast-container");
      if (!container) {
        const hasBootstrap =
          Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(
            l => /bootstrap/i.test(l.href)
          ) && window.bootstrap?.Toast;
        if (hasBootstrap) {
          container = document.createElement("div");
          container.id = "bootstrap-toast-container";
          container.setAttribute("aria-live", "polite");
          container.setAttribute("aria-atomic", "true");
          document.body.appendChild(container);
        }
      }
      if (container && window.bootstrap.Toast) {
        let toast = container.querySelector(".toast");
        if (!toast) {
          toast = document.createElement("div");
          toast.className = "toast";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          const body = document.createElement("div");
          body.className = "toast-body";
          toast.appendChild(body);
          container.appendChild(toast);
          if (toast.getAttribute("data-click-listener") !== "true") {
            toast.addEventListener("click", () => (body.textContent = message));
            toast.setAttribute("data-click-listener", "true");
          }
        }
        toast.querySelector(".toast-body").textContent = message;
        new bootstrap.Toast(toast).show();
      } else {
        alert(message);
      }
      el.setAttribute("data-failed-route", "true");
    } catch {
      alert(message);
    }
  }
})();
