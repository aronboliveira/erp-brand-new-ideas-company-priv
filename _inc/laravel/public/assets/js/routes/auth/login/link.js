/**
 * @fileoverview TypeScript version of public/assets/js/routes/auth/login/link.js
 * @generated from original JavaScript - manual review recommended
 * @module link
 */
(() => {
    const el = document.getElementById("loginLink");
    if (!el || el.getAttribute("data-event-alias") === "true")
        return;
    el.setAttribute("data-event-alias", "true");
    const url = el.getAttribute("data-url"), msg = el.getAttribute("data-guard-msg");
    if (!url || url === "#") {
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className = "toast-container position-fixed top-0 end-0 p-3";
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
        return;
    }
    if (!el.getAttribute("data-listener-bound-click")) {
        el.setAttribute("data-listener-bound-click", "1");
        el.addEventListener("click", event => {
            event.preventDefault();
            try {
                window.location.href = url;
            }
            catch (e) {
                console.error(`[link] Error:`, e);
            }
        });
    }
})();
//# sourceMappingURL=link.js.map