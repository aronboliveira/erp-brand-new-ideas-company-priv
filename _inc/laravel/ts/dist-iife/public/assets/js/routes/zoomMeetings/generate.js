(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */
(() => {
    try {
        const a = document.getElementById("zoom-ai-generate-link");
        if (!a || a.getAttribute("data-listener-active") === "true")
            return;
        a.setAttribute("data-listener-active", "true");
        const url = a.getAttribute("data-url") ?? "#";
        if (a.hasAttribute("href") &&
            (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
            url !== "#")
            a.setAttribute("href", url);
        a.addEventListener("click", (e) => {
            try {
                const href = a.getAttribute("href") ?? "#";
                if (href && href !== "#")
                    return;
                e.preventDefault();
                const msg = a.getAttribute("data-guard-msg") ??
                    "AI generation route is unavailable. Please contact technical support or your domain administrator.";
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
                const hasBs = window.bootstrap.Toast;
                if (hasBs) {
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
                    try {
                        window.bootstrap.Toast.getOrCreateInstance(toast).show();
                    }
                    catch {
                        alert(msg);
                    }
                }
                else {
                    alert(msg);
                }
                a.setAttribute("data-failed-route", "true");
            }
            catch (__err) {
                console.error(`[generate] Error:`, __err);
            }
        });
    }
    catch (__err) {
        console.error(`[generate] Error:`, __err);
    }
})();
})();