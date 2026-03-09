/**
 * @fileoverview TypeScript version of public/assets/js/routes/attendances/bulk/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const errFb = "# ERROR",
    dataClientLocalized = "data-client-localized",
    dataGuardMsg = "data-guard-msg";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const langSessionKey = "erp-np-lang";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getLocalizedMessage = (msgKey: string, el: HTMLElement) => {
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
        window.translations?.en?.[msgKey] ??
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const showError = (message: string): void => {
    try {
      let container = document.querySelector<HTMLElement>(
        "#bootstrap-toast-container",
      );
      if (!container) {
        const hasBs =
          Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(
            l => /bootstrap/i.test((l as HTMLLinkElement).href),
          ) && window.bootstrap.Toast;
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
          for (const [k, v] of Object.entries({
            role: "alert",
            "aria-live": "assertive",
            "aria-atomic": "true",
          }))
            toast.setAttribute(k, v);
          const body = document.createElement("div");
          body.className = "toast-body";
          toast.appendChild(body);
          container.appendChild(toast);
          if (toast.getAttribute("data-click-listener") !== "true") {
            toast.addEventListener("click", () => (body.textContent = message));
            toast.setAttribute("data-click-listener", "true");
          }
        }
        const toastBody = toast.querySelector(".toast-body");
        if (toastBody) toastBody.textContent = message;
        new bootstrap.Toast(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };
  const presentAllEl = document.getElementById(
    "present_all",
  ) as HTMLInputElement | null;
  if (presentAllEl && presentAllEl.dataset.listenerAttached !== "true") {
    presentAllEl.dataset.listenerAttached = "true";
    const obsAll = new MutationObserver((ms, obs) => {
      ms.forEach(m => {
        [...m.removedNodes].forEach(n => {
          if (n === presentAllEl) {
            presentAllEl.removeEventListener("click", onPresentAllClick);
            obs.disconnect();
          }
        });
      });
    });
    obsAll.observe(document.body, { childList: true, subtree: true });
    presentAllEl.addEventListener("click", onPresentAllClick);
  }
  function onPresentAllClick(): void {
    try {
      if (!presentAllEl) return;
      const checked = presentAllEl.checked ?? false;
      document.querySelectorAll(".present").forEach((el: Element): void => {
        if (el instanceof HTMLInputElement) el.checked = checked;
      });
      document
        .querySelectorAll(".present_check_in")
        .forEach((el: Element): void => {
          el.classList.toggle("d-none", !checked);
          el.classList.toggle("d-block", checked);
        });
    } catch {
      showError(
        getLocalizedMessage(
          "present_all_toggle_failed",
          presentAllEl ?? document.body,
        ),
      );
    }
  }
  document.querySelectorAll(".present").forEach((el: Element): void => {
    const htmlEl = el as HTMLElement;
    if (htmlEl.dataset.listenerAttached === "true") return;
    htmlEl.dataset.listenerAttached = "true";
    const obsPres = new MutationObserver((ms, obs) => {
      ms.forEach(m => {
        [...m.removedNodes].forEach(n => {
          if (n === el) {
            el.removeEventListener("click", onPresentClick);
            obs.disconnect();
          }
        });
      });
    });
    obsPres.observe(document.body, { childList: true, subtree: true });
    el.addEventListener("click", onPresentClick);
  });
  function onPresentClick(event: Event): void {
    try {
      const el = event.currentTarget as HTMLInputElement,
        container =
          el.parentElement?.parentElement?.parentElement?.parentElement,
        checkInEl = container?.querySelector(".present_check_in");
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
        getLocalizedMessage(
          "present_toggle_failed",
          event.currentTarget as HTMLElement,
        ),
      );
    }
  }
})();

export {};
