/**
 * @fileoverview TypeScript version of public/assets/js/routes/chartOfAccounts/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const bindGuard = (
    el,
    event,
    urlAttr = "data-url",
    msgAttr = "data-guard-msg"
  ) => {
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
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
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
      } catch (e) {}
    });
  };
  const filterForm = document.getElementById("report_bill_summary");
  bindGuard(filterForm, "submit");
  const applyBtn = document.getElementById("applyFilter");
  bindGuard(applyBtn, "click");
  document
    .querySelectorAll('[data-listener-alias="ledger-link"]')
    .forEach((el: Element): void => { bindGuard(el, "click"); });
  document
    .querySelectorAll('[data-listener-alias="edit-account"]')
    .forEach((el: Element): void => { bindGuard(el, "click"); });
  document
    .querySelectorAll('[data-listener-alias="delete-account"]')
    .forEach((el: Element): void => { bindGuard(el, "click"); });
})();

export {};
