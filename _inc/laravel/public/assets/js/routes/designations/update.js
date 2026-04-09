/**
 * @fileoverview TypeScript version of public/assets/js/routes/designations/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
(() => {
    try {
        const forms = Array.from(document.querySelectorAll('form[id^="designation-update-form-"][data-url][data-guard-msg]'));
        if (forms.length === 0)
            return;
        forms.forEach(fm => {
            if (fm.getAttribute("data-submit-guarded") === "true")
                return;
            fm.setAttribute("data-submit-guarded", "true");
            fm.addEventListener("submit", (e) => {
                try {
                    const action = (fm.getAttribute("action") ?? "#").trim(), url = (fm.getAttribute("data-url") ?? "#").trim();
                    if (url !== "#" && action !== "#")
                        return;
                    e.preventDefault();
                    const msg = fm.getAttribute("data-guard-msg") ??
                        "Update designation route is unavailable. Please contact technical support or your domain administrator.";
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
                catch (__err) {
                    console.error(`[update] Error:`, __err);
                }
            });
        });
    }
    catch (__err) {
        console.error(`[update] Error:`, __err);
    }
})();
//# sourceMappingURL=update.js.map