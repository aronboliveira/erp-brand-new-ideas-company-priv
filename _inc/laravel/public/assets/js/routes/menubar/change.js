/**
 * @fileoverview TypeScript version of public/assets/js/routes/menubar/change.js
 * @generated from original JavaScript - manual review recommended
 * @module change
 */
(() => {
    const lang = (document.documentElement.getAttribute("lang") ?? "en").toLowerCase();
    const dict = (window.translations &&
        (window.translations[lang] || window.translations[lang.split("-")[0]])) ||
        window.translations?.en ||
        {};
    const tr = (k) => dict[k] || k;
    const showToastOrAlert = (msg) => {
        try {
            const hasToast = !!window.bootstrap.Toast;
            if (hasToast) {
                let c = document.getElementById("toast-container");
                if (!c) {
                    c = document.createElement("div");
                    c.id = "toast-container";
                    Object.assign(c.style, {
                        position: "fixed",
                        top: "1rem",
                        right: "1rem",
                        zIndex: "1080",
                    });
                    document.body.appendChild(c);
                }
                const el = document.createElement("div");
                el.className = "toast align-items-center text-bg-danger border-0";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    el.setAttribute(k, v);
                {
                    el.replaceChildren();
                    const _d = document.createElement("div");
                    _d.className = "d-flex";
                    const _b = document.createElement("div");
                    _b.className = "toast-body";
                    _b.textContent = msg;
                    const _c = document.createElement("button");
                    _c.type = "button";
                    _c.className = "btn-close btn-close-white me-2 m-auto";
                    _c.dataset.bsDismiss = "toast";
                    _c.setAttribute("aria-label", "Close");
                    _d.append(_b, _c);
                    el.append(_d);
                }
                c.appendChild(el);
                const t = new window.bootstrap.Toast(el, { delay: 3000 });
                t.show();
                setTimeout(() => {
                    el.remove();
                }, 3400);
            }
            else {
                alert(msg);
            }
        }
        catch {
            alert(msg);
        }
    };
    const previewBinder = (inputId, imgId) => {
        try {
            const input = document.getElementById(inputId), img = document.getElementById(imgId);
            if (!input || !img)
                throw new Error(`${tr("element_unavailable")} (${!input ? inputId : imgId})`);
            input.addEventListener("change", () => {
                try {
                    const file = input.files?.[0];
                    if (!file)
                        return;
                    const URLAPI = window.URL || window.webkitURL;
                    if (!URLAPI.createObjectURL)
                        throw new Error(tr("request_failed"));
                    const src = URLAPI.createObjectURL(file);
                    img.src = src;
                    img.onload = () => {
                        try {
                            URLAPI.revokeObjectURL(src);
                        }
                        catch (__err) {
                            console.error(`[change] Error:`, __err);
                        }
                    };
                }
                catch (e) {
                    showToastOrAlert(e.message || tr("request_failed"));
                }
            });
        }
        catch (e) {
            showToastOrAlert(e.message || tr("request_failed"));
        }
    };
    const start = () => {
        previewBinder("home_banner", "image");
        previewBinder("home_logo", "image1");
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", start, { once: true })
        : start();
})();
//# sourceMappingURL=change.js.map