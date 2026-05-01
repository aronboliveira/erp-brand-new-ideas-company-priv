(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/bank/transfers/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    try {
        const fm = document.getElementById("transfer_form");
        if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
            fm.setAttribute("data-submit-guarded", "true");
            if (!fm.getAttribute("data-listener-bound-submit")) {
                fm.setAttribute("data-listener-bound-submit", "1");
                fm.addEventListener("submit", (e) => {
                    try {
                        const action = fm.getAttribute("action") ?? "#", url = fm.getAttribute("data-url") ?? "#";
                        if (url !== "#" && action !== "#")
                            return;
                        e.preventDefault();
                        const msg = fm.getAttribute("data-guard-msg") ??
                            "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.";
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
                        fm.setAttribute("data-failed-route", "true");
                    }
                    catch (err) {
                        console.error(`[index] Error:`, err);
                    }
                });
            }
        }
        const apply = document.getElementById("transfer-apply");
        if (apply && apply.getAttribute("data-listener-active") !== "true") {
            apply.setAttribute("data-listener-active", "true");
            if (!apply.getAttribute("data-listener-bound-click")) {
                apply.setAttribute("data-listener-bound-click", "1");
                apply.addEventListener("click", (e) => {
                    try {
                        e.preventDefault();
                        const fid = apply.getAttribute("data-form-id") ?? "";
                        if (fid === "")
                            return;
                        const form = document.getElementById(fid);
                        if (!form)
                            return;
                        const action = form.getAttribute("action") ?? "#", url = form.getAttribute("data-url") ?? "#";
                        if (url === "#" || action === "#") {
                            const msg = apply.getAttribute("data-guard-msg") ??
                                "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.";
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
                            apply.setAttribute("data-failed-route", "true");
                            form.setAttribute("data-failed-route", "true");
                            return;
                        }
                        form.submit();
                    }
                    catch (err) {
                        console.error(`[index] Error:`, err);
                    }
                });
            }
        }
        const reset = document.getElementById("transfer-reset");
        if (reset && reset.getAttribute("data-listener-active") !== "true") {
            reset.setAttribute("data-listener-active", "true");
            if (!reset.getAttribute("data-listener-bound-click")) {
                reset.setAttribute("data-listener-bound-click", "1");
                reset.addEventListener("click", (e) => {
                    try {
                        const href = reset.getAttribute("href") ?? "#", url = reset.getAttribute("data-url") ?? "#";
                        if (url !== "#" && href !== "#")
                            return;
                        e.preventDefault();
                        const msg = reset.getAttribute("data-guard-msg") ??
                            "Reset bank transfer route is unavailable. Please contact technical support or your domain administrator.";
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
                        reset.setAttribute("data-failed-route", "true");
                    }
                    catch (err) {
                        console.error(`[index] Error:`, err);
                    }
                });
            }
        }
    }
    catch (err) {
        console.error(`[index] Error:`, err);
    }
})();
})();