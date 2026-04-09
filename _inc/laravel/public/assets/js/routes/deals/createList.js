/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/createList.js
 * @generated from original JavaScript - manual review recommended
 * @module createList
 */
(() => {
    const btn = document.getElementById("deal-create-btn");
    if (!btn || btn.getAttribute("data-listener-active") === "true")
        return;
    btn.setAttribute("data-listener-active", "true");
    if (!btn.getAttribute("data-listener-bound-click")) {
        btn.setAttribute("data-listener-bound-click", "1");
        btn.addEventListener("click", (e) => {
            try {
                const url = btn.getAttribute("data-url") ?? "#";
                if (url !== "#")
                    return;
                e.preventDefault();
                const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR", bs = document.querySelector('link[href*="bootstrap"]') &&
                    window.bootstrap;
                let container = document.getElementById("toast-container");
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
                if (bs) {
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
                btn.setAttribute("data-failed-route", "true");
            }
            catch (__err) {
                console.error(`[createList] Error:`, __err);
            }
        });
    }
})();
//# sourceMappingURL=createList.js.map