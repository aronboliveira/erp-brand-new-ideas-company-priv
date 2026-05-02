(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/goals/trackings/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
(() => {
    try {
        const toast = (msg) => {
            const text = msg ??
                "Update route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
            let container = document.getElementById("toast-container");
            if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className = "toast-container position-fixed top-0 end-0 p-3";
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
                b.textContent = text;
                t.appendChild(b);
                container.appendChild(t);
                bootstrap.Toast.getOrCreateInstance(t).show();
            }
            else {
                alert(text);
            }
        };
        const fm = document.getElementById("goal-tracking-edit-form");
        if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
            fm.setAttribute("data-submit-guarded", "true");
            if (!fm.getAttribute("data-listener-bound-submit")) {
                fm.setAttribute("data-listener-bound-submit", "1");
                fm.addEventListener("submit", (e) => {
                    try {
                        const action = (fm.getAttribute("action") ?? "#").trim(), url = (fm.getAttribute("data-url") ?? "#").trim();
                        if (url === "#" || action === "#") {
                            e.preventDefault();
                            toast(fm.getAttribute("data-guard-msg") ?? "");
                            fm.setAttribute("data-failed-route", "true");
                        }
                    }
                    catch (__err) {
                        console.error(`[edit] Error:`, __err);
                    }
                });
            }
        }
        const range = document.getElementById("goal-progress-range"), out = document.getElementById("goal-progress-output");
        if (range && out) {
            const sync = () => {
                try {
                    out.textContent = String(range.value ?? "0");
                }
                catch (__err) {
                    console.error(`[edit] Error:`, __err);
                }
            };
            if (!range.getAttribute("data-listener-bound-input")) {
                range.setAttribute("data-listener-bound-input", "1");
                range.addEventListener("input", sync);
            }
            range.addEventListener("change", sync);
            sync();
        }
        try {

            const els = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));


            els.forEach((el) => {
                try {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                }
                catch (__err) {
                    console.error(`[edit] Error:`, __err);
                }
            });
        }
        catch (__err) {
            console.error(`[edit] Error:`, __err);
        }
    }
    catch (__err) {
        console.error(`[edit] Error:`, __err);
    }
})();
})();