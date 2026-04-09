/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/exportImport.js
 * @generated from original JavaScript - manual review recommended
 * @module exportImport
 */
(() => {
    try {
        const bindGuard = (id) => {
            try {
                const el = document.getElementById(id);
                if (!el)
                    return;
                if (el.getAttribute("data-listener-active") === "true")
                    return;
                el.setAttribute("data-listener-active", "true");
                if (!el.getAttribute("data-listener-bound-click")) {
                    el.setAttribute("data-listener-bound-click", "1");
                    el.addEventListener("click", (e) => {
                        try {
                            const href = el.getAttribute("href") ?? "#", url = el.getAttribute("data-url") ?? "#";
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
                            console.error(`[exportImport] Error:`, err);
                        }
                    });
                }
            }
            catch (err) {
                console.error(`[exportImport] Error:`, err);
            }
        };
        bindGuard("vendor-import");
        bindGuard("vendor-export");
    }
    catch (err) {
        console.error(`[exportImport] Error:`, err);
    }
})();
//# sourceMappingURL=exportImport.js.map