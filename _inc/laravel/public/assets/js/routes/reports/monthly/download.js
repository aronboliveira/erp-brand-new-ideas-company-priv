/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/monthly/download.js
 * @generated from original JavaScript - manual review recommended
 * @module download
 */
(() => {
    try {
        document
            .querySelectorAll(".download-pdf-link")
            .forEach((el) => {
            try {
                const alias = "data-listening-downloadpdfclick";
                if (!el.hasAttribute(alias)) {
                    el.setAttribute(alias, "true");
                    el.addEventListener("click", event => {
                        try {
                            const url = el.getAttribute("data-url"), href = el.getAttribute("href") ?? "";
                            if ((url && url !== "#") ?? (href && href !== "#"))
                                return;
                            event.preventDefault();
                            const funcName = el.getAttribute("data-func-name") ?? "", fn = window[funcName];
                            if (typeof fn !== "function") {
                                const msg = el.getAttribute("data-guard-msg") ??
                                    "Download function for monthly reports is unavailable. Please contact technical support or your domain administrator.";
                                const hasBS = Array.from(document.scripts).some(s => s.src.includes("bootstrap.min.js") &&
                                    window.bootstrap &&
                                    typeof bootstrap.Toast === "function");
                                if (hasBS) {
                                    const container = document.getElementById("toast-container") ??
                                        (() => {
                                            const d = document.createElement("div");
                                            d.id = "toast-container";
                                            d.className =
                                                "toast-container position-fixed bottom-0 end-0 p-3";
                                            document.body.appendChild(d);
                                            return d;
                                        })();
                                    const toastEl = document.createElement("div");
                                    toastEl.className =
                                        "toast align-items-center text-bg-danger border-0";
                                    for (const [k, v] of Object.entries({
                                        role: "alert",
                                        "aria-live": "assertive",
                                        "aria-atomic": "true",
                                    }))
                                        toastEl.setAttribute(k, v);
                                    toastEl.innerHTML =
                                        '<div class="d-flex"><div class="toast-body">' +
                                            msg +
                                            '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
                                    container.appendChild(toastEl);
                                    new bootstrap.Toast(toastEl, { delay: 5000 }).show();
                                }
                                else {
                                    alert(msg);
                                }
                                return;
                            }
                            fn();
                        }
                        catch (__err) {
                            console.error(`[download] Error:`, __err);
                        }
                    });
                }
            }
            catch (__err) {
                console.error(`[download] Error:`, __err);
            }
        });
    }
    catch (__err) {
        console.error(`[download] Error:`, __err);
    }
})();
//# sourceMappingURL=download.js.map