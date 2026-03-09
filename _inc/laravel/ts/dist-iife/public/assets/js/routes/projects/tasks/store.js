(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
(() => {
    try {
        const guardToast = (msg) => {
            try {
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') &&
                    window.bootstrap.Toast;
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    container.className = "position-fixed top-0 end-0 p-3";
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
                    body.textContent =
                        msg ??
                            "Requested route is unavailable. Please contact technical support or your domain administrator.";
                    toast.appendChild(body);
                    container.appendChild(toast);
                    const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                    toast.addEventListener("hidden.bs.toast", function () {
                        try {
                            toast.remove();
                        }
                        catch (_) {
                            console.error(`[store] Error:`, _);
                        }
                    });
                    inst.show();
                }
                else {
                    alert(msg ??
                        "Requested route is unavailable. Please contact technical support or your domain administrator.");
                }
            }
            catch (_) {
                console.error(`[store] Error:`, _);
            }
        };
        const f = document.getElementById("store_task");
        if (f &&
            !(f.hasAttribute("data-submit-listener") &&
                f.getAttribute("data-submit-listener") === "true")) {
            f.setAttribute("data-submit-listener", "true");
            f.addEventListener("submit", function (e) {
                try {
                    const action = f.getAttribute("action") ?? "#";
                    if (action !== "#")
                        return;
                    e.preventDefault();
                    const msg = f.getAttribute("data-guard-msg") ??
                        "Create project task route is unavailable. Please contact technical support or your domain administrator.";
                    guardToast(msg);
                    f.setAttribute("data-failed-route", "true");
                }
                catch (_) {
                    console.error(`[store] Error:`, _);
                }
            }, { passive: false });
        }
        const ai = document.getElementById("project-task-ai-generate-link");
        if (ai &&
            !(ai.hasAttribute("data-ai-listener") &&
                ai.getAttribute("data-ai-listener") === "true")) {
            ai.setAttribute("data-ai-listener", "true");
            ai.addEventListener("click", function (e) {
                try {
                    const href = ai.getAttribute("href") ?? "#", url = ai.getAttribute("data-url") ?? "#";
                    if (href !== "#" || url !== "#")
                        return;
                    e.preventDefault();
                    const msg = ai.getAttribute("data-guard-msg") ??
                        "Generate project task content route is unavailable. Please contact technical support or your domain administrator.";
                    guardToast(msg);
                    ai.setAttribute("data-failed-route", "true");
                }
                catch (_) {
                    console.error(`[store] Error:`, _);
                }
            }, { passive: false });
        }
    }
    catch (_) {
        console.error(`[store] Error:`, _);
    }
})();
})();