(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/indexBc.js
 * @generated from original JavaScript - manual review recommended
 * @module indexBc
 */
(() => {
    try {
        const a = document.getElementById("bc-job-index-link");
        if (!a)
            return;
        if (a.getAttribute("data-listener-active") === "true")
            return;
        a.setAttribute("data-listener-active", "true");
        a.addEventListener("click", (e) => {
            try {
                const href = (a.getAttribute("href") ?? "#").trim(), url = (a.getAttribute("data-url") ?? "#").trim();
                if (url !== "#" && href !== "#")
                    return;
                e.preventDefault();
                const msg = a.getAttribute("data-guard-msg") ??
                    "Job index route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') &&
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
                a.setAttribute("data-failed-route", "true");
            }
            catch (err) {
                console.error(`[indexBc] Error:`, err);
            }
        });
    }
    catch (err) {
        console.error(`[indexBc] Error:`, err);
    }
})();
})();