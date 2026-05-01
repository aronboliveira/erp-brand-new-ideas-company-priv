(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/awards/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    const attachGuard = (el, eventType) => {
        if (!el || el.getAttribute("data-listener-active") === "true")
            return;
        el.setAttribute("data-listener-active", "true");
        el.addEventListener(eventType, event => {
            try {
                const href = el.tagName === "A" ? el.getAttribute("href") : null, url = el.getAttribute("data-url");
                if ((href && href !== "#") || (url && url !== "#"))
                    return;
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
            catch (e) {
                console.error(`[index] Error:`, e);
            }
        });
    };
    attachGuard(document.getElementById("award-create-button"), "click");
    document
        .querySelectorAll('[id^="award-edit-"]')
        .forEach((el) => {
        attachGuard(el, "click");
    });
    document
        .querySelectorAll('[id^="award-delete-"]')
        .forEach((el) => {
        attachGuard(el, "click");
    });
})();
})();