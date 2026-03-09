(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/toggleType.js
 * @generated from original JavaScript - manual review recommended
 * @module toggleType
 */
(() => {
    try {
        const qtyWrap = document.querySelector(".quantity"), qtyInput = document.getElementById("quantity"), radios = Array.from(document.querySelectorAll('input.type[name="type"]'));
        if (!qtyWrap || !qtyInput || radios.length === 0)
            return;
        const apply = () => {
            const val = radios
                .find(r => r.checked)
                ?.getAttribute("value") ?? "product";
            const isService = val === "service";
            qtyWrap.style.display = isService ? "none" : "";
            if (isService) {
                qtyInput.removeAttribute("required");
            }
            else {
                qtyInput.setAttribute("required", "required");
            }
        };
        radios.forEach(r => {
            r.addEventListener("change", apply);
        });
        apply();
    }
    catch (__err) {
        console.error(`[toggleType] Error:`, __err);
    }
})();
})();