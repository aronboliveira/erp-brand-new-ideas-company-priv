(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/roles/cancel.js
 * @generated from original JavaScript - manual review recommended
 * @module cancel
 */
(() => {
    const link = document.getElementById("role-index-cancel-link");
    if (!link || link.getAttribute("data-listener-active") === "true")
        return;
    link.setAttribute("data-listener-active", "true");
    if (!link.getAttribute("data-listener-bound-click")) {
        link.setAttribute("data-listener-bound-click", "1");
        link.addEventListener("click", (e) => {
            try {
                const url = link.getAttribute("data-url") ?? "#";
                if (url !== "#")
                    return;
                e.preventDefault();
                const msg = link.getAttribute("data-guard-msg") ?? "# ERROR", hasBootstrap = document.querySelector('link[href*="bootstrap"]') &&
                    window.bootstrap;
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
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
                    bootstrap.Toast.getOrCreateInstance(toast).show();
                }
                else {
                    alert(msg);
                }
                link.setAttribute("data-failed-route", "true");
            }
            catch (err) {
                console.error(`[cancel] Error:`, err);
            }
        });
    }
})();
})();