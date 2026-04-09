/**
 * @fileoverview TypeScript version of public/assets/js/routes/employeeAttendances/clockOut.js
 * @generated from original JavaScript - manual review recommended
 * @module clockOut
 */
(() => {
    try {
        const f = document.getElementById("clock-out-form");
        if (!f)
            return;
        const flag = "data-submit-listener";
        if (f.hasAttribute(flag) && f.getAttribute(flag) === "true")
            return;
        f.setAttribute(flag, "true");
        f.addEventListener("submit", function (e) {
            try {
                const action = f.getAttribute("action") ?? "#", url = f.getAttribute("data-url") ?? "#";
                if (action !== "#" && url !== "#")
                    return;
                e.preventDefault();
                const msg = f.getAttribute("data-guard-msg") ??
                    "Clock out route is unavailable. Please contact technical support or your domain administrator.", linkEl = document.querySelector('link[href*="bootstrap"]'), hasBootstrapToast = window.bootstrap && typeof window.bootstrap.Toast === "function";
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
                            console.error(`[clockOut] Error:`, _);
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
                console.error(`[clockOut] Error:`, _);
            }
        }, { passive: false });
    }
    catch (_) {
        console.error(`[clockOut] Error:`, _);
    }
})();
//# sourceMappingURL=clockOut.js.map