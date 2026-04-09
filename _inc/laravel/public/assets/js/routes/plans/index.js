/**
 * @fileoverview TypeScript version of public/assets/js/routes/plans/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */
(function () {
    const mark = "data-listener-active";
    function toast(message) {
        const text = message ?? "Requested route is unavailable.", hasBs = !!(document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
            window.bootstrap);
        if (!hasBs) {
            alert(text);
            return;
        }
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
        bootstrap.Toast.getOrCreateInstance(t).show();
    }
    function guardLink(a) {
        if (!a || a.getAttribute(mark) === "true")
            return;
        a.setAttribute(mark, "true");
        a.addEventListener("click", function (e) {
            const href = (a.getAttribute("href") ?? "#").trim(), url = ((a.getAttribute("data-url") || href) ?? "#").trim();
            if (url !== "#" && href !== "#")
                return;
            e.preventDefault();
            toast(a.getAttribute("data-guard-msg") ?? "");
        });
    }
    function init() {
        document
            .querySelectorAll("a[data-guard-msg], a[data-url]")
            .forEach(guardLink);
        try {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                try {
                    bootstrap.Tooltip.getOrCreateInstance(el);
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
        const mo = new MutationObserver(init);
        mo.observe(document.body, { childList: true, subtree: true });
    });
})();
//# sourceMappingURL=index.js.map