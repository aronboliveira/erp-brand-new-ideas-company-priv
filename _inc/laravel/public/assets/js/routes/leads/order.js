/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/order.js
 * @generated from original JavaScript - manual review recommended
 * @module order
 */
(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-leads-error", dataBindDrag = "data-dragula-bound", dataBindPipe = "data-pipeline-bound", ns = "._npLeads";
    const qs = (s, r = document) => r.querySelector(s);
    const hasBS = () => !!(
    (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]'))) && !!window.bootstrap.Toast;
    const ensureToastContainer = () => {
        let c = qs("#np-toast-container");
        if (c)
            return c;
        c = document.createElement("div");
        c.id = "np-toast-container";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (message) => {
        if (hasBS()) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    t.setAttribute(k, v);
                t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = t.querySelector(".toast-body");
            if (body)
                body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            }
            catch (_) {
                alert(message ?? errFb);
            }
        }
        else {
            alert(message ?? errFb);
        }
    };
    const schedulePointerupError = (msg) => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true")
            return;
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(msg);
            }
            finally {
                host.removeAttribute(dataErrGuard);
            }
        };
        document.addEventListener("pointerup", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    const getMsg = (el, key) => {
        let msg = errFb;
        if (el.getAttribute(dataSvLocalized) === "true" ||
            el.getAttribute(dataClientLocalized) === "true")
            msg = el.getAttribute(dataGuardMsg) || errFb;
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const csrf = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta?.getAttribute("content") ?? "";
    };
    const verifyRoute = (candidate) => {
        const a = document.createElement("a");
        a.setAttribute("data-url", candidate ?? "");
        a.href = candidate ?? "";
        const url = a.getAttribute("data-url"), href = a.href;
        if ((!url || url === "#") && (!href || href === "#"))
            return false;
        return true;
    };
    const ensureJq = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[order] Error:`, _);
            }
            schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
            return false;
        }
        return true;
    };
    const ensureDragula = () => {
        if (typeof window.dragula === "function")
            return true;
        try {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("Dragula unavailable");
        }
        catch (_) {
            console.error(`[order] Error:`, _);
        }
        schedulePointerupError(getMsg(document.body, "dragula_unavailable"));
        return false;
    };
    const bindDragula = () => {
        if (!ensureJq() || !ensureDragula())
            return;
        if (document.body.getAttribute(dataBindDrag) === "true")
            return;
        document.body.setAttribute(dataBindDrag, "true");
        $('[data-plugin="dragula"]').each(function () {
            const $root = $(this);
            const containers = $root.data("containers");
            let nodes = [];
            if (containers?.length) {
                for (let i = 0; i < containers.length; i++) {
                    const el = document.getElementById(containers[i]);
                    if (el)
                        nodes.push(el);
                }
            }
            else {
                const rootEl = $root.get(0);
                if (rootEl)
                    nodes = [rootEl];
            }
            const handleCls = $root.data("handleclass"), dragulaFn = window.dragula;
            if (typeof dragulaFn !== "function")
                return;
            const drake = handleCls
                ? dragulaFn(nodes, {
                    moves: function (_el, _src, handle) {
                        return handle?.classList.contains(handleCls) ?? false;
                    },
                })
                : dragulaFn(nodes);
            drake.on("drop", ((el, target, source) => {
                try {
                    const order = [];
                    $("#" + target.id + " > div").each(function () {
                        order[$(this).index()] = $(this).attr("data-id");
                    });
                    const id = $(el).attr("data-id"), old_status = $("#" + source.id).data("status"), new_status = $("#" + target.id).data("status"), stage_id = $(target).attr("data-id"), pipeline_id = "{{$pipeline->id}}";
                    $("#" + source.id)
                        .parent()
                        .find(".count")
                        .text(String($("#" + source.id + " > div").length));
                    $("#" + target.id)
                        .parent()
                        .find(".count")
                        .text(String($("#" + target.id + " > div").length));
                    const url = "{{route('leads.order')}}";
                    if (!verifyRoute(url)) {
                        schedulePointerupError(getMsg(document.body, "route_unavailable"));
                        return;
                    }
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            lead_id: id ?? "",
                            stage_id: stage_id ?? "",
                            order: order,
                            new_status: new_status ?? "",
                            old_status: old_status ?? "",
                            pipeline_id: pipeline_id,
                            _token: csrf(),
                        },
                        success: function () { },
                        error: function (_xhr) {
                            schedulePointerupError(getMsg(document.body, "leads_order_unavailable"));
                        },
                    });
                }
                catch (_) {
                    schedulePointerupError(getMsg(document.body, "leads_order_unavailable"));
                }
            }));
        });
        const mo = new MutationObserver(function () {
            if (!$('[data-plugin="dragula"]').length)
                document.body.removeAttribute(dataBindDrag);
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    const bindPipelineChange = () => {
        if (!ensureJq())
            return;
        if (document.body.getAttribute(dataBindPipe) === "true")
            return;
        document.body.setAttribute(dataBindPipe, "true");
        $(document).on("change" + ns, "#default_pipeline_id", function () {
            try {
                const $f = $("#change-pipeline");
                if ($f.length) {
                    $f.trigger("submit");
                }
                else {
                    schedulePointerupError(getMsg(document.body, "form_unavailable"));
                }
            }
            catch (_) {
                schedulePointerupError(getMsg(document.body, "form_unavailable"));
            }
        });
        const mo = new MutationObserver(function () {
            if (!$("#default_pipeline_id").length) {
                $(document).off("change" + ns, "#default_pipeline_id");
                document.body.removeAttribute(dataBindPipe);
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            bindDragula();
            bindPipelineChange();
        }, { once: true });
    }
    else {
        bindDragula();
        bindPipelineChange();
    }
})();
//# sourceMappingURL=order.js.map