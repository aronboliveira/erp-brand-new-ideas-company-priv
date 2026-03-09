(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/transfers/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
(() => {
    try {
        const f = document.getElementById("edit_transfer");
        if (!f || f.getAttribute("data-listener-active") === "true")
            return;
        f.setAttribute("data-listener-active", "true");
        const resolved = f.getAttribute("data-resolved-action") ?? "#";
        if (f.hasAttribute("action") &&
            f.getAttribute("action") === "#" &&
            resolved !== "#")
            f.setAttribute("action", resolved);
        f.addEventListener("submit", (e) => {
            try {
                const action = f.getAttribute("action") ?? "#";
                if (action !== "#")
                    return;
                e.preventDefault();
                const msgAttr = f.getAttribute("data-guard-msg") ?? "", msg = msgAttr.trim().length
                    ? msgAttr
                    : "Update transfer route is unavailable. Please contact technical support or your domain administrator.", bsLink = document.querySelector('link[href*="bootstrap"]');
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
                if (bsLink && typeof window.bootstrap !== "undefined") {
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
                console.error(`[update] Error:`, err);
            }
        });
    }
    catch (error) {
        console.error(`[update] Error:`, error);
    }
})();
})();