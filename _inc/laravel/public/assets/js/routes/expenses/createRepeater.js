(() => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const langSessionKey = "erp-np-lang";
  let errorMessage = "";

  const getLocalizedMessage = (msgKey, el) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem(langSessionKey) ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ||
        window.translations?.["en"]?.[msgKey] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const showError = message => {
    try {
      const bsLink = document.querySelector('link[href*="bootstrap"]');
      let container = document.getElementById("toast-container");
      if (bsLink && window.bootstrap) {
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }
        const toastEl = document.createElement("div");
        toastEl.className = "toast";
        toastEl.setAttribute("role", "alert");
        toastEl.setAttribute("aria-live", "assertive");
        toastEl.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = message;
        toastEl.appendChild(body);
        container.appendChild(toastEl);
        window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };

  const onPointerUp = () => {
    if (errorMessage) {
      showError(errorMessage);
      errorMessage = "";
    }
  };

  document.addEventListener("pointerup", onPointerUp);

  document.querySelectorAll("[data-repeater-delete]").forEach(el => {
    if (el.getAttribute("data-guard-listener-active") === "true") return;
    el.setAttribute("data-guard-listener-active", "true");
    el.addEventListener("click", () => {
      try {
        $(".price").change();
        $(".discount").change();
      } catch {
        errorMessage = getLocalizedMessage("repeater_delete_failed", el);
      }
    });
  });

  new MutationObserver((muts, obs) => {
    muts.forEach(m =>
      m.removedNodes.forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onPointerUp);
          obs.disconnect();
        }
      })
    );
  }).observe(document.body, { childList: true, subtree: true });
})();
