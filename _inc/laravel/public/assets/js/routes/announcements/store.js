/**
 * @fileoverview TypeScript version of public/assets/js/routes/announcements/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
(() => {
    const ids = ["announcement-ai-generate-link", "announcement-store-form"], flagAttr = "data-listener-active";
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (!el || el.getAttribute(flagAttr) === "true")
            return;
        el.setAttribute(flagAttr, "true");
        if (el.tagName === "FORM") {
            el.addEventListener("submit", event => {
                try {
                    const url = el.getAttribute("data-url"), action = el.getAttribute("action");
                    if ((!action || action === "#") && (!url || url === "#")) {
                        event.preventDefault();
                        const msg = el.getAttribute("data-guard-msg") ?? "# ERROR", bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container = document.getElementById("toast-container");
                        if (!container) {
                            container = document.createElement("div");
                            container.id = "toast-container";
                            container.className =
                                "toast-container position-fixed top-0 end-0 p-3";
                            container.style.zIndex = "1080";
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
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        }
                        else {
                            alert(msg);
                        }
                    }
                }
                catch (__err) {
                    console.error(`[store] Error:`, __err);
                }
            });
        }
        else {
            if (!el.getAttribute("data-listener-bound-click")) {
                el.setAttribute("data-listener-bound-click", "1");
                el.addEventListener("click", event => {
                    try {
                        const url = el.getAttribute("data-url"), href = el.href
                            .replace(window.location.origin, "")
                            .replace(window.location.pathname, "");
                        if ((!url || url === "#") && (!href || href === "#")) {
                            event.preventDefault();
                            const msg = el.getAttribute("data-guard-msg") ?? "# ERROR", bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                            let container = document.getElementById("toast-container");
                            if (!container) {
                                container = document.createElement("div");
                                container.id = "toast-container";
                                container.className =
                                    "toast-container position-fixed top-0 end-0 p-3";
                                container.style.zIndex = "1080";
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
                                body.textContent = msg;
                                toastEl.appendChild(body);
                                container.appendChild(toastEl);
                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                            }
                            else {
                                alert(msg);
                            }
                            el.setAttribute("data-failed-route", "true");
                        }
                    }
                    catch (__err) {
                        console.error(`[store] Error:`, __err);
                    }
                });
            }
        }
        const observer = new MutationObserver(() => {
            if (!document.getElementById(id))
                observer.disconnect();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
//# sourceMappingURL=store.js.map