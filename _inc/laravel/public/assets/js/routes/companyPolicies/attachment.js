/**
 * @fileoverview TypeScript version of public/assets/js/routes/companyPolicies/attachment.js
 * @generated from original JavaScript - manual review recommended
 * @module attachment
 */
(() => {
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", el = document.getElementById("attachment");
    if (!el || el.getAttribute("data-listener-active") === "true")
        return;
    el.setAttribute("data-listener-active", "true");
    if (!el.getAttribute("data-listener-bound-change")) {
        el.setAttribute("data-listener-bound-change", "1");
        el.addEventListener("change", function (e) {
            try {
                const file = e.target.files?.[0];
                if (!file)
                    return;
                const img = document.getElementById("image");
                if (!img)
                    throw new Error("noIMG");
                img.src = URL.createObjectURL(file);
            }
            catch {
                let msg = errFb;
                if (el.getAttribute("data-sv-localized") === "true" ||
                    el.getAttribute(dataClientLocalized) === "true") {
                    msg = el.getAttribute(dataGuardMsg) ?? errFb;
                }
                else {
                    let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                        document.documentElement.lang ??
                        "en")
                        .toLowerCase()
                        .replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    const key = "preview_failed";
                    msg =
                        window.translations?.[lang]?.[key] ||
                            el.getAttribute(dataGuardMsg) ||
                            window.translations?.en?.[key] ||
                            errFb;
                    if (msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, "true");
                    }
                }
                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                if (bootstrapLink && window.bootstrap) {
                    let container = document.getElementById("toast-container");
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
                    body.textContent = msg;
                    toastEl.appendChild(body);
                    container.appendChild(toastEl);
                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                }
                else {
                    alert(msg);
                }
            }
        });
    }
    const observer = new MutationObserver(() => {
        if (!document.getElementById("attachment"))
            observer.disconnect();
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();
//# sourceMappingURL=attachment.js.map