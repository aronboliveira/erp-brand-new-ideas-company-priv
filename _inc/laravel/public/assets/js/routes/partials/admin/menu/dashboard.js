/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/menu/dashboard.js
 * @generated from original JavaScript - manual review recommended
 * @module dashboard
 */
(() => {
    const listenerAttr = "data-dashboard-listener-active", el = document.getElementById("dashboard-link");
    if (!el || el.getAttribute(listenerAttr) === "true")
        return;
    el.setAttribute(listenerAttr, "true");
    if (!el.getAttribute("data-listener-bound-click")) {
        el.setAttribute("data-listener-bound-click", "1");
        el.addEventListener("click", event => {
            try {
                const url = el.getAttribute("data-url"), href = el.href
                    .replace(window.location.origin, "")
                    .replace(window.location.pathname, "");
                if ((!url || url === "#") && (!href || href === "#")) {
                    event.preventDefault();
                    const message = el.getAttribute("data-guard-msg") ?? "# ERROR", bootstrapLink = document.querySelector('link[href*="bootstrap"]'), containerId = "toast-container";
                    let container = document.getElementById(containerId);
                    if (!container) {
                        container = document.createElement("div");
                        container.id = containerId;
                        document.body.appendChild(container);
                    }
                    if (bootstrapLink && window.bootstrap) {
                        const toastEl = document.createElement("div");
                        toastEl.className = "toast";
                        for (const [k, v] of Object.entries({
                            role: "alert",
                            "aria-live": "assertive",
                            "aria-atomic": "true",
                        }))
                            toastEl.setAttribute(k, v);
                        const body = document.createElement("div");
                        body.className = "toast-body";
                        body.textContent = message;
                        toastEl.appendChild(body);
                        container.appendChild(toastEl);
                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                    }
                    else {
                        alert(message);
                    }
                    el.setAttribute("data-failed-route", "true");
                }
            }
            catch (error) {
                console.error(`[dashboard] Error:`, error);
            }
        });
    }
    const observer = new MutationObserver(() => {
        if (!document.body.contains(el)) {
            observer.disconnect();
            el.removeEventListener("click", () => { });
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();
//# sourceMappingURL=dashboard.js.map