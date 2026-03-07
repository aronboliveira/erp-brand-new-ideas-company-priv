/**
 * @fileoverview TypeScript version of public/assets/js/routes/budgets/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const clientFlag = "data-client-localized";
  const guardMsgKey = "data-guard-msg";
  const langKey = "erp-np-lang";

  function getLocalizedMessage(key: string, el: HTMLElement): string {
    let msg = errFb;
    if (el.getAttribute(clientFlag) === "true") {
      msg = el.getAttribute(guardMsgKey) ?? msg;
    } else {
      let lang = (
        sessionStorage.getItem(langKey) ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ??
        el.getAttribute(guardMsgKey) ??
        window.translations?.en?.[key] ??
        msg;
      if (msg !== errFb) {
        el.setAttribute(guardMsgKey, msg);
        el.setAttribute(clientFlag, "true");
      }
    }
    return msg;
  }

  function showError(message: string) {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      const bs =
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
      if (bs) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = message;
        toast.appendChild(body);
        container.appendChild(toast);
        bootstrap.Toast.getOrCreateInstance(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  }

  let errorMessage = "";
  const onErrorPointerUp = (): void => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onErrorPointerUp);
  new MutationObserver((muts, obs) => {
    muts.forEach(m => {
      Array.from(m.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", (): void => {
    const bindField = (
      selector: string,
      handler: (e: Event) => void,
      isThrottle: boolean,
    ): void => {
      document
        .querySelectorAll<HTMLElement>(selector)
        .forEach((el: HTMLElement): void => {
          if (el.dataset.listenerAttached === "true") return;
          el.dataset.listenerAttached = "true";
          el.addEventListener(isThrottle ? "keyup" : "change", handler);
          new MutationObserver((ms, obs) => {
            ms.forEach(m => {
              Array.from(m.removedNodes).forEach(n => {
                if (n === el) {
                  el.removeEventListener(
                    isThrottle ? "keyup" : "change",
                    handler,
                  );
                  obs.disconnect();
                }
              });
            });
          }).observe(document.body, { childList: true, subtree: true });
        });
    };

    const onIncomeKeyup = (event: Event): void => {
      try {
        const target = event.currentTarget as HTMLInputElement;
        const row = target.closest("tr");
        if (!row) return;
        const inputs = row.querySelectorAll<HTMLInputElement>(".income_data");
        let total = 0;
        inputs.forEach(
          (i: HTMLInputElement) => (total += parseFloat(i.value) || 0),
        );
        const totalIncomeEl = row.querySelector(".totalIncome");
        if (totalIncomeEl) totalIncomeEl.textContent = String(total);
        const month = target.dataset.month;
        const monthInputs =
          row.parentElement?.querySelectorAll<HTMLInputElement>(
            `.${month}_income`,
          );
        let mTotal = 0;
        monthInputs?.forEach(
          (i: HTMLInputElement) => (mTotal += parseFloat(i.value) || 0),
        );
        const monthTotalEl = row.parentElement?.querySelector(
          `.${month}_total_income`,
        );
        if (monthTotalEl) monthTotalEl.textContent = String(mTotal);
        const allTotals = row.parentElement?.querySelectorAll(".totalIncome");
        let grand = 0;
        allTotals?.forEach(
          (t: Element) => (grand += parseFloat(t.textContent ?? "0") || 0),
        );
        const incomeEl = row.parentElement?.querySelector(".income");
        if (incomeEl) incomeEl.textContent = String(grand);
      } catch {
        errorMessage = getLocalizedMessage(
          "income_calculation_failed",
          event.currentTarget as HTMLElement,
        );
      }
    };

    const onExpenseKeyup = (event: Event): void => {
      try {
        const target = event.currentTarget as HTMLInputElement;
        const row = target.closest("tr");
        if (!row) return;
        const inputs = row.querySelectorAll<HTMLInputElement>(".expense_data");
        let total = 0;
        inputs.forEach(
          (i: HTMLInputElement) => (total += parseFloat(i.value) || 0),
        );
        const totalExpenseEl = row.querySelector(".totalExpense");
        if (totalExpenseEl) totalExpenseEl.textContent = String(total);
        const month = target.dataset.month;
        const monthInputs =
          row.parentElement?.querySelectorAll<HTMLInputElement>(
            `.${month}_expense`,
          );
        let mTotal = 0;
        monthInputs?.forEach(
          (i: HTMLInputElement) => (mTotal += parseFloat(i.value) || 0),
        );
        const monthTotalEl = row.parentElement?.querySelector(
          `.${month}_total_expense`,
        );
        if (monthTotalEl) monthTotalEl.textContent = String(mTotal);
        const allTotals = row.parentElement?.querySelectorAll(".totalExpense");
        let grand = 0;
        allTotals?.forEach(
          (t: Element) => (grand += parseFloat(t.textContent ?? "0") || 0),
        );
        const expenseEl = row.parentElement?.querySelector(".expense");
        if (expenseEl) expenseEl.textContent = String(grand);
      } catch {
        errorMessage = getLocalizedMessage(
          "expense_calculation_failed",
          event.currentTarget as HTMLElement,
        );
      }
    };

    const onPeriodChange = (event: Event): void => {
      try {
        const target = event.currentTarget as HTMLSelectElement;
        const val = target.value;
        document
          .querySelectorAll(".budget_plan")
          .forEach((el: Element): void => {
            el.classList.add("d-none");
          });
        const targetEl = document.getElementById(val);
        if (targetEl) targetEl.classList.replace("d-none", "d-block");
      } catch {
        showError(
          getLocalizedMessage(
            "period_toggle_failed",
            event.currentTarget as HTMLElement,
          ),
        );
      }
    };

    bindField(".income_data", onIncomeKeyup, true);
    bindField(".expense_data", onExpenseKeyup, true);
    bindField(".period", onPeriodChange, false);
    document.querySelectorAll(".period").forEach((el: Element): void => {
      el.dispatchEvent(new Event("change"));
    });
  });
})();

export {};
