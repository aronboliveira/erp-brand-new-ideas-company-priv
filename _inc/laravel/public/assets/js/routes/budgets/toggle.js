(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("pointerup", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";

  document.addEventListener("DOMContentLoaded", () => {
    const bindField = (selector, handler, isThrottle) => {
      document.querySelectorAll(selector).forEach(el => {
        if (el.dataset.listenerAttached === "true") return;
        el.dataset.listenerAttached = "true";
        el.addEventListener(isThrottle ? "keyup" : "change", handler);
      });
    };

    const onIncomeKeyup = event => {
      try {
        const row = event.currentTarget.closest("tr");
        const inputs = row.querySelectorAll(".income_data");
        let total = 0;
        inputs.forEach(i => (total += parseFloat(i.value) || 0));
        row.querySelector(".totalIncome").textContent = total;
        const month = event.currentTarget.dataset.month;
        const monthInputs = row.parentElement.querySelectorAll(
          `.${month}_income`,
        );
        let mTotal = 0;
        monthInputs.forEach(i => (mTotal += parseFloat(i.value) || 0));
        row.parentElement.querySelector(`.${month}_total_income`).textContent =
          mTotal;
        const allTotals = row.parentElement.querySelectorAll(".totalIncome");
        let grand = 0;
        allTotals.forEach(t => (grand += parseFloat(t.textContent) || 0));
        row.parentElement.querySelector(".income").textContent = grand;
      } catch {
        scheduleError(getMsg("income_calculation_failed"));
      }
    };

    const onExpenseKeyup = event => {
      try {
        const row = event.currentTarget.closest("tr");
        const inputs = row.querySelectorAll(".expense_data");
        let total = 0;
        inputs.forEach(i => (total += parseFloat(i.value) || 0));
        row.querySelector(".totalExpense").textContent = total;
        const month = event.currentTarget.dataset.month;
        const monthInputs = row.parentElement.querySelectorAll(
          `.${month}_expense`,
        );
        let mTotal = 0;
        monthInputs.forEach(i => (mTotal += parseFloat(i.value) || 0));
        row.parentElement.querySelector(`.${month}_total_expense`).textContent =
          mTotal;
        const allTotals = row.parentElement.querySelectorAll(".totalExpense");
        let grand = 0;
        allTotals.forEach(t => (grand += parseFloat(t.textContent) || 0));
        row.parentElement.querySelector(".expense").textContent = grand;
      } catch {
        scheduleError(getMsg("expense_calculation_failed"));
      }
    };

    const onPeriodChange = event => {
      try {
        const val = event.currentTarget.value;
        document
          .querySelectorAll(".budget_plan")
          .forEach(el => el.classList.add("d-none"));
        const target = document.getElementById(val);
        if (target) target.classList.replace("d-none", "d-block");
      } catch {
        scheduleError(getMsg("period_toggle_failed"));
      }
    };

    bindField(".income_data", onIncomeKeyup, true);
    bindField(".expense_data", onExpenseKeyup, true);
    bindField(".period", onPeriodChange, false);
    document
      .querySelectorAll(".period")
      .forEach(el => el.dispatchEvent(new Event("change")));
  });
})();
