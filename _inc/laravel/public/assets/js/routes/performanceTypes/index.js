/**
 * @fileoverview TypeScript version of public/assets/js/routes/performanceTypes/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(function () {
    const listened = "data-listener-active";
    function toast(message) {
        const text = message ?? "Requested route is unavailable.", hasBs = !!(document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
            window.bootstrap);
        if (hasBs) {
            let box = document.getElementById("toast-container");
            if (!box) {
                box = document.createElement("div");
                box.id = "toast-container";
                document.body.appendChild(box);
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
            b.textContent = text;
            t.appendChild(b);
            box.appendChild(t);
            window.bootstrap.Toast.getOrCreateInstance(t).show();
        }
        else {
            alert(text);
        }
    }
    function guardLink(a) {
        if (!a || a.getAttribute(listened) === "true")
            return;
        a.setAttribute(listened, "true");
        a.addEventListener("click", function (e) {
            const href = (a.getAttribute("href") ?? "#").trim(), url = ((a.getAttribute("data-url") || href) ?? "#").trim();
            if (url !== "#" && href !== "#")
                return;
            e.preventDefault();
            toast(a.getAttribute("data-guard-msg") ?? "");
        });
    }
    function guardForm(f) {
        if (!f || f.getAttribute(listened) === "true")
            return;
        f.setAttribute(listened, "true");
        f.addEventListener("submit", function (e) {
            const action = (f.getAttribute("action") ?? "#").trim(), url = ((f.getAttribute("data-url") || action) ?? "#").trim();
            if (url !== "#" && action !== "#")
                return;
            e.preventDefault();
            toast(f.getAttribute("data-guard-msg") ?? "");
        });
    }
    function hookConfirm(el) {
        const htmlEl = el;
        if (!el || el.getAttribute("data-confirm-hooked") === "true")
            return;
        el.setAttribute("data-confirm-hooked", "true");
        if (!el.getAttribute("data-listener-bound-click")) {
            el.setAttribute("data-listener-bound-click", "1");
            el.addEventListener("click", function (e) {
                const txt = el.getAttribute("data-confirm");
                if (!txt)
                    return;
                e.preventDefault();
                const parts = String(txt).split("|"), title = parts[0] || "", body = parts[1] || "", yes = htmlEl.getAttribute("data-confirm-yes"), hasBs = !!(document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') && window.bootstrap);
                if (hasBs) {
                    let modal = document.getElementById("confirm-modal");
                    if (!modal) {
                        const wrap = document.createElement("div");
                        wrap.innerHTML =
                            '<div class="modal fade" id="confirm-modal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p></p></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal"></button><button type="button" class="btn btn-primary" id="confirm-yes-btn"></button></div></div></div></div>';
                        if (wrap.firstChild)
                            document.body.appendChild(wrap.firstChild);
                    }
                    modal = document.getElementById("confirm-modal");
                    if (!modal)
                        return;
                    const modalTitle = modal.querySelector(".modal-title"), modalBody = modal.querySelector(".modal-body p"), modalCancel = modal.querySelector(".modal-footer .btn-light"), yesBtn = modal.querySelector("#confirm-yes-btn");
                    if (modalTitle)
                        modalTitle.textContent = title;
                    if (modalBody)
                        modalBody.textContent = body;
                    if (modalCancel)
                        modalCancel.textContent = "Cancel";
                    if (yesBtn)
                        yesBtn.textContent = "OK";
                    const inst = window.bootstrap.Modal.getOrCreateInstance(modal);
                    const handler = function () {
                        try {
                            if (yes)
                                // SECURITY: Safe handler dispatch instead of new Function()
                                // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
                                window.__confirmHandlers?.[yes]?.[0]?.() ||
                                    safeFormAction(yes, yesBtn);
                        }
                        catch (_) {
                            console.error(`[index] Error:`, _);
                        }
                        inst.hide();
                    };
                    if (yesBtn)
                        yesBtn.addEventListener("click", handler, { once: true });
                    inst.show();
                }
                else {
                    if (confirm((title ? title + "\n\n" : "") + body)) {
                        try {
                            if (yes)
                                // SECURITY: Safe handler dispatch instead of new Function()
                                // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
                                window.__confirmHandlers?.[yes]?.[0]?.() ||
                                    safeFormAction(yes, document.body);
                        }
                        catch (_) {
                            console.error(`[index] Error:`, _);
                        }
                    }
                }
            });
        }
    }
    // SECURITY: Safe fallback for confirm handlers instead of new Function()
    function safeFormAction(actionStr, _element) {
        if (!actionStr)
            return;
        if (actionStr.startsWith("#") || actionStr.startsWith(".")) {
            const form = document.querySelector(actionStr);
            if (form?.tagName === "FORM")
                form.submit();
            return;
        }
        if (/^(https?:\/\/|\/)/.test(actionStr) &&
            !/^javascript:/i.test(actionStr)) {
            window.location.href = actionStr;
            return;
        }
    }
    function init() {
        document
            .querySelectorAll("a[data-guard-msg],a[data-url]")
            .forEach(guardLink);
        document
            .querySelectorAll("form[data-guard-msg],form[data-url]")
            .forEach(guardForm);
        document.querySelectorAll(".bs-pass-para").forEach(hookConfirm);
        try {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                try {
                    window.bootstrap.Tooltip.getOrCreateInstance(el);
                }
                catch (_) {
                    console.error(`[index] Error:`, _);
                }
            });
        }
        catch (_) {
            console.error(`[index] Error:`, _);
        }
    }
    document.addEventListener("DOMContentLoaded", function () {
        init();
        const mo = new MutationObserver(function () {
            init();
        });
        mo.observe(document.body, { childList: true, subtree: true });
    });
})();
//# sourceMappingURL=index.js.map