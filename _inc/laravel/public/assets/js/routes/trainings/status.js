(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/trainings/status.js
 * @generated from original JavaScript - manual review recommended
 * @module status
 */
(() => {
    try {
        const f = document.getElementById("training-status-form");
        if (!f)
            return;
        if (f.getAttribute("data-listener-active") === "true")
            return;
        f.setAttribute("data-listener-active", "true");
        const resolved = f.getAttribute("data-resolved-action") ?? "#";
        if (f.hasAttribute("action") && (f.getAttribute("action") === "#" || !f.getAttribute("action")) && resolved !== "#")
            f.setAttribute("action", resolved);
        f.addEventListener("submit", (e) => {
            try {
                const action = f.getAttribute("action") ?? "#";
                if (action && action !== "#")
                    return;
                e.preventDefault();
                const msg = f.getAttribute("data-guard-msg") ?? "Update training status route is unavailable. Please contact technical support or your domain administrator.";
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className = "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
                const bs = document.querySelector('link[href*="bootstrap"]');
                if (bs && window.bootstrap.Toast) {
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
                    container.appendChild(t);
                    try {
                        window.bootstrap.Toast.getOrCreateInstance(t).show();
                    }
                    catch {
                        alert(msg);
                    }
                }
                else {
                    alert(msg);
                }
                f.setAttribute("data-failed-route", "true");
            }
            catch (_err) {
                /* no-op */
            }
        });
    }
    catch (_) {
        console.error(`[status] Error:`, _);
    }
})();
})();