(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/employees/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
(function () {
    try {
        const f = document.getElementById("update_employee_form");
        if (!f || f.getAttribute("data-listener-active") === "true")
            return;
        f.setAttribute("data-listener-active", "true");
        const resolved = f.getAttribute("data-resolved-action") ?? "#";
        if ((f.getAttribute("action") === "" || f.getAttribute("action") === "#") &&
            resolved !== "#")
            f.setAttribute("action", resolved);

        function notify(msg) {
            try {
                if (window.bootstrap.Toast) {
                    const c = document.getElementById("toast-container") ??
                        (function () {
                            const d = document.createElement("div");
                            d.id = "toast-container";
                            document.body.appendChild(d);
                            return d;
                        })();
                    const t = document.createElement("div");
                    t.className = "toast";
                    for (const [k, v] of Object.entries({
                        role: "alert",
                        "aria-live": "assertive",
                        "aria-atomic": "true",
                    }))
                        t.setAttribute(k, v);
                    const b = document.createElement("div");
                    b.className = "toast-body";
                    b.textContent = msg;
                    t.appendChild(b);
                    c.appendChild(t);
                    window.bootstrap.Toast.getOrCreateInstance(t).show();
                }
                else {
                    alert(msg);
                }
            }
            catch (_) {
                alert(msg);
            }
        }
        f.addEventListener("submit", function (e) {
            try {
                const action = f.getAttribute("action") ?? "#";
                if (action && action !== "#")
                    return;
                e.preventDefault();
                const msg = f.getAttribute("data-guard-msg") ??
                    "Requested route is unavailable. Please contact technical support or your domain administrator.";
                notify(msg);
                f.setAttribute("data-failed-route", "true");
            }
            catch (_) {
                console.error(`[update] Error:`, _);
            }
        });
    }
    catch (_) {
        console.error(`[update] Error:`, _);
    }
})();
})();