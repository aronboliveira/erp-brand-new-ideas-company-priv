(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/product.js
 * @generated from original JavaScript - manual review recommended
 * @module product
 */
(() => {
    try {
        const selects = Array.from(document.querySelectorAll("select.invoice-product-select[data-url][data-guard-msg]"));
        if (selects.length === 0)
            return;
        selects.forEach(sel => {
            try {
                if (sel.getAttribute("data-change-guarded") === "true")
                    return;
                sel.setAttribute("data-change-guarded", "true");
                sel.addEventListener("change", (e) => {
                    try {
                        const url = (sel.getAttribute("data-url") ?? "#").trim();
                        if (url !== "#")
                            return;
                        e.preventDefault();
                        const msg = sel.getAttribute("data-guard-msg") ??
                            "Invoice product route is unavailable. Please contact technical support or your domain administrator.";
                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') &&
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
                        sel.setAttribute("data-failed-route", "true");
                    }
                    catch (err) {
                        console.error(`[product] Error:`, err);
                    }
                });
            }
            catch (err) {
                console.error(`[product] Error:`, err);
            }
        });
    }
    catch (err) {
        console.error(`[product] Error:`, err);
    }
})();
})();