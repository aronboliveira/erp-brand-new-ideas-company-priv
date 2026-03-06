/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  const showMsg = msg => {
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

  const guardSubmit = (form, fallbackMsg) => {
    if (!form) return;
    form.addEventListener(
      "submit",
      e => {
        const url =
          form.getAttribute("action") || form.getAttribute("data-url") ?? "#";
        if (!url || url === "#") {
          e.preventDefault();
          const msg = form.getAttribute("data-guard-msg") || fallbackMsg;
          showMsg(msg);
        }
      },
      { passive: false }
    );
  };

  const gForm = document.getElementById("payslip-generate-form");
  const gBtn = document.getElementById("payslip-generate-btn");
  if (gBtn && gForm) {
    gBtn.addEventListener("click", e => {
      e.preventDefault();
      gForm.requestSubmit();
    });
  }
  guardSubmit(
    gForm,
    "Generate Payslip route is unavailable. Please contact technical support or your domain administrator."
  );

  const eForm = document.getElementById("payslip-export-form");
  const monthSel = document.querySelector<HTMLElement>(".month_date");
  const yearSel = document.querySelector<HTMLElement>(".year_date");
  if (eForm) {
    const fm = eForm.querySelector("input.filter_month");
    const fy = eForm.querySelector("input.filter_year");
    const syncHidden = (): void => {
      if (fm && monthSel) fm.value = monthSel.value ?? "";
      if (fy && yearSel) fy.value = yearSel.value ?? "";
    };
    syncHidden();
    monthSel?.addEventListener("change", syncHidden);
    yearSel?.addEventListener("change", syncHidden);
    eForm.addEventListener("submit", syncHidden, { passive: true });
  }
  guardSubmit(
    eForm,
    "Export Payslip route is unavailable. Please contact technical support or your domain administrator."
  );

  const bc = document.getElementById("bc-payslip-index-link");
  if (bc) {
    bc.addEventListener("click", e => {
      const href =
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        bc.getAttribute("href") ?? bc.getAttribute("data-url") ?? "#";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!href || href === "#") {
        e.preventDefault();
        const msg =
          bc.getAttribute("data-guard-msg") ?? "Payslip index route is unavailable. Please contact technical support or your domain administrator.";
        showMsg(msg);
      }
    });
  }
})();

export {};
