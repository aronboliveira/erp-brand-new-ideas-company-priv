/**
 * @fileoverview TypeScript version of public/assets/js/routes/formBuilders/fields.js
 * @generated from original JavaScript - manual review recommended
 * @module fields
 */
(() => {
    try {
        const toast = (msg) => {
            const text = msg ??
                "Requested route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
            let container = document.getElementById("toast-container");
            if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className = "toast-container position-fixed top-0 end-0 p-3";
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
                b.textContent = text;
                t.appendChild(b);
                container.appendChild(t);
                bootstrap.Toast.getOrCreateInstance(t).show();
            }
            else {
                alert(text);
            }
        };
        const bindGuard = (el) => {
            if (!el || el.getAttribute("data-listener-active") === "true")
                return;
            el.setAttribute("data-listener-active", "true");
            if (!el.getAttribute("data-listener-bound-click")) {
                el.setAttribute("data-listener-bound-click", "1");
                el.addEventListener("click", (e) => {
                    try {
                        const href = (el.getAttribute("href") ?? "#").trim(), url = (el.getAttribute("data-url") ?? href ?? "#").trim();
                        if (url !== "#" && href !== "#")
                            return;
                        e.preventDefault();
                        toast(el.getAttribute("data-guard-msg") ?? "");
                        el.setAttribute("data-failed-route", "true");
                    }
                    catch (_) {
                        console.error(`[fields] Error:`, _);
                    }
                });
            }
        };
        document
            .querySelectorAll("a[data-guard-msg], a[data-url]")
            .forEach(el => bindGuard(el));
        try {
            const els = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            els.forEach((el) => {
                try {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                }
                catch (_) {
                    console.error(`[fields] Error:`, _);
                }
            });
        }
        catch (_) {
            console.error(`[fields] Error:`, _);
        }
    }
    catch (_) {
        console.error(`[fields] Error:`, _);
    }
})();
//# sourceMappingURL=fields.js.map