(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/indexBreadcrumb.js
 * @generated from original JavaScript - manual review recommended
 * @module indexBreadcrumb
 */
(() => {
    const el = document.getElementById("deal-index-breadcrumb");
    if (!el || el.getAttribute("data-listener-active") === "true")
        return;
    el.setAttribute("data-listener-active", "true");
    if (!el.getAttribute("data-listener-bound-click")) {
        el.setAttribute("data-listener-bound-click", "1");
        el.addEventListener("click", (e) => {
            try {
                const url = el.getAttribute("data-url") ?? "#";
                if (url !== "#")
                    return;
                e.preventDefault();
                const msg = el.getAttribute("data-guard-msg") ?? "# ERROR", bs = document.querySelector('link[href*="bootstrap"]') &&
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
                    body.textContent = msg;
                    toast.appendChild(body);
                    container.appendChild(toast);
                    bootstrap.Toast.getOrCreateInstance(toast).show();
                }
                else {
                    alert(msg);
                }
                el.setAttribute("data-failed-route", "true");
            }
            catch (__err) {
                console.error(`[indexBreadcrumb] Error:`, __err);
            }
        });
    }
})();
})();