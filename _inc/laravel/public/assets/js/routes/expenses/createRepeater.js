/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/createRepeater.js
 * @generated from original JavaScript - manual review recommended
 * @module createRepeater
 */
(() => {
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", langSessionKey = "erp-np-lang";
    let errorMessage = "";
    const getLocalizedMessage = (msgKey, el) => {
        let msg = errFb;
        if (el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
        }
        else {
            let lang = (window.sessionStorage.getItem(langSessionKey) ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const showError = (message) => {
        try {
            const bsLink = document.querySelector('link[href*="bootstrap"]');
            let container = document.getElementById("toast-container");
            if (bsLink && window.bootstrap) {
                if (!container) {
                    container = document.createElement("div");
                    container.id = "toast-container";
                    container.className =
                        "toast-container position-fixed top-0 end-0 p-3";
                    container.style.zIndex = "1080";
                    document.body.appendChild(container);
                }
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
                body.textContent = message;
                toastEl.appendChild(body);
                container.appendChild(toastEl);
                window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
            }
            else {
                alert(message);
            }
        }
        catch {
            alert(message);
        }
    };
    const onPointerUp = () => {
        if (errorMessage !== "") {
            showError(errorMessage);
            errorMessage = "";
        }
    };
    document.addEventListener("pointerup", onPointerUp);
    document.querySelectorAll("[data-repeater-delete]").forEach((el) => {
        if (el.getAttribute("data-guard-listener-active") === "true")
            return;
        el.setAttribute("data-guard-listener-active", "true");
        if (!el.getAttribute("data-listener-bound-click")) {
            el.setAttribute("data-listener-bound-click", "1");
            el.addEventListener("click", () => {
                try {
                    $(".price").change();
                    $(".discount").change();
                }
                catch {
                    errorMessage = getLocalizedMessage("repeater_delete_failed", el);
                }
            });
        }
    });
    new MutationObserver((muts, obs) => {
        muts.forEach(m => {
            m.removedNodes.forEach(n => {
                if (n === document.documentElement) {
                    document.removeEventListener("pointerup", onPointerUp);
                    obs.disconnect();
                }
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
})();
//# sourceMappingURL=createRepeater.js.map