(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    try {
        const fm = document.getElementById("product-service-filter-form");
        if (!fm)
            return;
        // Guard form submission (e.g., user presses Enter)
        if (fm.getAttribute("data-submit-guarded") !== "true") {
            fm.setAttribute("data-submit-guarded", "true");
            if (!fm.getAttribute("data-listener-bound-submit")) {
                fm.setAttribute("data-listener-bound-submit", "1");
                fm.addEventListener("submit", (e) => {
                    try {
                        const action = (fm.getAttribute("action") ?? "#").trim(), url = (fm.getAttribute("data-url") ?? "#").trim();
                        if (url !== "#" && action !== "#")
                            return;
                        e.preventDefault();
                        const msg = fm.getAttribute("data-guard-msg") ??
                            "Product & Service index route is unavailable. Please contact technical support or your domain administrator.";
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
                            const t = document.createElement("div");
                            t.className = "toast";
                            for (const [k, v] of Object.entries({
                                role: "alert",
                                "aria-live": "assertive",
                                "aria-atomic": "true",
                            }))
                                t.setAttribute(k, v);
                            const b = document.createElement("div");
                            b.className = "toast-body";
                            b.textContent = msg;
                            t.appendChild(b);
                            container.appendChild(t);
                            bootstrap.Toast.getOrCreateInstance(t).show();
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
        // Apply button (submit)
        const applyBtn = document.getElementById("product-service-apply-btn");
        if (applyBtn && applyBtn.getAttribute("data-listener-active") !== "true") {
            applyBtn.setAttribute("data-listener-active", "true");
            if (!applyBtn.getAttribute("data-listener-bound-click")) {
                applyBtn.setAttribute("data-listener-bound-click", "1");
                applyBtn.addEventListener("click", (e) => {
                    try {
                        const href = (applyBtn.getAttribute("href") ?? "#").trim(), url = (applyBtn.getAttribute("data-url") ?? "#").trim();
                        if (url === "#" || href === "#") {
                            e.preventDefault();
                            const msg = applyBtn.getAttribute("data-guard-msg") ??
                                "Product & Service index route is unavailable. Please contact technical support or your domain administrator.";
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
                                const t = document.createElement("div");
                                t.className = "toast";
                                for (const [k, v] of Object.entries({
                                    role: "alert",
                                    "aria-live": "assertive",
                                    "aria-atomic": "true",
                                }))
                                    t.setAttribute(k, v);
                                const b = document.createElement("div");
                                b.className = "toast-body";
                                b.textContent = msg;
                                t.appendChild(b);
                                container.appendChild(t);
                                bootstrap.Toast.getOrCreateInstance(t).show();
                            }
                            else {
                                alert(msg);
                            }
                            applyBtn.setAttribute("data-failed-route", "true");
                            return;
                        }
                        e.preventDefault();
                        if (fm && typeof fm.submit === "function")
                            fm.submit();
                    }
                    catch (err) {
                        console.error(`[index] Error:`, err);
                    }
                });
            }
        }
        // Reset link (navigate to index)
        const resetLink = document.getElementById("product-service-reset-link");
        if (resetLink &&
            resetLink.getAttribute("data-listener-active") !== "true") {
            resetLink.setAttribute("data-listener-active", "true");
            if (!resetLink.getAttribute("data-listener-bound-click")) {
                resetLink.setAttribute("data-listener-bound-click", "1");
                resetLink.addEventListener("click", (e) => {
                    try {
                        const href = (resetLink.getAttribute("href") ?? "#").trim(), url = (resetLink.getAttribute("data-url") ?? href ?? "#").trim();
                        if (url !== "#" && href !== "#")
                            return;
                        e.preventDefault();
                        const msg = resetLink.getAttribute("data-guard-msg") ??
                            "Product & Service index route is unavailable. Please contact technical support or your domain administrator.";
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
                            const t = document.createElement("div");
                            t.className = "toast";
                            for (const [k, v] of Object.entries({
                                role: "alert",
                                "aria-live": "assertive",
                                "aria-atomic": "true",
                            }))
                                t.setAttribute(k, v);
                            const b = document.createElement("div");
                            b.className = "toast-body";
                            b.textContent = msg;
                            t.appendChild(b);
                            container.appendChild(t);
                            bootstrap.Toast.getOrCreateInstance(t).show();
                        }
                        else {
                            alert(msg);
                        }
                        resetLink.setAttribute("data-failed-route", "true");
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