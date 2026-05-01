(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/url.js
 * @generated from original JavaScript - manual review recommended
 * @module url
 */
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
    const ERR_FB = "# ERROR", CLIENT_FLAG = "data-client-localized", GUARD_MSG = "data-guard-msg", LANG_KEY = "erp-np-lang";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    function getLocalizedMessage(key, el) {
        let msg = ERR_FB;
        if (el.getAttribute(CLIENT_FLAG) === "true") {
            msg = el.getAttribute(GUARD_MSG) || msg;
        }
        else {
            let lang = (sessionStorage.getItem(LANG_KEY) ?? document.documentElement.lang ?? "en").toLowerCase().replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg = window.translations?.[lang]?.[key] || el.getAttribute(GUARD_MSG) || window.translations?.en?.[key] || msg;
            if (msg !== ERR_FB) {
                el.setAttribute(GUARD_MSG, msg);
                el.setAttribute(CLIENT_FLAG, "true");
            }
        }
        return msg;
    }
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    function showToast(message, _isError = false) {
        try {
            let container = document.getElementById("toast-container");
            if (!container) {
                container = document.createElement("div");
                container.id = "toast-container";
                container.className = "toast-container position-fixed top-0 end-0 p-3";
                container.style.zIndex = "1080";
                document.body.appendChild(container);
            }
            const hasBs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap.Toast;
            if (hasBs) {
                const toast = document.createElement("div");
                toast.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toast.setAttribute(k, v);
                const body = document.createElement("div");
                body.className = "toast-body";
                body.textContent = message;
                toast.appendChild(body);
                container.appendChild(toast);
                bootstrap.Toast.getOrCreateInstance(toast).show();
            }
            else {
                alert(message);
            }
        }
        catch {
            alert(message);
        }
    }
    let errorMessage = "";
    const onPointerUp = () => {
        if (errorMessage !== "") {
            showToast(errorMessage, true);
            errorMessage = "";
        }
    };
    document.addEventListener("pointerup", onPointerUp);
    new MutationObserver((m, obs) => {
        m.forEach(mut => {
            Array.from(mut.removedNodes).forEach(node => {
                if (node === document.documentElement) {
                    document.removeEventListener("pointerup", onPointerUp);
                    obs.disconnect();
                }
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
    // @ts-ignore -- migration: function overload type
    window.copyToClipboard = (text) => {
        const element = document.getElementById(text) ?? document.body;
        try {
            if (!navigator.clipboard)
                throw new Error("url_copy_failed");
            navigator.clipboard
                .writeText(text)
                .then(() => {
                showToast(getLocalizedMessage("url_copy_success", element));
            })
                .catch(() => {
                throw new Error("url_copy_failed");
            });
        }
        catch (err) {
            errorMessage = getLocalizedMessage(err.message, element);
        }
    };
})();
})();