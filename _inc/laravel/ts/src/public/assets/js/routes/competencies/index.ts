/**
 * @fileoverview TypeScript version of public/assets/js/routes/competencies/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

/* global bootstrap */
((): void => {
  const bindGuard = (
    el: HTMLElement | null,
    event: string,
    urlAttr = "data-url",
    msgAttr = "data-guard-msg",
  ): void=> {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    el.addEventListener(event, e => {
      try {
        const url = el.getAttribute(urlAttr) ?? "#";
        if (url !== "#") return;
        e.preventDefault();
        const msg = el.getAttribute(msgAttr) ?? "# ERROR";
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (bootstrapLink && window.bootstrap) {
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
          bootstrap.Toast.getOrCreateInstance(toastEl).show();
        } else {
          alert(msg);
        }
        el.setAttribute("data-failed-route", "true");
      } catch {}
    });
  };

  bindGuard(document.getElementById("createCompetencyBtn"), "click");
  document
    .querySelectorAll('[data-listener-alias="edit-competency"]')
    .forEach((el): void => {
      bindGuard(el as HTMLElement, "click");
    });
  document
    .querySelectorAll('[data-listener-alias="delete-competency"]')
    .forEach((el): void => {
      bindGuard(el as HTMLElement, "click");
    });
})();

export {};
