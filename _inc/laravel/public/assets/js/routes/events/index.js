/**
 * @fileoverview TypeScript version of public/assets/js/routes/events/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    try {
        const bindGuard = (el) => {
            try {
                if (!el)
                    return;
                if (el.getAttribute("data-listener-active") === "true")
                    return;
                el.setAttribute("data-listener-active", "true");
                if (!el.getAttribute("data-listener-bound-click")) {
                    el.setAttribute("data-listener-bound-click", "1");
                    el.addEventListener("click", (e) => {
                        try {
                            const href = el.getAttribute("href") ?? "#", url = el.getAttribute("data-url") ?? href ?? "#";
                            if (url !== "#" && href !== "#")
                                return;
                            e.preventDefault();
                            const msg = el.getAttribute("data-guard-msg") ??
                                "Requested route is unavailable. Please contact technical support or your domain administrator.";
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
                            el.setAttribute("data-failed-route", "true");
                        }
                        catch (err) {
                            console.error(`[index] Error:`, err);
                        }
                    });
                }
            }
            catch (err) {
                console.error(`[index] Error:`, err);
            }
        };
        const bindFormGuard = (fm) => {
            try {
                if (!fm)
                    return;
                if (fm.getAttribute("data-submit-guarded") === "true")
                    return;
                fm.setAttribute("data-submit-guarded", "true");
                if (!fm.getAttribute("data-listener-bound-submit")) {
                    fm.setAttribute("data-listener-bound-submit", "1");
                    fm.addEventListener("submit", (e) => {
                        try {
                            const action = fm.getAttribute("action") ?? "#", url = fm.getAttribute("data-url") ?? action ?? "#";
                            if (url !== "#" && action !== "#")
                                return;
                            e.preventDefault();
                            const msg = fm.getAttribute("data-guard-msg") ??
                                "Requested route is unavailable. Please contact technical support or your domain administrator.";
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
            catch (err) {
                console.error(`[index] Error:`, err);
            }
        };
        bindGuard(document.getElementById("events-create-link"));
        document
            .querySelectorAll("[id^='events-edit-title-link-']")
            .forEach(bindGuard);
        document
            .querySelectorAll("[id^='events-edit-icon-link-']")
            .forEach(bindGuard);
        document.querySelectorAll("[id^='events-delete-link-']").forEach(bindGuard);
        document
            .querySelectorAll("form[id^='events-delete-form-']")
            .forEach(bindFormGuard);
    }
    catch (err) {
        console.error(`[index] Error:`, err);
    }
})();
//# sourceMappingURL=index.js.map