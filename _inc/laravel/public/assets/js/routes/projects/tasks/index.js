/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(() => {
    try {
        const links = document.querySelectorAll(".project-task-index-link");
        if (!links || links.length === 0)
            return;
        links.forEach(l => {
            try {
                if (!l || l.getAttribute("data-listener-active") === "true")
                    return;
                l.setAttribute("data-listener-active", "true");
                const url = l.getAttribute("data-url") ?? "#";
                if (l.hasAttribute("href") &&
                    (l.getAttribute("href") === "#" || !l.getAttribute("href")) &&
                    url !== "#")
                    l.setAttribute("href", url);
                l.addEventListener("click", (e) => {
                    try {
                        const href = l.getAttribute("href") ?? "#";
                        if (href && href !== "#")
                            return;
                        e.preventDefault();
                        const msg = l.getAttribute("data-guard-msg") ??
                            "Show project task route is unavailable. Please contact technical support or your domain administrator.";
                        let container = document.getElementById("toast-container");
                        if (!container) {
                            container = document.createElement("div");
                            container.id = "toast-container";
                            container.className =
                                "toast-container position-fixed top-0 end-0 p-3";
                            container.style.zIndex = "1080";
                            document.body.appendChild(container);
                        }
                        const bsLink = document.querySelector('link[href*="bootstrap"]');
                        if (bsLink && window.bootstrap.Toast) {
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
                            try {
                                window.bootstrap.Toast.getOrCreateInstance(toast).show();
                            }
                            catch (err) {
                                if (window.location.hostname === "localhost" ||
                                    window.location.hostname === "127.0.0.1")
                                    console.error("[assets/js/routes/projects/tasks/index.js] Bootstrap toast instantiation error:", 
                                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                                    err?.constructor?.name ?? "Error", 
                                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                                    err?.message ?? "Unknown error");
                                alert(msg);
                            }
                        }
                        else {
                            alert(msg);
                        }
                        l.setAttribute("data-failed-route", "true");
                    }
                    catch (err) {
                        if (window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1")
                            console.error("[assets/js/routes/projects/tasks/index.js] Click handler error:", 
                            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                            err?.constructor?.name ?? "Error", 
                            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                            err?.message ?? "Unknown error");
                    }
                });
            }
            catch (err) {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("[assets/js/routes/projects/tasks/index.js] Link binding error:", 
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                    err?.constructor?.name ?? "Error", 
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                    err?.message ?? "Unknown error");
            }
        });
    }
    catch (error) {
        if (window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1")
            console.error("[assets/js/routes/projects/tasks/index.js] Initialization error:", 
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            error?.constructor?.name ?? "Error", 
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            error?.message ?? "Unknown error");
    }
})();
//# sourceMappingURL=index.js.map