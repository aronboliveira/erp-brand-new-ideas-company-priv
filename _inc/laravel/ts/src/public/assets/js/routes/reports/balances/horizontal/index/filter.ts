/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/balances/horizontal/index/filter.js
 * @generated from original JavaScript - manual review recommended
 * @module filter
 */

((): void => {
  try {
    const btn = document.querySelector<HTMLButtonElement>("button#filter");
    if (!btn) return;
    const flag = "data-click-listener";
    if (btn.hasAttribute(flag) && btn.getAttribute(flag) === "true") return;
    btn.setAttribute(flag, "true");
    btn.addEventListener(
      "click",
      function (e) {
        try {
          e.preventDefault();
          const wrap = document.querySelector<HTMLElement>("div#filter");
          if (!wrap) return;
          const active = wrap.getAttribute("data-active") === "true";
          wrap.setAttribute("data-active", (!active).toString());
          const ev = new CustomEvent("balance-sheet-filter-toggle", {
            detail: { active: !active },
          });
          document.dispatchEvent(ev);
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();

export {};
