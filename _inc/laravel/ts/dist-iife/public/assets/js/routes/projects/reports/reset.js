(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/reports/reset.js
 * @generated from original JavaScript - manual review recommended
 * @module reset
 */
(() => {
    try {
        const form = document.getElementById("project_report_submit"), formAlias = "data-listening-projectreportsubmit";
        if (form && !form.hasAttribute(formAlias)) {
            form.setAttribute(formAlias, "true");
            if (!form.getAttribute("data-listener-bound-submit")) {
                form.setAttribute("data-listener-bound-submit", "1");
                form.addEventListener("submit", event => {
                    try {
                        const url = form.getAttribute("data-url");
                        if (url !== "#" || form.action !== "#")
                            return;
                        event.preventDefault();
                        const msg = form.getAttribute("data-guard-msg") ??
                            "Project report index route is unavailable. Please contact technical support or your domain administrator.";
                        const hasBS = Array.from(document.scripts).some(s => s.src.includes("bootstrap.min.js") &&
                            window.bootstrap &&
                            typeof window.bootstrap.Toast === "function");
                        if (hasBS) {
                            const container = document.getElementById("toast-container") ??
                                (() => {
                                    const c = document.createElement("div");
                                    c.id = "toast-container";
                                    c.className =
                                        "toast-container position-fixed bottom-0 end-0 p-3";
                                    document.body.appendChild(c);
                                    return c;
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
                    }
                    catch (__err) {
                        console.error(`[reset] Error:`, __err);
                    }
                });
            }
        }
    }
    catch (__err) {
        console.error(`[reset] Error:`, __err);
    }
    try {
        const selector = ".reset-project-report-link", alias = "data-listening-resetprojectreportclick";
        document.querySelectorAll(selector).forEach((el) => {
            try {
                if (!el.hasAttribute(alias)) {
                    el.setAttribute(alias, "true");
                    if (!el.getAttribute("data-listener-bound-click")) {
                        el.setAttribute("data-listener-bound-click", "1");
                        el.addEventListener("click", event => {
                            try {
                                if (el.getAttribute("data-url") !== "#" ||
                                    el.href !== "#")
                                    return;
                                event.preventDefault();
                                const msg = el.getAttribute("data-guard-msg") ??
                                    "Project report index route is unavailable. Please contact technical support or your domain administrator.";
                                const hasBS = Array.from(document.scripts).some(s => s.src.includes("bootstrap.min.js") &&
                                    window.bootstrap &&
                                    typeof window.bootstrap.Toast === "function");
                                if (hasBS) {
                                    const container = document.getElementById("toast-container") ??
                                        (() => {
                                            const c = document.createElement("div");
                                            c.id = "toast-container";
                                            c.className =
                                                "toast-container position-fixed bottom-0 end-0 p-3";
                                            document.body.appendChild(c);
                                            return c;
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
                            }
                            catch (__err) {
                                console.error(`[reset] Error:`, __err);
                            }
                        });
                    }
                }
            }
            catch (__err) {
                console.error(`[reset] Error:`, __err);
            }
        });
    }
    catch (__err) {
        console.error(`[reset] Error:`, __err);
    }
})();
})();