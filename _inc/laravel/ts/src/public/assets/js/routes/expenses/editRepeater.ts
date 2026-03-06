/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/editRepeater.js
 * @generated from original JavaScript - manual review recommended
 * @module editRepeater
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const langSessionKey = "erp-np-lang";
  const errFb = "# ERROR";

  function getMsg(key, el) {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem(langSessionKey) ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  }

  function showError(message) {
    try {
      const hasBs =
        Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(
          l => /bootstrap/i.test(l.href)
        ) && window.bootstrap.Toast;
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (hasBs) {
        let container = document.getElementById("bootstrap-toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "bootstrap-toast-container";
          container.setAttribute("aria-live", "polite");
          container.setAttribute("aria-atomic", "true");
          document.body.appendChild(container);
        }
        const toast =
          container.querySelector(".toast") ??
          ((): void => {
            const t = document.createElement("div");
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            const body = document.createElement("div");
            body.className = "toast-body";
            t.appendChild(body);
            container.appendChild(t);
            return t;
          })();
        toast.querySelector(".toast-body").textContent = message;
        bootstrap.Toast.getOrCreateInstance(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  }

  const listenerAttr = "data-rep-del-listener";
  document.querySelectorAll("[data-repeater-delete]").forEach((el: Element): void => {
    if (el.getAttribute(listenerAttr) === "true") return;
    el.setAttribute(listenerAttr, "true");
    el.addEventListener("click", (): void => {
      try {
        $(".price").change();
        $(".discount").change();
      } catch {
        showError(getMsg("repeater_delete_failed", el));
      }
    });
    const obs = new MutationObserver((mutations, o) => {
      if (!document.body.contains(el)) {
        el.removeEventListener("click", null);
        o.disconnect();
      }
    });
    obs.observe(document.body, { childList: true, subtree: true });
  });
})();

export {};
