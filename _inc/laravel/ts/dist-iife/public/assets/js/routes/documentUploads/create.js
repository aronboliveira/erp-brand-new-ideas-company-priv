(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/documentUploads/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */
(() => {
    try {
        const el = document.getElementById("document-create-btn");
        if (!el)
            return;
        if (el.getAttribute("data-click-guarded") === "true")
            return;
        el.setAttribute("data-click-guarded", "true");
        if (!el.getAttribute("data-listener-bound-click")) {
            el.setAttribute("data-listener-bound-click", "1");
            el.addEventListener("click", (e) => {
                try {
                    const url = (el.getAttribute("data-url") ?? "#").trim();
                    if (url !== "#")
                        return;
                    e.preventDefault();
                    const msg = el.getAttribute("data-guard-msg") ??
                        "Create document route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') &&
                        window.bootstrap);
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
                        bootstrap.Toast.getOrCreateInstance(t).show();
                    }
                    else {
                        alert(msg);
                    }
                    el.setAttribute("data-failed-route", "true");
                }
                catch (__err) {
                    console.error(`[create] Error:`, __err);
                }
            });
        }
    }
    catch (__err) {
        console.error(`[create] Error:`, __err);
    }
})();
})();