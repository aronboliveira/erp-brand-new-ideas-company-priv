(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/barcodeSetting.js
 * @generated from original JavaScript - manual review recommended
 * @module barcodeSetting
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
    const L = "data-guard-listener", DCL = "data-client-localized", DGM = "data-guard-msg", DSL = "data-sv-localized", ERR = "# ERROR";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const map = new WeakMap();
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    function hasBootstrapCss() {
        try {
            return !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
        }
        catch (_) {
            return false;
        }
    }
    function toast(msg) {
        try {
            if (hasBootstrapCss() && window.bootstrap.Toast) {
                let c = document.getElementById("toast-container");
                if (!c) {
                    c = document.createElement("div");
                    c.id = "toast-container";
                    document.body.appendChild(c);
                }
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
                window.bootstrap.Toast.getOrCreateInstance(t).show();
            }
            else {
                alert(msg);
            }
        }
        catch (_) {
            alert(msg);
            // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
        }
    }
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    function getMsg(el, key) {
        try {
            let msg = ERR;
            if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true")
                msg = el.getAttribute(DGM) || ERR;
            else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                    document.documentElement.lang ??
                    "en")
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
                const dict = window.translations || {};
                msg = dict[lang][key] || el.getAttribute(DGM) || dict.en[key] || ERR;
                if (msg !== ERR) {
                    el.setAttribute(DGM, msg);
                    el.setAttribute(DCL, "true");
                }
            }
            return msg || ERR;
        }
        catch (_) {
            return ERR;
        }
    }
    function bindForm($f) {
        const f = $f.get(0);
        if (!f || f.getAttribute(L) === "true")
            return;
        f.setAttribute(L, "true");
        const handler = function (e) {
            try {
                const url = f.getAttribute("data-url"), href = f.action;
                if ((!url || url === "#") && (!href || href === "#")) {
                    e.preventDefault();
                    toast(getMsg(f, "action_unavailable"));
                }
            }
            catch (_) {
                e.preventDefault();
                toast(getMsg(f, "action_unavailable"));
            }
        };
        $f.on("submit.formGuard", handler);
        map.set(f, handler);
    }
    function unbindForm(f) {
        try {
            if (!f)
                return;
            if (!window.jQuery)
                return;
            const $f = window.jQuery(f);
            $f.off("submit.formGuard");
            f.removeAttribute(L);
            map.delete(f);
        }
        catch (_) {
            console.error(`[barcodeSetting] Error:`, _);
        }
    }
    function observeRemoval(f) {
        try {
            const obs = new MutationObserver(function () {
                if (!document.body.contains(f)) {
                    unbindForm(f);
                    obs.disconnect();
                }
            });
            obs.observe(document.body, { childList: true, subtree: true });
        }
        catch (_) {
            console.error(`[barcodeSetting] Error:`, _);
        }
    }
    try {
        const $ = window.jQuery;
        if (!$) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery not found for formGuard");
            }
            catch (_) {
                console.error(`[barcodeSetting] Error:`, _);
            }
            return;
        }
        $(function () {
            try {
                const $forms = $("form[data-guard-msg], form[data-url]");
                $forms.each(function () {
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                    const $f = $(this);
                    bindForm($f);
                    observeRemoval($f.get(0));
                });
            }
            catch (_) {
                console.error(`[barcodeSetting] Error:`, _);
            }
        });
    }
    catch (_) {
        try {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("Failed to initialize formGuard");
        }
        catch (__) {
            console.error(`[barcodeSetting] Error:`, __);
        }
    }
    (function () {
        const L = "data-listener-active", NS = ".barcodeSetting";
        function bindSelect($s) {
            const el = $s.get(0);
            if (!el || el.getAttribute(L) === "true")
                return;
            el.setAttribute(L, "true");
            if (!el.value && el.options.length)
                el.selectedIndex = 0;
            $s.on("change" + NS, function () {
                try {
                    const v = $s.val();
                    if (v == null)
                        return;
                    el.setAttribute("data-has-selection", String(v !== ""));
                }
                catch (_) {
                    console.error(`[barcodeSetting] Error:`, _);
                }
            });
        }
        function unbindSelect(el) {
            try {
                if (!el)
                    return;
                if (!window.jQuery)
                    return;
                const $s = window.jQuery(el);
                $s.off("change" + NS);
                el.removeAttribute(L);
            }
            catch (_) {
                console.error(`[barcodeSetting] Error:`, _);
            }
        }
        function observeRemoval(nodeList) {
            try {
                const obs = new MutationObserver(function () {
                    nodeList.forEach(function (el) {
                        if (!document.body.contains(el))
                            unbindSelect(el);
                    });
                });
                obs.observe(document.body, { childList: true, subtree: true });
            }
            catch (_) {
                console.error(`[barcodeSetting] Error:`, _);
            }
        }
        try {
            const $ = window.jQuery;
            if (!$) {
                try {
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("jQuery not found for barcodeSetting");
                }
                catch (_) {
                    console.error(`[barcodeSetting] Error:`, _);
                }
                return;
            }
            $(function () {
                try {
                    const form = document.getElementById("pos-barcode-setting-form");
                    if (!form)
                        return;
                    const selects = form.querySelectorAll('select[data-toggle="select"]');
                    const nodes = [];
                    selects.forEach(function (s) {
                        const $s = $(s);
                        bindSelect($s);
                        nodes.push(s);
                    });
                    observeRemoval(nodes);
                }
                catch (_) {
                    console.error(`[barcodeSetting] Error:`, _);
                }
            });
        }
        catch (_) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("Failed to initialize barcodeSetting");
            }
            catch (__) {
                console.error(`[barcodeSetting] Error:`, __);
            }
        }
    })();
})();
})();