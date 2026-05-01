(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/attendances/bulk/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */
(() => {
    try {
        const links = document.querySelectorAll("a.apply-bulkattendance");
        if (links.length === 0)
            return;
        links.forEach(l => {
            try {
                if (l.getAttribute("data-listener-active") === "true")
                    return;
                l.setAttribute("data-listener-active", "true");
                l.addEventListener("click", (e) => {
                    try {
                        e.preventDefault();
                        const formId = l.getAttribute("data-form-id") ?? "";
                        if (formId === "")
                            return;
                        const form = document.getElementById(formId);
                        if (!form)
                            return;
                        const action = form.getAttribute("action") ?? "#";
                        if (action !== "#") {
                            form.submit();
                            return;
                        }
                        const msg = l.getAttribute("data-guard-msg") ??
                            "Apply bulk attendance route is unavailable. Please contact technical support or your domain administrator.";
                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') &&
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
                        l.setAttribute("data-failed-route", "true");
                        form.setAttribute("data-failed-route", "true");
                    }
                    catch (err) {
                        console.error(`[apply] Error:`, err);
                    }
                });
            }
            catch (err) {
                console.error(`[apply] Error:`, err);
            }
        });
    }
    catch (err) {
        console.error(`[apply] Error:`, err);
    }
})();
})();