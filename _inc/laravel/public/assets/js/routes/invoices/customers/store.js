(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/customers/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
(() => {
    const selectEl = document.getElementById("customer");
    if (!selectEl || selectEl.getAttribute("data-listener-active") === "true")
        return;
    selectEl.setAttribute("data-listener-active", "true");
    if (!selectEl.getAttribute("data-listener-bound-change")) {
        selectEl.setAttribute("data-listener-bound-change", "1");
        selectEl.addEventListener("change", event => {
            try {
                const url = selectEl.getAttribute("data-url");
                if (url && url !== "#")
                    return;
                event.preventDefault();
                const msg = selectEl.getAttribute("data-guard-msg") ?? "# ERROR", bootstrapLink = document.querySelector('link[href*="bootstrap"]');
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
                selectEl.setAttribute("data-failed-route", "true");
            }
            catch (e) {
                console.error(`[store] Error:`, e);
            }
        });
    }
})();
})();