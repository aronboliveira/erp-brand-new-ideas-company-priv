/**
 * @fileoverview TypeScript version of public/assets/js/routes/goals/trackings/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const dataClientLoc = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";

  function getLocalizedMessage(el, key) {
    let msg = errFb;
    if (el.getAttribute(dataClientLoc) === "true") {
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
        window.translations?.[lang]?.[key] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLoc, "true");
      }
    }
    return msg;
  }

  function showError(msg) {
    const bsLink = document.querySelector("link[href*='bootstrap']");
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    if (bsLink && window.bootstrap.Toast) {
      const container =
        document.getElementById("toast-container") ??
        ((): void => {
          const c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
          return c;
        })();
      const toastEl = document.createElement("div");
      toastEl.className = "toast";
      toastEl.setAttribute("role", "alert");
      toastEl.setAttribute("aria-live", "assertive");
      toastEl.setAttribute("aria-atomic", "true");
      const body = document.createElement("div");
      body.className = "toast-body";
      body.textContent = msg;
      toastEl.appendChild(body);
      container.appendChild(toastEl);
      window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
    } else {
      alert(msg);
    }
  }

  document.addEventListener("DOMContentLoaded", (): void => {
    document.querySelectorAll(".toggleswitch").forEach((el: Element): void => {
      try {
        if (typeof $(el).bootstrapToggle !== "function") {
          throw new Error("bootstrapToggle missing");
        }
        $(el).bootstrapToggle();
      } catch {
        const msg = getLocalizedMessage(el, "toggle_init_failed");
        el.addEventListener("click", (): void => { showError(msg); }, { once: true });
      }
    });

    const starSelector = "fieldset[id^='demo'] .stars";
    const handleStarClick = e => {
      const tgt = e.target;
      if (!tgt.matches(starSelector)) return;
      try {
        alert(tgt.value);
        tgt.checked = true;
      } catch {
        const msg = getLocalizedMessage(tgt, "star_click_failed");
        tgt.addEventListener("pointerup", (): void => { showError(msg); }, { once: true });
      }
    };

    if (!document.body.hasAttribute("data-star-listener")) {
      document.body.addEventListener("click", handleStarClick);
      document.body.setAttribute("data-star-listener", "true");
      const mo = new MutationObserver((): void => {
        if (!document.querySelector(starSelector)) {
          mo.disconnect();
          document.body.removeEventListener("click", handleStarClick);
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    }
  });
})();

export {};
