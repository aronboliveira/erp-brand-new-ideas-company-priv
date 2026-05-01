(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/labels/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
(() => {
    const DEFAULT_MSG = "The requested route is unavailable. Please contact technical support or your domain administrator.";
    const showError = (message) => {
        try {
            const hasBootstrapToast = !!window.bootstrap.Toast;
            if (hasBootstrapToast) {
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    Object.assign(container.style, {
                        position: "fixed",
                        top: "1rem",
                        right: "1rem",
                        zIndex: "2000",
                    });
                    document.body.appendChild(container);
                }
                const toastEl = document.createElement("div");
                toastEl.className = "toast show";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toastEl.setAttribute(k, v);
                toastEl.style.minWidth = "280px";
                {
                    const _b = document.createElement("div");
                    _b.className = "toast-body";
                    _b.textContent = message;
                    toastEl.replaceChildren(_b);
                }
                container.appendChild(toastEl);
                setTimeout(() => {
                    toastEl.remove();
                }, 4000);
            }
            else {
                alert(message);
            }
        }
        catch {
            alert(message);
        }
    };
    const attachGuard = (form) => {
        if (!form)
            return;
        const guardMsg = form.getAttribute("data-guard-msg") || DEFAULT_MSG;
        if (!form.getAttribute("data-listener-bound-submit")) {
            form.setAttribute("data-listener-bound-submit", "1");
            form.addEventListener("submit", (e) => {
                const action = (form.getAttribute("action") ?? "").trim();
                if (!action || action === "#") {
                    e.preventDefault();
                    showError(guardMsg);
                }
            });
        }
    };
    document.addEventListener("DOMContentLoaded", () => {
        // Primary target by id
        const mainForm = document.getElementById("label-edit-form");
        attachGuard(mainForm);
        // Fallback: any form with a guard message
        document
            .querySelectorAll("form[data-guard-msg]")
            .forEach(f => f !== mainForm && attachGuard(f));
    });
})();
})();