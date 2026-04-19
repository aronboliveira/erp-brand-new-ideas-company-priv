/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/users.js
 * @generated from original JavaScript - manual review recommended
 * @module users
 */
(() => {
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", DATA_LISTENER = "data-listener-added";
    const getMsg = (el, key) => {
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
                window.translations?.[lang]?.[key] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[key] ||
                    errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
        }
        return msg;
    };
    const showFeedback = (el, key, ev = "click") => {
        const text = getMsg(el ?? document.body, key), hasBs = document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap.Toast;
        if (hasBs) {
            let toast = document.querySelector("#np-error-toast");
            if (!toast) {
                toast = document.createElement("div");
                toast.id = "np-error-toast";
                toast.className =
                    "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    toast.setAttribute(k, v);
                {
                    toast.replaceChildren();
                    const _d = document.createElement("div");
                    _d.className = "d-flex";
                    const _b = document.createElement("div");
                    _b.className = "toast-body";
                    _b.textContent = text;
                    const _c = document.createElement("button");
                    _c.type = "button";
                    _c.className = "btn-close btn-close-white me-2 m-auto";
                    _c.dataset.bsDismiss = "toast";
                    _c.setAttribute("aria-label", "Close");
                    _d.append(_b, _c);
                    toast.append(_d);
                }
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
    const guardOnce = (el, key, ev = "click") => {
        if (!el || el.getAttribute(DATA_LISTENER) === "true")
            return;
        const cb = () => {
            showFeedback(el, key, ev);
        };
        document.addEventListener(ev, cb, { once: true });
        el.setAttribute(DATA_LISTENER, "true");
        const mo = new MutationObserver((_, o) => {
            if (!document.body.contains(el)) {
                document.removeEventListener(ev, cb);
                o.disconnect();
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });
    };
    const routeGuard = (url, el) => {
        const bad = !url || url === "#";
        if (bad)
            guardOnce(el, "zoom_users_unavailable", "click");
        return bad;
    };
    try {
        if (typeof $ === "undefined") {
            if (window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1")
                console.error("jQuery failed to load");
            return;
        }
        const BASE = "{{ url('zoom-meeting/projects/select') }}", userDiv = $("#user_div"), SELECT_ID = "user_id", SELECT_HTML = `<select class="form-control" id="${SELECT_ID}" name="user_id[]" multiple></select>`;
        const buildOrReuseSelect = () => {
            let $sel = $("#" + SELECT_ID);
            if (!$sel.length) {
                if (!userDiv.children("#" + SELECT_ID).length)
                    userDiv.append(SELECT_HTML);
                $sel = $("#" + SELECT_ID);
            }
            else {
                $sel.empty();
            }
            return $sel;
        };
        const choicesKey = "_npChoicesInstance";
        const ensureChoices = (sel) => {
            if (typeof window.Choices !== "function") {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("Choices failed to load");
                return null;
            }
            const selEl = sel[0];
            if (selEl[choicesKey]) {
                try {
                    selEl[choicesKey].destroy();
                }
                catch (__err) {
                    console.error(`[users] Error:`, __err);
                }
                selEl[choicesKey] = null;
            }
            const inst = new Choices(selEl, { removeItemButton: true });
            selEl[choicesKey] = inst;
            const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(selEl)) {
                    try {
                        inst.destroy();
                    }
                    catch (__err) {
                        console.error(`[users] Error:`, __err);
                    }
                    o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            return inst;
        };
        const fetchUsers = (projectId) => {
            const pid = projectId ?? "", url = `${BASE}/${encodeURIComponent(pid)}`;
            if (routeGuard(url, document.body))
                return;
            $.ajax({
                url,
                type: "GET",
                success: (data) => {
                    const list = Array.isArray(data) ? data : [];
                    const $sel = buildOrReuseSelect();
                    const frag = document.createDocumentFragment();
                    for (const it of list) {
                        const id = String(it?.id ?? "");
                        const name = String(it?.name ?? "");
                        if (id === "")
                            continue;
                        const opt = document.createElement("option");
                        opt.value = id;
                        opt.textContent = name;
                        frag.appendChild(opt);
                    }
                    $sel[0].appendChild(frag);
                    ensureChoices($sel);
                    if (list.length === 0)
                        $sel.empty();
                },
                error: () => {
                    guardOnce(document.body, "zoom_users_unavailable", "click");
                },
            });
        };
        if (document.body.getAttribute("data-zoom-users-bound") !== "true") {
            $(document).on("change", ".project_select", function () {
                const projectId = String($(this).val() ?? "");
                fetchUsers(projectId);
            });
            document.body.setAttribute("data-zoom-users-bound", "true");
        }
    }
    catch (e) {
        if (window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1")
            console.error("Initialization failed", e);
    }
})();
//# sourceMappingURL=users.js.map