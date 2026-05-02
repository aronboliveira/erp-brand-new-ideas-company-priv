(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/boards/convert.js
 * @generated from original JavaScript - manual review recommended
 * @module convert
 */


(() => {

    const Q = (s) => document.querySelector(s),

    QA = (s) => Array.from(document.querySelectorAll(s)), G = () => {
        try {
            QA('[data-bs-toggle="tooltip"]').forEach((el) => {
                try {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                }
                catch (_) {
                    console.error(`[convert] Error:`, _);
                }
            });
        }
        catch (_) {
            console.error(`[convert] Error:`, _);
        }
    };
    const L = () => {
        QA('input[type="file"][data-filename]').forEach(el => {
            const i = el, c = i.getAttribute("data-filename"), o = c ? Q(`.${c}`) : null;
            const set = () => {
                if (o)
                    o.textContent = i.files?.[0]?.name ?? "";
            };
            i.addEventListener("change", set);
            set();
        });

    };

    const N = (d) => {
        if (!d)
            return [];
        if (Array.isArray(d))
            return d
                .map(x => typeof x === "object"
                ?

                    { id: x.id ?? x.value ?? "", name: x.name ?? x.text ?? "" }
                : null)
                .filter((x) => x != null);
        if (typeof d === "object")
            return Object.keys(d).map(k => ({
                id: k,
                name: String(d[k]),
            }));
        return [];
    };
    const P = (sel, items, selId = "") => {
        if (!sel)
            return;
        sel.innerHTML = "";
        const def = document.createElement("option");
        def.value = "";
        def.textContent = "Select any Designation";
        sel.appendChild(def);

        N(items).forEach(it => {
            const o = document.createElement("option");

            o.value = String(it.id);

            o.textContent = String(it.name);

            if (selId && String(selId) === String(it.id))
                o.selected = true;
            sel.appendChild(o);
        });
        try {
            const jQ = window.jQuery;
            if (jQ?.fn.select2 && jQ(sel).data("select2"))
                jQ(sel).trigger("change.select2");

        }
        catch (_) {
            console.error(`[convert] Error:`, _);
        }
    };

    const C = () => {
        const d = Q("#department_id"), s = Q("#designation_id");
        if (!d || !s)
            return;
        const url = (s.getAttribute("data-url") ||
            d.getAttribute("data-designation-url")) ??
            "#", _guard = (s.getAttribute("data-guard-msg") ||
            d.getAttribute("data-guard-msg")) ??
            "";
        const csrf = (document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('input[name="_token"]')?.value) ??
            "";

        const load = async (id) => {
            if (!id) {
                P(s, []);
                return;
            }
            if (!url || url.trim() === "#" || /^javascript:/i.test(url))
                return;
            const payload = { department_id: id };

            const doFetch = () => fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    ...(csrf ? { "X-CSRF-TOKEN": csrf } : {}),
                },
                body: JSON.stringify(payload),
            }).then(r => (r.ok ? r.json().catch(() => ({})) : Promise.reject()));
            const doAjax = () => {
                if (typeof $ === "undefined")
                    return Promise.reject();
                return Promise.resolve($.ajax({
                    url,
                    method: "POST",
                    headers: csrf ? { "X-CSRF-TOKEN": csrf } : {},
                    data: payload,
                }));
            };
            try {

                const res = await (typeof fetch === "function"
                    ? doFetch().catch(doAjax)
                    : doAjax().catch(doFetch));


                P(s, res?.data ?? res ?? []);
            }
            catch (_) {
                console.error(`[convert] Error:`, _);
            }
        };

        d.addEventListener("change", () => load(d.value));
        if (d.value)
            void load(d.value);
    };
    document.addEventListener("DOMContentLoaded", () => {
        G();
        L();
        C();
    });
})();
})();