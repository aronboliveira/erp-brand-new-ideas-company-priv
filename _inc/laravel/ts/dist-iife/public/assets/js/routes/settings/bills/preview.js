(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/bills/preview.js
 * @generated from original JavaScript - manual review recommended
 * @module preview
 */
(() => {
    try {
        const previewFrame = document.getElementById("bill-template-preview-frame");
        if (!previewFrame)
            return;
        if (previewFrame.getAttribute("data-listener-active") === "true")
            return;
        previewFrame.setAttribute("data-listener-active", "true");
        const src = previewFrame.getAttribute("src") ?? "#", url = previewFrame.getAttribute("data-url") ?? "#";
        if (url !== "#" && src !== "#")
            return;
        const msg = previewFrame.getAttribute("data-guard-msg") ??
            "Bill preview route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
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
        previewFrame.setAttribute("data-failed-route", "true");
    }
    catch (err) {
        console.error(`[preview] Error:`, err);
    }
})();
})();