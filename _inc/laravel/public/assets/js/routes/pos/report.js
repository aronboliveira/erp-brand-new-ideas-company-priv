/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/report.js
 * @generated from original JavaScript - manual review recommended
 * @module report
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
                console.error("jQuery not found for anchors.js");
        }
        catch (_) {
            console.error(`[report] Error:`, _);
        }
        return;
    }
    const ERR_FB = "# ERROR", DCL = "data-client-localized", DGM = "data-guard-msg", DSL = "data-sv-localized", DLA = "data-listener-active", MSG_KEY = "pos_route_unavailable";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const hasBootstrapCss = () => 
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const getMsg = (el) => {
        let msg = ERR_FB;
        if (!el)
            return msg;
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
            msg =
                window.translations?.[lang]?.[MSG_KEY] ||
                    el.getAttribute(DGM) ||
                    window.translations?.en?.[MSG_KEY] ||
                    ERR_FB;
            if (msg !== ERR_FB) {
                el.setAttribute(DGM, msg);
                el.setAttribute(DCL, "true");
            }
        }
        return msg;
    };
    const showError = (el) => {
        const msg = getMsg(el);
        if (hasBootstrapCss() && window.bootstrap) {
            let wrap = document.getElementById("toast-wrap-pos-guard");
            if (!wrap) {
                wrap = document.createElement("div");
                wrap.id = "toast-wrap-pos-guard";
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
    const handlers = new WeakMap();
    const onClick = (el) => (e) => {
        try {
            const url = el.getAttribute("data-url"), href = el.getAttribute("href");
            if ((!url || url === "#") && (!href || href === "#")) {
                e.preventDefault();
                showError(el);
            }
        }
        catch (_) {
            console.error(`[report] Error:`, _);
        }
    };
    const bind = (el) => {
        if (!el || el.getAttribute(DLA) === "true")
            return;
        el.setAttribute(DLA, "true");
        const h = onClick(el);
        handlers.set(el, h);
        $(el).on("click", h);
    };
    const unbind = (el) => {
        if (!el)
            return;
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        const h = handlers.get(el);
        if (h) {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            $(el).off("click", h);
            handlers.delete(el);
        }
        el.removeAttribute(DLA);
    };
    const scan = (root) => {
        const list = (root ?? document).querySelectorAll("a[" + DGM + "]:not([" + DLA + '="true"])');
        list.forEach(bind);
    };
    const ready = () => {
        try {
            scan(document);
        }
        catch (_) {
            console.error(`[report] Error:`, _);
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
                        if (el.hasAttribute(DLA))
                            unbind(el);
                        el.querySelectorAll("a[" + DLA + "]").forEach(unbind);
                    }
                });
        });
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
    (() => {
        const $ = window.jQuery;
        if (!$) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery not found for summary.js");
            }
            catch (_) {
                console.error(`[report] Error:`, _);
            }
            return;
        }
        const init = () => {
            try {
                if (!$.fn.DataTable)
                    return;
                const $t = $(".datatable").filter((_i, el) => el.getAttribute("data-dt-init") !== "true");
                if (!$t.length)
                    return;
                $t.each(function () {
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                    $(this).attr("data-dt-init", "true").DataTable({ order: [] });
                });
            }
            catch (_) {
                console.error(`[report] Error:`, _);
            }
        };
        if (document.readyState === "loading") {
            $(init);
        }
        else {
            init();
        }
    })();
})();
//# sourceMappingURL=report.js.map