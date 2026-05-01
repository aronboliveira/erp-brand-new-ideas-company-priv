(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/ips/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */
(() => {
    try {
        const f = document.getElementById("ip-create-form");
        if (!f || f.getAttribute("data-listener-active") === "true")
            return;
        f.setAttribute("data-listener-active", "true");
        f.addEventListener("submit", (e) => {
            try {
                const action = f.getAttribute("action") ?? "#", url = f.getAttribute("data-action-url") ?? "#";
                if (action !== "#" && url !== "#")
                    return;
                e.preventDefault();
                const msg = f.getAttribute("data-form-guard-msg") ??
                    "Create IP route is unavailable. Please contact technical support or your domain administrator.";
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
                const bootstrapLink = document.querySelector('link[href*="bootstrap"]') ??
                    document.querySelector('link[href*="bootstrap.min"]'), hasBootstrap = !!bootstrapLink &&
                    typeof window !== "undefined" &&
                    typeof window.bootstrap !== "undefined";
                if (hasBootstrap) {
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
                    window.bootstrap.Toast.getOrCreateInstance(toast).show();
                }
                else {
                    alert(msg);
                }
                f.setAttribute("data-failed-route", "true");
            }
            catch (err) {
                console.error(`[create] Error:`, err);
            }
        });
    }
    catch (error) {
        console.error(`[create] Error:`, error);
    }
})();
})();