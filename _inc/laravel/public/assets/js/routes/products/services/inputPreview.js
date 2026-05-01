(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/inputPreview.js
 * @generated from original JavaScript - manual review recommended
 * @module inputPreview
 */
(() => {
    try {
        const inputs = Array.from(document.querySelectorAll('input[type="file"][data-preview-target]'));
        if (inputs.length === 0)
            return;
        inputs.forEach(inp => {
            if (inp.getAttribute("data-preview-guarded") === "true")
                return;
            inp.setAttribute("data-preview-guarded", "true");
            inp.addEventListener("change", () => {
                try {
                    const targetId = inp.getAttribute("data-preview-target"), img = targetId ? document.getElementById(targetId) : null, file = inp.files?.[0]
                        ? inp.files[0]
                        : null;
                    if (!img || !file)
                        return;
                    img.src = URL.createObjectURL(file);
                }
                catch (__err) {
                    console.error(`[inputPreview] Error:`, __err);
                }
            });
        });
    }
    catch (__err) {
        console.error(`[inputPreview] Error:`, __err);
    }
})();
})();