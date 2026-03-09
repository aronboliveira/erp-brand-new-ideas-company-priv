/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  const showMsg = (msg: string): void => {
    try {
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          el.setAttribute(k, v);
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

  const guardSubmit = (
    form: HTMLFormElement | null,
    fallbackMsg: string,
  ): void => {
    if (!form) return;
    form.addEventListener(
      "submit",
      (e: Event) => {
        const url =
          form.getAttribute("action") || form.getAttribute("data-url") || "#";
        if (!url || url === "#") {
          e.preventDefault();
          const msg = form.getAttribute("data-guard-msg") || fallbackMsg;
          showMsg(msg);
        }
      },
      { passive: false },
    );
  };

  const gForm = document.getElementById(
      "payslip-generate-form",
    ) as HTMLFormElement | null,
    gBtn = document.getElementById("payslip-generate-btn");
  if (gBtn && gForm)
    if (!gBtn.getAttribute("data-listener-bound-click")) {
      gBtn.setAttribute("data-listener-bound-click", "1");
      gBtn.addEventListener("click", (e: Event) => {
        e.preventDefault();
        gForm.requestSubmit();
      });
    }
  guardSubmit(
    gForm,
    "Generate Payslip route is unavailable. Please contact technical support or your domain administrator.",
  );

  const eForm = document.getElementById(
      "payslip-export-form",
    ) as HTMLFormElement | null,
    monthSel = document.querySelector<HTMLSelectElement>(".month_date"),
    yearSel = document.querySelector<HTMLSelectElement>(".year_date");
  if (eForm) {
    const fm = eForm.querySelector<HTMLInputElement>("input.filter_month"),
      fy = eForm.querySelector<HTMLInputElement>("input.filter_year");
    const syncHidden = (): void => {
      if (fm && monthSel) fm.value = monthSel.value ?? "";
      if (fy && yearSel) fy.value = yearSel.value ?? "";
    };
    syncHidden();
    monthSel?.addEventListener("change", syncHidden);
    yearSel?.addEventListener("change", syncHidden);
    if (!eForm.getAttribute("data-listener-bound-submit")) {
      eForm.setAttribute("data-listener-bound-submit", "1");
      eForm.addEventListener("submit", syncHidden, { passive: true });
    }
  }
  guardSubmit(
    eForm,
    "Export Payslip route is unavailable. Please contact technical support or your domain administrator.",
  );

  const bc = document.getElementById("bc-payslip-index-link");
  if (bc) {
    if (!bc.getAttribute("data-listener-bound-click")) {
      bc.setAttribute("data-listener-bound-click", "1");
      bc.addEventListener("click", (e: Event) => {
        const href =
          bc.getAttribute("href") ?? bc.getAttribute("data-url") ?? "#";
        if (!href || href === "#") {
          e.preventDefault();
          const msg =
            bc.getAttribute("data-guard-msg") ??
            "Payslip index route is unavailable. Please contact technical support or your domain administrator.";
          showMsg(msg);
        }
      });
    }
  }
})();

export {};
