/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
(() => {
    try {
        const formEl = document.getElementById("customers-store-form");
        if (!formEl)
            return;
        if (formEl.getAttribute("data-listener-active") === "true")
            return;
        formEl.setAttribute("data-listener-active", "true");
        if (!formEl.getAttribute("data-listener-bound-submit")) {
            formEl.setAttribute("data-listener-bound-submit", "1");
            formEl.addEventListener("submit", (e) => {
                try {
                    const action = formEl.getAttribute("action") ?? "#", url = formEl.getAttribute("data-url") ?? "#";
                    if (url !== "#" && action !== "#")
                        return;
                    e.preventDefault();
                    const msg = formEl.getAttribute("data-guard-msg") ??
                        "Store customer route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') &&
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
                    formEl.setAttribute("data-failed-route", "true");
                }
                catch (err) {
                    console.error(`[store] Error:`, err);
                }
            });
        }
    }
    catch (err) {
        console.error(`[store] Error:`, err);
    }
})();
//# sourceMappingURL=store.js.map