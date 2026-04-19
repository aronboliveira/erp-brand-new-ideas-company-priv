/**
 * @fileoverview TypeScript version of public/assets/js/routes/formBuilders/responses.js
 * @generated from original JavaScript - manual review recommended
 * @module responses
 */
(() => {
    try {
        const showGuard = (msg) => {
            const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
            let container = document.getElementById("toast-container");
            if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className = "toast-container position-fixed top-0 end-0 p-3";
                container.style.zIndex = "1080";
                document.body.appendChild(container);
            }
            const text = msg ??
                "Requested route is unavailable. Please contact technical support or your domain administrator.";
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
        const bindGuard = (a) => {
            if (!a || a.getAttribute("data-listener-active") === "true")
                return;
            a.setAttribute("data-listener-active", "true");
            a.addEventListener("click", (e) => {
                try {
                    const href = (a.getAttribute("href") ?? "#").trim(), url = (a.getAttribute("data-url") ?? href ?? "#").trim();
                    if (url !== "#" && href !== "#")
                        return;
                    e.preventDefault();
                    const msg = a.getAttribute("data-guard-msg") ?? "";
                    showGuard(msg);
                    a.setAttribute("data-failed-route", "true");
                }
                catch (err) {
                    console.error(`[responses] Error:`, err);
                }
            });
        };
        document
            .querySelectorAll("a[data-guard-msg], a[data-url]")
            .forEach(bindGuard);
        try {
            const els = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            els.forEach((el) => {
                try {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                }
                catch (e) {
                    console.error(`[responses] Error:`, e);
                }
            });
        }
        catch (err) {
            console.error(`[responses] Error:`, err);
        }
    }
    catch (err) {
        console.error(`[responses] Error:`, err);
    }
})();
//# sourceMappingURL=responses.js.map