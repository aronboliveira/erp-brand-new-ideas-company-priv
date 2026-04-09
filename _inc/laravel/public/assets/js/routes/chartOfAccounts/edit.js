/**
 * @fileoverview TypeScript version of public/assets/js/routes/chartOfAccounts/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */
(() => {
    const bindGuard = (el, event, urlAttr = "data-url", msgAttr = "data-guard-msg") => {
        if (!el || el.getAttribute("data-listener-active") === "true")
            return;
        el.setAttribute("data-listener-active", "true");
        el.addEventListener(event, e => {
            try {
                const url = el.getAttribute(urlAttr) ?? "#";
                if (url !== "#")
                    return;
                e.preventDefault();
                const msg = el.getAttribute(msgAttr) ?? "# ERROR", bootstrapLink = document.querySelector('link[href*="bootstrap"]');
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
                console.error(`[edit] Error:`, e);
            }
        });
    };
    const filterForm = document.getElementById("report_bill_summary");
    bindGuard(filterForm, "submit");
    const applyBtn = document.getElementById("applyFilter");
    bindGuard(applyBtn, "click");
    document
        .querySelectorAll('[data-listener-alias="ledger-link"]')
        .forEach((el) => {
        bindGuard(el, "click");
    });
    document
        .querySelectorAll('[data-listener-alias="edit-account"]')
        .forEach((el) => {
        bindGuard(el, "click");
    });
    document
        .querySelectorAll('[data-listener-alias="delete-account"]')
        .forEach((el) => {
        bindGuard(el, "click");
    });
})();
//# sourceMappingURL=edit.js.map