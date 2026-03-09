/**
 * Payslips Index Route Guards
 * Handles payslip generation, export forms, and breadcrumb link
 * @module routes/payslips/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  const gForm = document.getElementById("payslip-generate-form");
  const gBtn = document.getElementById("payslip-generate-btn");
  if (gBtn && gForm) {
    gBtn.addEventListener("click", e => {
      e.preventDefault();
      gForm.requestSubmit();
    });
  }
  guard.bindSubmitGuard("#payslip-generate-form", {
    fallbackMsg:
      "Generate Payslip route is unavailable. Please contact technical support or your domain administrator.",
  });

  const eForm = document.getElementById("payslip-export-form");
  const monthSel = document.querySelector(".month_date");
  const yearSel = document.querySelector(".year_date");
  if (eForm) {
    const fm = eForm.querySelector("input.filter_month");
    const fy = eForm.querySelector("input.filter_year");
    const syncHidden = () => {
      if (fm && monthSel) fm.value = monthSel.value || "";
      if (fy && yearSel) fy.value = yearSel.value || "";
    };
    syncHidden();
    monthSel && monthSel.addEventListener("change", syncHidden);
    yearSel && yearSel.addEventListener("change", syncHidden);
    eForm.addEventListener("submit", syncHidden, { passive: true });
  }
  guard.bindSubmitGuard("#payslip-export-form", {
    fallbackMsg:
      "Export Payslip route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard("#bc-payslip-index-link", {
    fallbackMsg:
      "Payslip index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
