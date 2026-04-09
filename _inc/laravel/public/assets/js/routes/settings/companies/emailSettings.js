/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/companies/emailSettings.js
 * @generated from original JavaScript - manual review recommended
 * @module emailSettings
 */
(() => {
    try {
        document.addEventListener("DOMContentLoaded", function () {
            document
                .querySelectorAll(".email-template-toggle")
                .forEach(function (el) {
                const inp = el;
                if (inp.dataset.guardBound === "1")
                    return;
                inp.dataset.guardBound = "1";
                inp.addEventListener("change", function (e) {
                    const url = inp.getAttribute("data-url") ?? "#";
                    if (url !== "#")
                        return;
                    e.preventDefault();
                    inp.checked = !inp.checked;
                    const msg = el.getAttribute("data-guard-msg") ?? "", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') &&
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
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    }
                    else {
                        alert(msg);
                    }
                });
            });
        });
    }
    catch (__moduleErr) {
        console.error("[emailSettings] failed to initialise:", __moduleErr);
    }
})();
//# sourceMappingURL=emailSettings.js.map