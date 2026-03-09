(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/trainings/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    try {
        const a = document.getElementById("training-index-link");
        if (!a)
            return;
        if (a.getAttribute("data-listener-active") === "true")
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
                if (href !== "#")
                    return;
                e.preventDefault();
                const msg = a.getAttribute("data-guard-msg") ??
                    "Training index route is unavailable. Please contact technical support or your domain administrator.";
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
                const bs = document.querySelector('link[href*="bootstrap"]');
                if (bs && typeof window.bootstrap !== "undefined") {
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
                    window.bootstrap.Toast.getOrCreateInstance(t).show();
                }
                else {
                    alert(msg);
                }
                a.setAttribute("data-failed-route", "true");
            }
            catch (_) {
                console.error(`[index] Error:`, _);
            }
        });
    }
    catch (_) {
        console.error(`[index] Error:`, _);
    }
})();
})();