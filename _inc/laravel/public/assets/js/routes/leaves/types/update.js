/**
 * @fileoverview TypeScript version of public/assets/js/routes/leaves/types/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
(() => {
    "use strict";
    const form = document.getElementById("leaveType-edit-form");
    if (!form)
        return;
    const guardMsg = form.getAttribute("data-guard-msg") ?? "Update route is unavailable.", submitBtn = form.querySelector('input[type="submit"]'), titleEl = form.querySelector("#title"), daysEl = form.querySelector("#days");
    const actionIsBlocked = () => {
        const act = form.getAttribute("action") ?? "#";
        return !act || act === "#";
    };
    const showErr = (msg) => {
        try {
            alert(msg);
        }
        catch {
            /* no-op */
        }
    };
    const validate = () => {
        if (titleEl && !titleEl.value.trim()) {
            titleEl.focus();
            return false;
        }
        if (daysEl) {
            const v = Number(daysEl.value);
            if (Number.isNaN(v) || v < 0) {
                daysEl.focus();
                return false;
            }
        }
        return true;
    };
    if (actionIsBlocked() && submitBtn)
        if (!submitBtn.getAttribute("data-listener-bound-click")) {
            submitBtn.setAttribute("data-listener-bound-click", "1");
            submitBtn.addEventListener("click", (e) => {
                e.preventDefault();
                showErr(guardMsg);
            });
        }
    if (!form.getAttribute("data-listener-bound-submit")) {
        form.setAttribute("data-listener-bound-submit", "1");
        form.addEventListener("submit", (e) => {
            if (actionIsBlocked()) {
                e.preventDefault();
                showErr(guardMsg);
                return;
            }
            if (!validate())
                e.preventDefault();
        });
    }
})();
//# sourceMappingURL=update.js.map