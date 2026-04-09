/**
 * @fileoverview TypeScript version of public/assets/js/routes/interviewSchedules/storeSelect.js
 * @generated from original JavaScript - manual review recommended
 * @module storeSelect
 */
(() => {
    const dataListenerAdded = "data-listener-added", errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg";
    const getLocalizedMessage = (el, msgKey) => {
        let msg = errFb;
        if (el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true") {
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
                window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    try {
        if (!window.jQuery) {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("jQuery is not available");
            return;
        }
        const candidate = jQuery("select#candidate"), el = candidate.get(0);
        if (!el) {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("Select#candidate element not found");
            return;
        }
        const url = el.getAttribute("data-url"), href = el.href
            .replace(window.location.origin, "")
            .replace(window.location.pathname, "") ?? "";
        if ((!url || url === "#") && (!href || href === "#"))
            return;
        const candidateVal = String(candidate.val() ?? "");
        if (candidateVal == null)
            return;
        candidate.val(candidateVal).trigger("change");
    }
    catch {
        const handleErrorDisplay = () => {
            const el = document.querySelector("select#candidate"), message = el ? getLocalizedMessage(el, "candidate_unavailable") : errFb, hasBootstrap = document.querySelector('link[href*="bootstrap"]') &&
                window.bootstrap.Toast;
            if (hasBootstrap) {
                if (!document.querySelector("#error-toast")) {
                    const toast = document.createElement("div");
                    toast.id = "error-toast";
                    toast.className = "toast align-items-center text-bg-danger border-0";
                    for (const [k, v] of Object.entries({
                        role: "alert",
                        "aria-live": "assertive",
                        "aria-atomic": "true",
                    }))
                        toast.setAttribute(k, v);
                    toast.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button"
                            class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>`;
                    document.body.appendChild(toast);
                }
                new bootstrap.Toast(document.querySelector("#error-toast")).show();
            }
            else {
                alert(message);
            }
        };
        const el = document.querySelector("select#candidate");
        if (el && el.getAttribute(dataListenerAdded) !== "true") {
            const observer = new MutationObserver((_, obs) => {
                if (!document.body.contains(el)) {
                    el.removeEventListener("click", handleErrorDisplay);
                    obs.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
            if (!el.getAttribute("data-listener-bound-click")) {
                el.setAttribute("data-listener-bound-click", "1");
                el.addEventListener("click", handleErrorDisplay);
            }
            el.setAttribute(dataListenerAdded, "true");
        }
    }
})();
//# sourceMappingURL=storeSelect.js.map