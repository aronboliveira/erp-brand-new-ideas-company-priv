/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/balances/index/filter.js
 * @generated from original JavaScript - manual review recommended
 * @module filter
 */
(() => {
    try {
        const btn = document.querySelector("button#filter");
        if (!btn)
            return;
        const flag = "data-click-listener";
        if (btn.hasAttribute(flag) && btn.getAttribute(flag) === "true")
            return;
        btn.setAttribute(flag, "true");
        btn.addEventListener("click", function (e) {
            try {
                e.preventDefault();
                const wrap = document.querySelector("div#filter");
                if (!wrap)
                    return;
                const active = wrap.getAttribute("data-active") === "true";
                wrap.setAttribute("data-active", (!active).toString());
                const ev = new CustomEvent("balance-sheet-filter-toggle", {
                    detail: { active: !active },
                });
                document.dispatchEvent(ev);
            }
            catch (_) {
                console.error(`[filter] Error:`, _);
            }
        }, { passive: false });
    }
    catch (_) {
        console.error(`[filter] Error:`, _);
    }
})();
//# sourceMappingURL=filter.js.map