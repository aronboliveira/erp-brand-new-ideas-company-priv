(() => {
  const showMsg = msg => {
    try {
      if (window.bootstrap?.Toast) {
        const c =
          document.getElementById("toast-container") ||
          (() => {
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
          form.getAttribute("action") || form.getAttribute("data-url") || "#";
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
  guardSubmit(
    eForm,
    "Export Payslip route is unavailable. Please contact technical support or your domain administrator."
  );

  const bc = document.getElementById("bc-payslip-index-link");
  if (bc) {
    bc.addEventListener("click", e => {
      const href =
        bc.getAttribute("href") || bc.getAttribute("data-url") || "#";
      if (!href || href === "#") {
        e.preventDefault();
        const msg =
          bc.getAttribute("data-guard-msg") ||
          "Payslip index route is unavailable. Please contact technical support or your domain administrator.";
        showMsg(msg);
      }
    });
  }
})();
