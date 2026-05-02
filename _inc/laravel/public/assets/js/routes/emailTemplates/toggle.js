(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/emailTemplates/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */
(() => {
    try {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? "";
        const lang = (() => {
            const l = (sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            return l === "pt-br" ? l : l.slice(0, 2);
        })();

        const t = (k) => window.translations?.[lang]?.[k] ||
            window.translations?.en?.[k] ||
            "# ERROR";
        const pop = (msg, type = "error: Error") => {
            window.show_toastr ? window.show_toastr(type, msg, type) : alert(msg);
        };
        document.addEventListener("click", (e) => {
            const tgt = e.target, cb = tgt?.closest(".email-template-checkbox");
            if (!cb)
                return;
            const url = cb.dataset.url, val = cb.value ?? "";
            if (!url) {
                pop(t("email_template_toggle_failed"));
                return;
            }
            fetch(url, {
                method: "PUT",
                headers: {
                    "X-CSRF-TOKEN": "" + CSRF,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ status: val }),
            })
                .then(r => (r.ok ? r.json().catch(console.error) : Promise.reject()))
                .then((res) => {
                const data = res;
                if (!data.is_success)
                    return Promise.reject();
                pop(data.success ?? "OK", "success");
                cb.value = val === "1" ? "0" : "1";
            })
                .catch(() => {
                pop(t("email_template_toggle_failed"));
            });
        });
    }
    catch (__moduleErr) {
        console.error("[toggle] failed to initialise:", __moduleErr);
    }
})();
})();