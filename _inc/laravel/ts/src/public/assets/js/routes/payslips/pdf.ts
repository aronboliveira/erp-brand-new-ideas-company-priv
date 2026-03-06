/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const toast = msg => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): void => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        el.appendChild(body);
        c.appendChild(el);
        window.bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  const mail = document.getElementById("payslip-mail-send");
  if (mail) {
    mail.addEventListener("click", e => {
      const url =
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        mail.getAttribute("href") ?? mail.getAttribute("data-url") ?? "#";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          mail.getAttribute("data-guard-msg") ?? "Send Payslip route is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
      }
    });
  }

  const printableId = "printableArea";
  const printBtn = document.getElementById("payslip-download");
  const printFn = (): void => {
    const el = document.getElementById(printableId);
    if (!el) return;
    const w = window.open("about:blank", "_blank", "noopener,noreferrer");
    if (!w) return;
    const bootstrapHref =
      document.querySelector('link[href*="bootstrap"]')?.href ?? "";
    const newDoc = w.document;
    newDoc.head.innerHTML = [
      "<title>Payslip</title>",
      '<meta charset="utf-8" />',
      '<meta name="viewport" content="width=device-width, initial-scale=1" />',
      `<link rel="stylesheet" href="${bootstrapHref}">`,
      "<style>body{padding:16px}</style>",
    ].join("");
    newDoc.body.innerHTML = el.innerHTML;
    w.focus();
    setTimeout((): void => { w.print(); }, 300);
  };
  if (printBtn) {
    printBtn.addEventListener("click", e => {
      e.preventDefault();
      printFn();
    });
  }
  if (!window.saveAsPDF) window.saveAsPDF = printFn;
})();

export {};
