(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/supports/attachmentEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module attachmentEdit
 */
(() => {
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", DATA_LISTENER_ADDED = "data-listener-added";
    const getLocalizedMsg = (el, msgKey) => {
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
    const showFeedback = (el, key, ev = "click") => {
        const text = getLocalizedMsg(el ?? document.body, key), hasBs = document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap.Toast;
        if (hasBs) {
            let toast = document.querySelector("#np-error-toast");
            if (!toast) {
                toast = document.createElement("div");
                toast.id = "np-error-toast";
                toast.className = "toast align-items-center text-bg-danger border-0";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toast.setAttribute(k, v);
                toast.innerHTML = `
            <div class="d-flex">
              <div class="toast-body">${text}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
                document.body.appendChild(toast);
            }
            const handler = () => {
                new bootstrap.Toast(toast).show();
            };
            document.addEventListener(ev, handler, { once: true });
            const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(toast)) {
                    document.removeEventListener(ev, handler);
                    o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        }
        else {
            const handler = () => {
                alert(text);
            };
            document.addEventListener(ev, handler, { once: true });
        }
    };
    const guardOnce = (targetEl, key, ev = "click") => {
        if (!targetEl || targetEl.getAttribute(DATA_LISTENER_ADDED) === "true")
            return;
        const handler = () => {
            showFeedback(targetEl, key, ev);
        };
        document.addEventListener(ev, handler, { once: true });
        targetEl.setAttribute(DATA_LISTENER_ADDED, "true");
        const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(targetEl)) {
                document.removeEventListener(ev, handler);
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    try {
        if (typeof $ === "undefined") {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("jQuery failed to load");
            return;
        }
        const $input = $("#attachment");
        const $img = $("#image");
        if ($input.length) {
            const onChange = function () {
                // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
                // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-assignment
                const file = this?.files?.[0];
                if (!file || !$img.length) {
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                    guardOnce(this, "attachment_preview_unavailable");
                    return;
                }
                try {
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-assignment
                    const prev = this.getAttribute("data-prev-url") ?? "";
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                    const url = URL.createObjectURL(file);
                    $img.attr("src", url);
                    if (prev)
                        try {
                            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                            URL.revokeObjectURL(prev);
                        }
                        catch (__err) {
                            console.error(`[attachmentEdit] Error:`, __err);
                        }
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
                    this.setAttribute("data-prev-url", url);
                }
                catch {
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                    guardOnce(this, "attachment_preview_unavailable");
                }
            };
            if ($input.attr("data-np-bound") !== "true") {
                $input.on("change", onChange);
                $input.attr("data-np-bound", "true");
                const el = $input.get(0);
                const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                        $input.off("change", onChange);
                        o.disconnect();
                    }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            }
        }
        else {
            guardOnce(document.body, "attachment_preview_unavailable");
        }
    }
    catch (e) {
        if (window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1")
            console.error("Initialization failed", e);
    }
})();
})();