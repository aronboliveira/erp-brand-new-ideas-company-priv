(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/proposals/preview.js
 * @generated from original JavaScript - manual review recommended
 * @module preview
 */
(() => {
    try {
        const ifr = document.getElementById("proposal-template-preview-frame");
        if (!ifr)
            return;
        if (ifr.getAttribute("data-listener-active") === "true")
            return;
        ifr.setAttribute("data-listener-active", "true");
        const src = ifr.getAttribute("src") ?? "#", url = ifr.getAttribute("data-url") ?? "#";
        if (url !== "#" && src !== "#")
            return;
        const msg = ifr.getAttribute("data-guard-msg") ??
            "Proposal preview route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
        let container = document.getElementById("toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className = "toast-container position-fixed top-0 end-0 p-3";
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
        ifr.setAttribute("data-failed-route", "true");
    }
    catch (err) {
        console.error(`[preview] Error:`, err);
    }
})();
})();