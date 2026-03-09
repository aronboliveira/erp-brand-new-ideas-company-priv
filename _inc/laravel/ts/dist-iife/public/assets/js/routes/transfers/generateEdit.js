(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/transfers/generateEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module generateEdit
 */
(() => {
    try {
        const links = document.querySelectorAll('a[data-ajax-popup-over="true"][data-url][data-guard-msg]');
        if (!links || links.length === 0)
            return;
        links.forEach(l => {
            try {
                if (!l || l.getAttribute("data-listener-active") === "true")
                    return;
                l.setAttribute("data-listener-active", "true");
                const url = l.getAttribute("data-url") ?? "#", href = l.getAttribute("href") ?? "#";
                if (href === "#" && url !== "#")
                    l.setAttribute("href", url);
                l.addEventListener("click", (e) => {
                    try {
                        const u = l.getAttribute("data-url") ?? l.getAttribute("href") ?? "#";
                        if (u !== "#")
                            return;
                        e.preventDefault();
                        const msgAttr = l.getAttribute("data-guard-msg") ?? "", msg = msgAttr.trim().length
                            ? msgAttr
                            : "Generate transfer content route is unavailable. Please contact technical support or your domain administrator.", bsLink = document.querySelector('link[href*="bootstrap"]');
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
                        l.setAttribute("data-failed-route", "true");
                    }
                    catch (err) {
                        console.error(`[generateEdit] Error:`, err);
                    }
                });
            }
            catch (innerErr) {
                console.error(`[generateEdit] Error:`, innerErr);
            }
        });
    }
    catch (error) {
        console.error(`[generateEdit] Error:`, error);
    }
})();
})();