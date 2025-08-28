(() => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const langSessionKey = "erp-np-lang";
  const getLocalizedMessage = (msgKey, el) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) ?? errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem(langSessionKey) ??
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
  const showError = message => {
    try {
      let container = document.querySelector("#bootstrap-toast-container");
      if (!container) {
        const hasBs =
          Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(
            l => /bootstrap/i.test(l.href)
          ) && window.bootstrap?.Toast;
        if (hasBs) {
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
    } catch {
      alert(message);
    }
  };
  const presentAllEl = document.getElementById("present_all");
  if (presentAllEl && presentAllEl.dataset.listenerAttached !== "true") {
    presentAllEl.dataset.listenerAttached = "true";
    const obsAll = new MutationObserver((ms, obs) => {
      ms.forEach(m =>
        [...m.removedNodes].forEach(n => {
          if (n === presentAllEl) {
            presentAllEl.removeEventListener("click", onPresentAllClick);
            obs.disconnect();
          }
        })
      );
    });
    obsAll.observe(document.body, { childList: true, subtree: true });
    presentAllEl.addEventListener("click", onPresentAllClick);
  }
  function onPresentAllClick() {
    try {
      const checked = presentAllEl.checked ?? false;
      document.querySelectorAll(".present").forEach(el => {
        if (el instanceof HTMLInputElement) el.checked = checked;
      });
      document.querySelectorAll(".present_check_in").forEach(el => {
        el.classList.toggle("d-none", !checked);
        el.classList.toggle("d-block", checked);
      });
    } catch {
      showError(getLocalizedMessage("present_all_toggle_failed", presentAllEl));
    }
  }
  document.querySelectorAll(".present").forEach(el => {
    if (el.dataset.listenerAttached === "true") return;
    el.dataset.listenerAttached = "true";
    const obsPres = new MutationObserver((ms, obs) => {
      ms.forEach(m =>
        [...m.removedNodes].forEach(n => {
          if (n === el) {
            el.removeEventListener("click", onPresentClick);
            obs.disconnect();
          }
        })
      );
    });
    obsPres.observe(document.body, { childList: true, subtree: true });
    el.addEventListener("click", onPresentClick);
  });
  function onPresentClick(event) {
    try {
      const el = event.currentTarget;
      const container =
        el.parentElement?.parentElement?.parentElement?.parentElement;
      const checkInEl = container?.querySelector(".present_check_in");
      if (!checkInEl) return;
      if (el.checked) {
        checkInEl.classList.remove("d-none");
        checkInEl.classList.add("d-block");
      } else {
        checkInEl.classList.remove("d-block");
        checkInEl.classList.add("d-none");
      }
    } catch {
      showError(
        getLocalizedMessage("present_toggle_failed", event.currentTarget)
      );
    }
  }
})();
