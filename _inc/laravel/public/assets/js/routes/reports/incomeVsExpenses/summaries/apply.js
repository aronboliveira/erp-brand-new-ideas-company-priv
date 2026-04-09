/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/incomeVsExpenses/summaries/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */
(() => {
    try {
        const host = document.documentElement, flag = "data-apply-income-vs-expense-summary-listener";
        if (host.hasAttribute(flag) && host.getAttribute(flag) === "true")
            return;
        host.setAttribute(flag, "true");
        document.addEventListener("click", function (e) {
            try {
                const a = e.target &&
                    (e.target.closest
                        ? e.target.closest("a.apply-income-vs-expense-summary")
                        : null);
                if (!a)
                    return;
                e.preventDefault();
                const formId = a.getAttribute("data-form-id") ?? "", f = formId ? document.getElementById(formId) : null;
                if (!f)
                    return;
                const _action = f.getAttribute("action") ?? "#", url = f.getAttribute("data-url") ?? "#";
                if (url !== "#") {
                    try {
                        f.submit();
                    }
                    catch (_) {
                        console.error(`[apply] Error:`, _);
                    }
                    return;
                }
                const msg = f.getAttribute("data-guard-msg") ??
                    a.getAttribute("data-guard-msg") ??
                    "Income vs expense summary report route is unavailable. Please contact technical support or your domain administrator.", linkEl = document.querySelector('link[href*="bootstrap"]'), hasBootstrapToast = window.bootstrap && typeof window.bootstrap.Toast === "function";
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    container.className = "position-fixed top-0 end-0 p-3";
                    document.body.appendChild(container);
                }
                if (linkEl && hasBootstrapToast) {
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
                    body.textContent = msg;
                    toast.appendChild(body);
                    container.appendChild(toast);
                    const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                    toast.addEventListener("hidden.bs.toast", function () {
                        try {
                            toast.remove();
                        }
                        catch (_) {
                            console.error(`[apply] Error:`, _);
                        }
                    });
                    inst.show();
                }
                else {
                    alert(msg);
                }
                f.setAttribute("data-failed-route", "true");
            }
            catch (_) {
                console.error(`[apply] Error:`, _);
            }
        }, { passive: false });
    }
    catch (_) {
        console.error(`[apply] Error:`, _);
    }
})();
//# sourceMappingURL=apply.js.map