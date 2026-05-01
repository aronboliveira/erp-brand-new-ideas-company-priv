(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/menu/productService.js
 * @generated from original JavaScript - manual review recommended
 * @module productService
 */
(() => {
    const bindGuard = (id) => {
        const listenerAttr = `data-${id}-listener-active`, el = document.getElementById(id);
        if (!el || el.getAttribute(listenerAttr) === "true")
            return;
        el.setAttribute(listenerAttr, "true");
        if (!el.getAttribute("data-listener-bound-click")) {
            el.setAttribute("data-listener-bound-click", "1");
            el.addEventListener("click", event => {
                try {
                    const url = el.getAttribute("data-url"), href = el.href
                        .replace(window.location.origin, "")
                        .replace(window.location.pathname, "");
                    if ((!url || url === "#") && (!href || href === "#")) {
                        event.preventDefault();
                        const msg = el.getAttribute("data-guard-msg") ?? "# ERROR", bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container = document.getElementById("toast-container");
                        if (!container) {
                            container = document.createElement("div");
                            container.id = "toast-container";
                            container.className =
                                "toast-container position-fixed top-0 end-0 p-3";
                            container.style.zIndex = "1080";
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement("div");
                            toastEl.className = "toast";
                            for (const [k, v] of Object.entries({
                                role: "alert",
                                "aria-live": "assertive",
                                "aria-atomic": "true",
                            }))
                                toastEl.setAttribute(k, v);
                            const body = document.createElement("div");
                            body.className = "toast-body";
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        }
                        else {
                            alert(msg);
                        }
                        el.setAttribute("data-failed-route", "true");
                    }
                }
                catch (__err) {
                    console.error(`[productService] Error:`, __err);
                }
            });
        }
        const observer = new MutationObserver(() => {
            if (!document.getElementById(id))
                observer.disconnect();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    };
    ["product-services-index-link", "product-stock-index-link"].forEach(bindGuard);
})();
})();