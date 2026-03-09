(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  if (!guard || !utils) return;

  const getLocalizedMessage = (msgKey, el) => {
    return utils.getTranslation(msgKey) || "# ERROR";
  };

  const showError = message => {
    guard.showToast(message);
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
        }),
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
        }),
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
        getLocalizedMessage("present_toggle_failed", event.currentTarget),
      );
    }
  }
})();
