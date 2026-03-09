(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/projectList.js
 * @generated from original JavaScript - manual review recommended
 * @module projectList
 */
(() => {
    try {
        const anchors = document.querySelectorAll('a[id^="task-index-link-"]');
        if (anchors.length === 0)
            return;
        // eslint-disable-next-line @typescript-eslint/prefer-for-of
        for (let i = 0; i < anchors.length; i++) {
            try {
                const el = anchors[i], flag = "data-listener-active";
                if (el.hasAttribute(flag) && el.getAttribute(flag) === "true")
                    continue;
                el.setAttribute(flag, "true");
                el.addEventListener("click", function (e) {
                    try {
                        const href = el.getAttribute("href") ?? "#", url = el.getAttribute("data-url") ?? "#";
                        if (href !== "#" || url !== "#")
                            return;
                        e.preventDefault();
                        const msg = el.getAttribute("data-guard-msg") ??
                            "View project tasks route is unavailable. Please contact technical support or your domain administrator.", hasBootstrap = document.querySelector('link[href*="bootstrap"]') &&
                            window.bootstrap.Toast;
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
                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener("hidden.bs.toast", function () {
                                try {
                                    toast.remove();
                                }
                                catch (_) {
                                    console.error(`[projectList] Error:`, _);
                                }
                            });
                            inst.show();
                        }
                        else {
                            alert(msg);
                        }
                        el.setAttribute("data-failed-route", "true");
                    }
                    catch (_) {
                        console.error(`[projectList] Error:`, _);
                    }
                }, { passive: false });
            }
            catch (_) {
                console.error(`[projectList] Error:`, _);
            }
        }
    }
    catch (_) {
        console.error(`[projectList] Error:`, _);
    }
})();
})();