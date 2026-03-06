/**
 * @fileoverview TypeScript version of public/assets/js/routes/interviewSchedules/storeSelect.js
 * @generated from original JavaScript - manual review recommended
 * @module storeSelect
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const dataListenerAdded = "data-listener-added";
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";

  const getLocalizedMessage = (el, msgKey) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
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
  };

  try {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!window.jQuery) {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery is not available");
      return;
    }
    const candidate = jQuery("select#candidate");
    const el = candidate.get(0);
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!el) {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Select#candidate element not found");
      return;
    }
    const url = el.getAttribute("data-url");
    const href = el.href
      .replace(window.location.origin, "")
      .replace(window.location.pathname, "");
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if ((!url || url === "#") && (!href || href === "#")) {
      return;
    }
    const candidateVal = candidate;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    if (candidateVal == null) {
      return;
    }
    candidate.val(candidateVal).trigger("change");
  } catch {
    const handleErrorDisplay = (): void => {
      const el = document.querySelector<HTMLSelectElement>("select#candidate");
      const msgKey = "candidate_unavailable";
      const message = el ? getLocalizedMessage(el, msgKey) : errFb;
      const hasBootstrap =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (hasBootstrap) {
        if (!document.querySelector<HTMLElement>("#error-toast")) {
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
        new bootstrap.Toast(document.querySelector<HTMLElement>("#error-toast")).show();
      } else {
        alert(message);
      }
    };
    const el = document.querySelector<HTMLSelectElement>("select#candidate");
    if (el?.getAttribute(dataListenerAdded) !== "true") {
      const observer = new MutationObserver((_, obs) => {
        if (!document.body.contains(el)) {
          el.removeEventListener("click", handleErrorDisplay);
          obs.disconnect();
        }
      });
      observer.observe(document.body, { childList: true, subtree: true });
      el.addEventListener("click", handleErrorDisplay);
      el.setAttribute(dataListenerAdded, "true");
    }
  }
})();

export {};
