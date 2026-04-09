/**
 * @fileoverview TypeScript version of public/assets/js/routes/budgets/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */
(() => {
    const errFb = "# ERROR", clientFlag = "data-client-localized", guardMsgKey = "data-guard-msg", langKey = "erp-np-lang";
    function getLocalizedMessage(key, el) {
        let msg = errFb;
        if (el.getAttribute(clientFlag) === "true") {
            msg = el.getAttribute(guardMsgKey) ?? msg;
        }
        else {
            let lang = (sessionStorage.getItem(langKey) ??
                document.documentElement.lang ??
                "en")
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
    function showError(message) {
        try {
            let container = document.getElementById("toast-container");
            if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className = "toast-container position-fixed top-0 end-0 p-3";
                container.style.zIndex = "1080";
                document.body.appendChild(container);
            }
            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
            if (bs) {
                const toast = document.createElement("div");
                toast.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toast.setAttribute(k, v);
                const body = document.createElement("div");
                body.className = "toast-body";
                body.textContent = message;
                toast.appendChild(body);
                container.appendChild(toast);
                bootstrap.Toast.getOrCreateInstance(toast).show();
            }
            else {
                alert(message);
            }
        }
        catch {
            alert(message);
        }
    }
    let errorMessage = "";
    const onErrorPointerUp = () => {
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
    document.addEventListener("DOMContentLoaded", () => {
        const bindField = (selector, handler, isThrottle) => {
            document
                .querySelectorAll(selector)
                .forEach((el) => {
                if (el.dataset.listenerAttached === "true")
                    return;
                el.dataset.listenerAttached = "true";
                el.addEventListener(isThrottle ? "keyup" : "change", handler);
                new MutationObserver((ms, obs) => {
                    ms.forEach(m => {
                        Array.from(m.removedNodes).forEach(n => {
                            if (n === el) {
                                el.removeEventListener(isThrottle ? "keyup" : "change", handler);
                                obs.disconnect();
                            }
                        });
                    });
                }).observe(document.body, { childList: true, subtree: true });
            });
        };
        const onIncomeKeyup = (event) => {
            try {
                const target = event.currentTarget, row = target.closest("tr");
                if (!row)
                    return;
                const inputs = row.querySelectorAll(".income_data");
                let total = 0;
                inputs.forEach((i) => (total += parseFloat(i.value) || 0));
                const totalIncomeEl = row.querySelector(".totalIncome");
                if (totalIncomeEl)
                    totalIncomeEl.textContent = String(total);
                const month = target.dataset.month;
                const monthInputs = row.parentElement?.querySelectorAll(`.${month}_income`);
                let mTotal = 0;
                monthInputs?.forEach((i) => (mTotal += parseFloat(i.value) || 0));
                const monthTotalEl = row.parentElement?.querySelector(`.${month}_total_income`);
                if (monthTotalEl)
                    monthTotalEl.textContent = String(mTotal);
                const allTotals = row.parentElement?.querySelectorAll(".totalIncome");
                let grand = 0;
                allTotals?.forEach((t) => (grand += parseFloat(t.textContent ?? "0") || 0));
                const incomeEl = row.parentElement?.querySelector(".income");
                if (incomeEl)
                    incomeEl.textContent = String(grand);
            }
            catch {
                errorMessage = getLocalizedMessage("income_calculation_failed", event.currentTarget);
            }
        };
        const onExpenseKeyup = (event) => {
            try {
                const target = event.currentTarget, row = target.closest("tr");
                if (!row)
                    return;
                const inputs = row.querySelectorAll(".expense_data");
                let total = 0;
                inputs.forEach((i) => (total += parseFloat(i.value) || 0));
                const totalExpenseEl = row.querySelector(".totalExpense");
                if (totalExpenseEl)
                    totalExpenseEl.textContent = String(total);
                const month = target.dataset.month;
                const monthInputs = row.parentElement?.querySelectorAll(`.${month}_expense`);
                let mTotal = 0;
                monthInputs?.forEach((i) => (mTotal += parseFloat(i.value) || 0));
                const monthTotalEl = row.parentElement?.querySelector(`.${month}_total_expense`);
                if (monthTotalEl)
                    monthTotalEl.textContent = String(mTotal);
                const allTotals = row.parentElement?.querySelectorAll(".totalExpense");
                let grand = 0;
                allTotals?.forEach((t) => (grand += parseFloat(t.textContent ?? "0") || 0));
                const expenseEl = row.parentElement?.querySelector(".expense");
                if (expenseEl)
                    expenseEl.textContent = String(grand);
            }
            catch {
                errorMessage = getLocalizedMessage("expense_calculation_failed", event.currentTarget);
            }
        };
        const onPeriodChange = (event) => {
            try {
                const target = event.currentTarget, val = target.value;
                document
                    .querySelectorAll(".budget_plan")
                    .forEach((el) => {
                    el.classList.add("d-none");
                });
                const targetEl = document.getElementById(val);
                if (targetEl)
                    targetEl.classList.replace("d-none", "d-block");
            }
            catch {
                showError(getLocalizedMessage("period_toggle_failed", event.currentTarget));
            }
        };
        bindField(".income_data", onIncomeKeyup, true);
        bindField(".expense_data", onExpenseKeyup, true);
        bindField(".period", onPeriodChange, false);
        document.querySelectorAll(".period").forEach((el) => {
            el.dispatchEvent(new Event("change"));
        });
    });
})();
//# sourceMappingURL=toggle.js.map