(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/warehouses/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
(() => {
    try {
        const forms = document.querySelectorAll("form[data-resolved-action][data-guard-msg]");
        forms.forEach(f => {
            if (f.getAttribute("data-listener-active") === "true")
                return;
            f.setAttribute("data-listener-active", "true");
            const resolved = f.getAttribute("data-resolved-action") ?? "#";
            if ((f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
                resolved !== "#")
                f.setAttribute("action", resolved);
            f.addEventListener("submit", (e) => {
                try {
                    const action = f.getAttribute("action") ?? "#";
                    if (action && action !== "#")
                        return;
                    e.preventDefault();
                    const msg = f.getAttribute("data-guard-msg") ??
                        "Store warehouse route is unavailable. Please contact technical support or your domain administrator.";
                    let c = document.getElementById("toast-container");
                    if (!c) {
                        c = document.createElement("div");
                        c.id = "toast-container";
                        document.body.appendChild(c);
                    }
                    const ok = document.querySelector('link[href*="bootstrap"]') &&
                        window.bootstrap.Toast;
                    if (ok) {
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
                        c.appendChild(t);
                        try {
                            window.bootstrap.Toast.getOrCreateInstance(t).show();
                        }
                        catch {
                            alert(msg);
                        }
                    }
                    else {
                        alert(msg);
                    }
                    f.setAttribute("data-failed-route", "true");
                }
                catch (__err) {
                    console.error(`[store] Error:`, __err);
                }
            });
        });
    }
    catch (__err) {
        console.error(`[store] Error:`, __err);
    }
})();
})();