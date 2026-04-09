/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */
(() => {
    const Q = (s) => document.querySelector(s), QA = (s) => Array.from(document.querySelectorAll(s)), DEFAULT_ROUTE_MSG = "Requested route is unavailable. Please contact technical support or your domain administrator.";
    const toast = (message) => {
        const text = message || DEFAULT_ROUTE_MSG, hasBs = !!(document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
            window.bootstrap);
        let box = document.getElementById("toast-container");
        if (!box) {
            box = document.createElement("div");
            box.id = "toast-container";
            document.body.appendChild(box);
        }
        if (hasBs) {
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
            b.textContent = text;
            t.appendChild(b);
            box.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
        }
        else {
            alert(text);
        }
    };
    const bindLinkGuard = (a) => {
        if (!a || a.getAttribute("data-listener-active") === "true")
            return;
        a.setAttribute("data-listener-active", "true");
        a.addEventListener("click", function (e) {
            const href = (this.getAttribute("href") ?? "#").trim(), url = (this.getAttribute("data-url") ?? href ?? "#").trim();
            if (url !== "#" && href !== "#")
                return;
            e.preventDefault();
            toast(this.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
            this.setAttribute("data-failed-route", "true");
        });
    };
    const bindFormGuard = (fm) => {
        if (!fm || fm.getAttribute("data-submit-guarded") === "true")
            return;
        fm.setAttribute("data-submit-guarded", "true");
        fm.addEventListener("submit", function (e) {
            const action = (this.getAttribute("action") ?? "#").trim(), url = (this.getAttribute("data-url") ?? action ?? "#").trim();
            if (url !== "#" && action !== "#")
                return;
            e.preventDefault();
            toast(this.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
            this.setAttribute("data-failed-route", "true");
        });
    };
    const initTooltips = () => {
        try {
            QA('[data-bs-toggle="tooltip"]').forEach((el) => {
                try {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                }
                catch (__err) {
                    console.error(`[apply] Error:`, __err);
                }
            });
        }
        catch (__err) {
            console.error(`[apply] Error:`, __err);
        }
    };
    const filenameFromInput = (inp) => {
        if (!inp.files)
            return "";
        if (inp.files.length === 0)
            return "";
        if (inp.files.length === 1)
            return inp.files[0].name ?? "";
        return Array.from(inp.files)
            .map((f) => f.name ?? "")
            .filter(Boolean)
            .join(", ");
    };
    const previewImage = (file, imgEl) => {
        if (!file || !imgEl)
            return;
        try {
            const url = URL.createObjectURL(file);
            imgEl.src = url;
            imgEl.onload = () => {
                try {
                    URL.revokeObjectURL(url);
                }
                catch (__err) {
                    console.error(`[apply] Error:`, __err);
                }
            };
        }
        catch (__err) {
            console.error(`[apply] Error:`, __err);
        }
    };
    const bindFileInputs = () => {
        QA('input[type="file"][data-filename]').forEach((inp) => {
            if (inp.getAttribute("data-file-listener") === "true")
                return;
            inp.setAttribute("data-file-listener", "true");
            const outClass = inp.getAttribute("data-filename") ?? "", out = outClass ? Q(`.${CSS.escape(outClass)}`) : null;
            if (!inp.getAttribute("data-listener-bound-change")) {
                inp.setAttribute("data-listener-bound-change", "1");
                inp.addEventListener("change", function () {
                    if (out)
                        out.textContent = filenameFromInput(this) ?? "";
                    const id = this.id ?? "";
                    if (this.files?.[0]) {
                        if (id === "profile")
                            previewImage(this.files[0], Q("#blah"));
                        if (id === "resume")
                            previewImage(this.files[0], Q("#blah1"));
                    }
                });
            }
        });
    };
    document.addEventListener("DOMContentLoaded", () => {
        QA("a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
        QA("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
        initTooltips();
        bindFileInputs();
    });
})();
//# sourceMappingURL=apply.js.map