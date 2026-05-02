(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */



(function () {

    /* assets/js/routes/leads/update.js */

    (function () {
        const L = "data-guard-listener", DCL = "data-client-localized", DGM = "data-guard-msg";

        const DSL = "data-sv-localized", ERR = "# ERROR";

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
            }
        }

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
                    msg =
                        window.translations?.[lang]?.[key] ||
                            el.getAttribute(DGM) ||
                            window.translations?.en?.[key] ||
                            ERR;
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
        function bindSubmitGuard() {
            try {
                const $ = window.jQuery;
                if (!$) {
                    try {
                        if (window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1")
                            console.error("jQuery not found for leads/update");
                    }
                    catch (_) {
                        console.error(`[update] Error:`, _);
                    }
                    return;
                }
                const form = document.getElementById("lead-update-form"), btn = document.getElementById("lead-update-submit");
                if (!form || !btn)
                    return;
                if (form.getAttribute(L) === "true")
                    return;
                form.setAttribute(L, "true");
                $(btn)
                    .off("click.leadsUpdateGuard")
                    .on("click.leadsUpdateGuard", function (e) {
                    try {
                        const url = form.getAttribute("data-url"), href = form.action;
                        if ((!url || url === "#") && (!href || href === "#")) {
                            e.preventDefault();
                            toast(getMsg(form, "action_unavailable"));
                        }
                    }
                    catch (_) {
                        e.preventDefault();
                        toast(getMsg(form, "action_unavailable"));
                    }
                });
                const obs = new MutationObserver(function () {
                    if (!document.body.contains(form) || !document.body.contains(btn)) {
                        try {
                            $(btn).off("click.leadsUpdateGuard");
                        }
                        catch (_) {
                            console.error(`[update] Error:`, _);
                        }
                        obs.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
            }
            catch (_) {
                console.error(`[update] Error:`, _);
            }
        }
        try {
            const $ = window.jQuery;
            if (!$) {
                try {
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("Failed to initialize leads/update");
                }
                catch (_) {
                    console.error(`[update] Error:`, _);
                }
                return;
            }
            $(function () {
                bindSubmitGuard();
            });
        }
        catch (_) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("Failed to run leads/update");
            }
            catch (__) {
                console.error(`[update] Error:`, __);
            }
        }
    })();
    const L = "data-guard-listener";

    const DCL = "data-client-localized", DGM = "data-guard-msg", DSL = "data-sv-localized", ERR = "# ERROR";

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
        }
    }

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
                msg =
                    window.translations?.[lang]?.[key] ||
                        el.getAttribute(DGM) ||
                        window.translations?.en?.[key] ||
                        ERR;
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
    function bindAiGuard() {
        try {
            const $ = window.jQuery;
            if (!$) {
                try {
                    if (window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1")
                        console.error("jQuery not found for aiGenerateGuard");
                }
                catch (_) {
                    console.error(`[update] Error:`, _);
                }
                return;
            }
            const a = document.getElementById("lead-ai-generate");
            if (!a || a.getAttribute(L) === "true")
                return;
            a.setAttribute(L, "true");
            $(a)
                .off("click.aiGuard")
                .on("click.aiGuard", function (e) {
                try {
                    const url = a.getAttribute("data-url"), href = a.href;
                    if ((!url || url === "#") && (!href || href === "#")) {
                        e.preventDefault();
                        toast(getMsg(a, "ai_generate_unavailable"));
                    }
                }
                catch (_) {
                    e.preventDefault();
                    toast(getMsg(a, "ai_generate_unavailable"));
                }
            });
            const obs2 = new MutationObserver(function () {
                if (!document.body.contains(a)) {
                    try {
                        $(a).off("click.aiGuard");
                    }
                    catch (_) {
                        console.error(`[update] Error:`, _);
                    }
                    obs2.disconnect();
                }
            });
            obs2.observe(document.body, { childList: true, subtree: true });
        }
        catch (_) {
            console.error(`[update] Error:`, _);
        }
    }
    try {
        const $ = window.jQuery;
        if (!$) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("Failed to initialize aiGenerateGuard");
            }
            catch (_) {
                console.error(`[update] Error:`, _);
            }
            return;
        }
        $(function () {
            bindAiGuard();
        });
    }
    catch (_) {
        try {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("Failed to run aiGenerateGuard");
        }
        catch (__) {
            console.error(`[update] Error:`, __);
        }
    }
})();
})();