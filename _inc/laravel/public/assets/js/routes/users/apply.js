/**
 * @fileoverview TypeScript version of public/assets/js/routes/users/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */
(() => {
    try {
        const f = document.getElementById("user_userlog");
        if (!f || f.getAttribute("data-listener-active") === "true")
            return;
        f.setAttribute("data-listener-active", "true");
        const resolved = f.getAttribute("data-resolved-action") ?? "#";
        if ((f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
            resolved !== "#")
            f.setAttribute("action", resolved);
        const apply = document.getElementById("userlog-apply-btn");
        if (!apply)
            return;
        if (apply.getAttribute("data-listener-active") !== "true") {
            apply.setAttribute("data-listener-active", "true");
            if (!apply.getAttribute("data-listener-bound-click")) {
                apply.setAttribute("data-listener-bound-click", "1");
                apply.addEventListener("click", (e) => {
                    e.preventDefault();
                    const action = f.getAttribute("action") ?? "#";
                    if (action && action !== "#") {
                        f.submit();
                        return;
                    }
                    const msg = f.getAttribute("data-guard-msg") ??
                        "User logs route is unavailable. Please contact technical support or your domain administrator.";
                    let c = document.getElementById("toast-container");
                    if (!c) {
                        c = document.createElement("div");
                        c.id = "toast-container";
                        document.body.appendChild(c);
                    }
                    const hasBS = document.querySelector('link[href*="bootstrap"]') &&
                        window.bootstrap.Toast;
                    if (hasBS) {
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
                });
            }
        }
    }
    catch (__err) {
        console.error(`[apply] Error:`, __err);
    }
})();
//# sourceMappingURL=apply.js.map