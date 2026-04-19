/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */
(() => {
    const toast = (msg) => {
        try {
            if (window.bootstrap.Toast) {
                const c = document.getElementById("toast-container") ??
                    (() => {
                        const t = document.createElement("div");
                        t.id = "toast-container";
                        document.body.appendChild(t);
                        return t;
                    })();
                const el = document.createElement("div");
                el.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    el.setAttribute(k, v);
                const body = document.createElement("div");
                body.className = "toast-body";
                body.textContent = msg;
                el.appendChild(body);
                c.appendChild(el);
                window.bootstrap.Toast.getOrCreateInstance(el).show();
            }
            else {
                alert(msg);
            }
        }
        catch {
            alert(msg);
        }
    };
    const mail = document.getElementById("payslip-mail-send");
    if (mail) {
        if (!mail.getAttribute("data-listener-bound-click")) {
            mail.setAttribute("data-listener-bound-click", "1");
            mail.addEventListener("click", (e) => {
                const url = mail.getAttribute("href") ?? mail.getAttribute("data-url") ?? "#";
                if (!url || url === "#") {
                    e.preventDefault();
                    const msg = mail.getAttribute("data-guard-msg") ??
                        "Send Payslip route is unavailable. Please contact technical support or your domain administrator.";
                    toast(msg);
                }
            });
        }
    }
    const printableId = "printableArea", printBtn = document.getElementById("payslip-download");
    const printFn = () => {
        const el = document.getElementById(printableId);
        if (!el)
            return;
        const w = window.open("about:blank", "_blank", "noopener,noreferrer");
        if (!w)
            return;
        const bootstrapHref = document.querySelector('link[href*="bootstrap"]')?.href ?? "";
        const newDoc = w.document;
        newDoc.head.innerHTML = [
            "<title>Payslip</title>",
            '<meta charset="utf-8" />',
            '<meta name="viewport" content="width=device-width, initial-scale=1" />',
            `<link rel="stylesheet" href="${bootstrapHref}">`,
            "<style>body{padding:16px}</style>",
        ].join("");
        newDoc.body.innerHTML = el.innerHTML;
        w.focus();
        setTimeout(() => {
            w.print();
        }, 300);
    };
    if (printBtn)
        if (!printBtn.getAttribute("data-listener-bound-click")) {
            printBtn.setAttribute("data-listener-bound-click", "1");
            printBtn.addEventListener("click", (e) => {
                e.preventDefault();
                printFn();
            });
        }
    if (!window.saveAsPDF)
        window.saveAsPDF = printFn;
})();
//# sourceMappingURL=pdf.js.map