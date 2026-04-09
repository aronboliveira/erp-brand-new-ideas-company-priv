/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/trials/balance/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    try {
        const form = document.getElementById("report_trial_balance");
        if (form) {
            const submitFlag = "data-submit-listener";
            if (!(form.hasAttribute(submitFlag) &&
                form.getAttribute(submitFlag) === "true")) {
                form.setAttribute(submitFlag, "true");
                form.addEventListener("submit", function (e) {
                    try {
                        const action = form.getAttribute("action") ?? "#", url = form.getAttribute("data-url") ?? "#";
                        if (action !== "#" && url !== "#")
                            return;
                        e.preventDefault();
                        const msg = form.getAttribute("data-guard-msg") ??
                            "Trial balance report route is unavailable. Please contact technical support or your domain administrator.", linkEl = document.querySelector('link[href*="bootstrap"]'), hasBootstrapToast = window.bootstrap &&
                            typeof window.bootstrap.Toast === "function";
                        let container = document.getElementById("toast-container");
                        if (!container) {
                            container = document.createElement("div");
                            container.id = "toast-container";
                            container.className =
                                "toast-container position-fixed top-0 end-0 p-3";
                            container.style.zIndex = "1080";
                            container.className = "position-fixed top-0 end-0 p-3";
                            document.body.appendChild(container);
                        }
                        if (linkEl && hasBootstrapToast) {
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
                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener("hidden.bs.toast", function () {
                                try {
                                    toast.remove();
                                }
                                catch (_) {
                                    console.error(`[index] Error:`, _);
                                }
                            });
                            inst.show();
                        }
                        else {
                            alert(msg);
                        }
                        form.setAttribute("data-failed-route", "true");
                    }
                    catch (_) {
                        console.error(`[index] Error:`, _);
                    }
                }, { passive: false });
            }
        }
        const apply = document.getElementById("trial-balance-apply");
        if (apply) {
            const clickFlag = "data-click-listener";
            if (!(apply.hasAttribute(clickFlag) &&
                apply.getAttribute(clickFlag) === "true")) {
                apply.setAttribute(clickFlag, "true");
                apply.addEventListener("click", function (e) {
                    try {
                        e.preventDefault();
                        const targetId = apply.getAttribute("data-target-form") ?? "", f = targetId ? document.getElementById(targetId) : null;
                        if (!f)
                            return;
                        const action = f.getAttribute("action") ?? "#", url = f.getAttribute("data-url") ?? "#";
                        if (action === "#" || url === "#") {
                            const msg = apply.getAttribute("data-guard-msg") ??
                                f.getAttribute("data-guard-msg") ??
                                "Trial balance report route is unavailable. Please contact technical support or your domain administrator.", linkEl = document.querySelector('link[href*="bootstrap"]'), hasBootstrapToast = window.bootstrap &&
                                typeof window.bootstrap.Toast === "function";
                            let container = document.getElementById("toast-container");
                            if (!container) {
                                container = document.createElement("div");
                                container.id = "toast-container";
                                container.className =
                                    "toast-container position-fixed top-0 end-0 p-3";
                                container.style.zIndex = "1080";
                                container.className = "position-fixed top-0 end-0 p-3";
                                document.body.appendChild(container);
                            }
                            if (linkEl && hasBootstrapToast) {
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
                                const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                toast.addEventListener("hidden.bs.toast", function () {
                                    try {
                                        toast.remove();
                                    }
                                    catch (_) {
                                        console.error(`[index] Error:`, _);
                                    }
                                });
                                inst.show();
                            }
                            else {
                                alert(msg);
                            }
                            apply.setAttribute("data-failed-route", "true");
                            f.setAttribute("data-failed-route", "true");
                            return;
                        }
                        f.submit();
                    }
                    catch (_) {
                        console.error(`[index] Error:`, _);
                    }
                }, { passive: false });
            }
        }
        const host = document.documentElement;
        if (host) {
            const resetFlag = "data-trial-balance-reset-listener";
            if (!(host.hasAttribute(resetFlag) &&
                host.getAttribute(resetFlag) === "true")) {
                host.setAttribute(resetFlag, "true");
                document.addEventListener("click", function (e) {
                    try {
                        const a = e.target &&
                            (e.target.closest
                                ? e.target.closest("a.trial-balance-reset")
                                : null);
                        if (!a)
                            return;
                        const href = a.getAttribute("href") ?? "#", url = (a.getAttribute("data-url") || href) ?? "#";
                        if (href !== "#" || url !== "#")
                            return;
                        e.preventDefault();
                        const msg = a.getAttribute("data-guard-msg") ||
                            (form ? form.getAttribute("data-guard-msg") : "") ||
                            "Trial balance report route is unavailable. Please contact technical support or your domain administrator.", linkEl = document.querySelector('link[href*="bootstrap"]'), hasBootstrapToast = window.bootstrap &&
                            typeof window.bootstrap.Toast === "function";
                        let container = document.getElementById("toast-container");
                        if (!container) {
                            container = document.createElement("div");
                            container.id = "toast-container";
                            container.className =
                                "toast-container position-fixed top-0 end-0 p-3";
                            container.style.zIndex = "1080";
                            container.className = "position-fixed top-0 end-0 p-3";
                            document.body.appendChild(container);
                        }
                        if (linkEl && hasBootstrapToast) {
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
                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener("hidden.bs.toast", function () {
                                try {
                                    toast.remove();
                                }
                                catch (_) {
                                    console.error(`[index] Error:`, _);
                                }
                            });
                            inst.show();
                        }
                        else {
                            alert(msg);
                        }
                        a.setAttribute("data-failed-route", "true");
                    }
                    catch (_) {
                        console.error(`[index] Error:`, _);
                    }
                }, { passive: false });
            }
        }
    }
    catch (_) {
        console.error(`[index] Error:`, _);
    }
})();
//# sourceMappingURL=index.js.map