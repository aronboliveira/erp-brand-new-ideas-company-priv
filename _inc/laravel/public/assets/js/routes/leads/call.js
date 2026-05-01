/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/call.js
 * @generated from original JavaScript - manual review recommended
 * @module call
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
    const $ = window.jQuery;
    if (!$) {
        try {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("jQuery not found for callsGuard.js");
        }
        catch (_) {
            console.error(`[call] Error:`, _);
        }
        return;
    }
    const ERR_FB = "# ERROR", DCL = "data-client-localized", DGM = "data-guard-msg", DSL = "data-sv-localized", DLA = "data-listener-active", DPL = "data-pointer-listener", DMK = "data-msg-key";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const hasBootstrapCss = () => !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const getMsg = (el, fallbackKey) => {
        let msg = ERR_FB;
        if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true") {
            msg = el.getAttribute(DGM) || ERR_FB;
        }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const key = el.getAttribute(DMK) || fallbackKey;
            msg =
                window.translations?.[lang]?.[key] ||
                    el.getAttribute(DGM) ||
                    window.translations?.en?.[key] ||
                    ERR_FB;
            if (msg !== ERR_FB) {
                el.setAttribute(DGM, msg);
                el.setAttribute(DCL, "true");
            }
        }
        return msg;
    };
    const showError = (el, key) => {
        const msg = getMsg(el, key);
        if (hasBootstrapCss() && window.bootstrap) {
            let wrap = document.getElementById("toast-wrap-ld-calls");
            if (!wrap) {
                wrap = document.createElement("div");
                wrap.id = "toast-wrap-ld-calls";
                wrap.className = "position-fixed top-0 end-0 p-3";
                wrap.style.zIndex = "1080";
                document.body.appendChild(wrap);
            }
            const t = document.createElement("div");
            t.className = "toast align-items-center text-bg-danger border-0";
            for (const [k, v] of Object.entries({
                role: "alert",
                "aria-live": "assertive",
                "aria-atomic": "true",
            }))
                t.setAttribute(k, v);
            t.innerHTML =
                '<div class="d-flex"><div class="toast-body">' +
                    msg +
                    '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            wrap.appendChild(t);
            new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
        }
        else {
            alert(msg);
        }
    };
    const handlersClick = new WeakMap(), handlersPointer = new WeakMap();
    const bindAnchorGuard = (el) => {
        if (!el || el.getAttribute(DLA) === "true")
            return;
        el.setAttribute(DLA, "true");
        const h = (e) => {
            try {
                const url = el.getAttribute("data-url"), href = el.getAttribute("href");
                if ((!url || url === "#") && (!href || href === "#")) {
                    e.preventDefault();
                    showError(el, "ld_call_route_unavailable");
                }
            }
            catch (_) {
                console.error(`[call] Error:`, _);
            }
        };
        handlersClick.set(el, h);
        $(el).on("click", h);
    };
    const bindFormPointerGuard = (form) => {
        if (!form || form.getAttribute(DPL) === "true")
            return;
        form.setAttribute(DPL, "true");
        const $btns = $(form).find('button[type="submit"], input[type="submit"]');
        if (!$btns.length)
            return;
        const h = (e) => {
            try {
                const url = form.getAttribute("data-url"), action = form.getAttribute("action");
                if ((!url || url === "#") && (!action || action === "#")) {
                    e.preventDefault();
                    e.stopPropagation();
                    showError(form, "ld_call_route_unavailable");
                }
            }
            catch (_) {
                console.error(`[call] Error:`, _);
            }
        };
        handlersPointer.set(form, h);
        $btns.each(function () {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            $(this).on("pointerup", h);
        });
    };
    const unbindAnchorGuard = (el) => {
        if (!el)
            return;
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        const h = handlersClick.get(el);
        if (h) {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            $(el).off("click", h);
            handlersClick.delete(el);
        }
        el.removeAttribute(DLA);
    };
    const unbindFormPointerGuard = (form) => {
        if (!form)
            return;
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        const h = handlersPointer.get(form);
        if (h) {
            $(form)
                .find('button[type="submit"], input[type="submit"]')
                .each(function () {
                // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                $(this).off("pointerup", h);
            });
            handlersPointer.delete(form);
        }
        form.removeAttribute(DPL);
    };
    const scan = (root) => {
        root ||
            document
                .querySelectorAll("a[" + DGM + "]:not([" + DLA + '="true"])')
                .forEach(el => bindAnchorGuard(el));
        const form = document.getElementById("ld-call-form");
        if (form)
            bindFormPointerGuard(form);
    };
    const ready = () => {
        try {
            scan(document);
        }
        catch (_) {
            console.error(`[call] Error:`, _);
        }
    };
    if (document.readyState === "loading") {
        $(ready);
    }
    else {
        ready();
    }
    const mo = new MutationObserver(muts => {
        muts.forEach(m => {
            m.addedNodes &&
                m.addedNodes.forEach(n => {
                    if (n.nodeType === 1)
                        scan(n);
                });
            m.removedNodes &&
                m.removedNodes.forEach(n => {
                    if (n.nodeType === 1) {
                        const el = n;
                        if (el.matches("a[" + DLA + "]"))
                            unbindAnchorGuard(el);
                        el.querySelectorAll("a[" + DLA + "]").forEach(c => unbindAnchorGuard(c));
                        if (el.id === "ld-call-form")
                            unbindFormPointerGuard(el);
                        el.querySelectorAll("#ld-call-form").forEach(c => unbindFormPointerGuard(c));
                    }
                });
        });
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
})();
//# sourceMappingURL=call.js.map