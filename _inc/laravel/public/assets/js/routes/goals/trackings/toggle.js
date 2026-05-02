(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/goals/trackings/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */



(() => {
    const errFb = "# ERROR", dataClientLoc = "data-client-localized", dataGuardMsg = "data-guard-msg";


    function getLocalizedMessage(el, key) {
        let msg = errFb;
        if (el.getAttribute(dataClientLoc) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
        }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[key] ||
                    errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLoc, "true");
            }
        }
        return msg;

    }

    function showError(msg) {
        const bsLink = document.querySelector("link[href*='bootstrap']");
        if (bsLink && window.bootstrap.Toast) {
            const container = document.getElementById("toast-container") ??
                (() => {
                    const c = document.createElement("div");
                    c.id = "toast-container";
                    document.body.appendChild(c);
                    return c;
                })();
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
            window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
        }
        else {
            alert(msg);
        }
    }
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".toggleswitch").forEach((el) => {
            try {


                if (typeof $(el).bootstrapToggle !== "function")
                    throw new Error("bootstrapToggle missing");



                $(el).bootstrapToggle();
            }
            catch {
                const msg = getLocalizedMessage(el, "toggle_init_failed");
                el.addEventListener("click", () => {
                    showError(msg);
                }, { once: true });
            }
        });
        const starSelector = "fieldset[id^='demo'] .stars";
        const handleStarClick = (e) => {
            const tgt = e.target;
            if (!tgt?.matches(starSelector))
                return;
            try {
                alert(tgt.value);
                tgt.checked = true;
            }
            catch {
                const msg = getLocalizedMessage(tgt, "star_click_failed");
                tgt.addEventListener("pointerup", () => {
                    showError(msg);
                }, { once: true });
            }
        };
        if (!document.body.hasAttribute("data-star-listener")) {
            document.body.addEventListener("click", handleStarClick);
            document.body.setAttribute("data-star-listener", "true");
            const mo = new MutationObserver(() => {
                if (!document.querySelector(starSelector)) {
                    mo.disconnect();
                    document.body.removeEventListener("click", handleStarClick);
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        }
    });
})();
})();