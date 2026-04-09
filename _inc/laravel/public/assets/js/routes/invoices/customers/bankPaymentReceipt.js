/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/customers/bankPaymentReceipt.js
 * @generated from original JavaScript - manual review recommended
 * @module bankPaymentReceipt
 */
(() => {
    document
        .querySelectorAll('[data-listener-alias^="bankpayment-receipt"]')
        .forEach((el) => {
        if (el.getAttribute("data-listener-active") === "true")
            return;
        el.setAttribute("data-listener-active", "true");
        el.addEventListener("click", event => {
            try {
                const url = el.getAttribute("data-url") ?? "#";
                if (url !== "#")
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
                console.error(`[bankPaymentReceipt] Error:`, e);
            }
        });
    });
})();
//# sourceMappingURL=bankPaymentReceipt.js.map