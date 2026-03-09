(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/clients/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
(function () {
    try {
        const f = document.getElementById("store_client");
        if (!f)
            return;
        const guardMsg = f.getAttribute("data-guard-msg") ?? "Route unavailable", actionHref = f.getAttribute("data-action-href") ?? "";
        if (!f.getAttribute("action") && actionHref && actionHref !== "#")
            f.setAttribute("action", actionHref);
        // eslint-disable-next-line no-inner-declarations
        function toastOrAlert(msg) {
            try {
                const hasBootstrap = !!window.bootstrap.Toast;
                if (!hasBootstrap) {
                    alert(msg);
                    return;
                }
                let t = document.getElementById("route-guard-toast");
                if (!t) {
                    t = document.createElement("div");
                    t.id = "route-guard-toast";
                    t.className =
                        "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                    for (const [k, v] of Object.entries({
                        role: "alert",
                        "aria-live": "assertive",
                        "aria-atomic": "true",
                    }))
                        t.setAttribute(k, v);
                    t.innerHTML =
                        '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                    document.body.appendChild(t);
                }
                const body = t.querySelector(".toast-body");
                if (body)
                    body.textContent = msg;
                new window.bootstrap.Toast(t, { delay: 4000 }).show();
            }
            catch (e) {
                alert(msg);
            }
        }
        f.addEventListener("submit", function (e) {
            try {
                const a = f.getAttribute("action") ?? "";
                if (!a || a === "#") {
                    e.preventDefault();
                    toastOrAlert(guardMsg);
                }
            }
            catch (_) {
                e.preventDefault();
                alert(guardMsg);
            }
        });
    }
    catch (_) {
        console.error(`[store] Error:`, _);
    }
})();
})();