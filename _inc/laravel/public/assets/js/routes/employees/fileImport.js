/**
 * @fileoverview TypeScript version of public/assets/js/routes/employees/fileImport.js
 * @generated from original JavaScript - manual review recommended
 * @module fileImport
 */
(() => {
    try {
        const el = document.getElementById("employee-import-btn");
        if (!el)
            return;
        if (el.getAttribute("data-listener-active") === "true")
            return;
        el.setAttribute("data-listener-active", "true");
        if (!el.getAttribute("data-listener-bound-click")) {
            el.setAttribute("data-listener-bound-click", "1");
            el.addEventListener("click", (e) => {
                try {
                    const href = (el.getAttribute("href") ?? "#").trim(), url = (el.getAttribute("data-url") ?? "#").trim();
                    if (url !== "#" && href !== "#")
                        return;
                    e.preventDefault();
                    const msg = el.getAttribute("data-guard-msg") ??
                        "Import employee route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') &&
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
                    el.setAttribute("data-failed-route", "true");
                }
                catch (err) {
                    console.error(`[fileImport] Error:`, err);
                }
            });
        }
    }
    catch (err) {
        console.error(`[fileImport] Error:`, err);
    }
})();
//# sourceMappingURL=fileImport.js.map