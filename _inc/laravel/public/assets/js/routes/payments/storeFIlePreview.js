/**
 * @fileoverview TypeScript version of public/assets/js/routes/payments/storeFIlePreview.js
 * @generated from original JavaScript - manual review recommended
 * @module storeFIlePreview
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
                    const targetSel = inp.getAttribute("data-preview-target"), target = targetSel ? document.querySelector(targetSel) : null, file = inp.files?.[0]
                        ? inp.files[0]
                        : null;
                    if (!target || !file)
                        return;
                    target.setAttribute("src", URL.createObjectURL(file));
                }
                catch (__err) {
                    console.error(`[storeFIlePreview] Error:`, __err);
                }
            });
        });
    }
    catch (__err) {
        console.error(`[storeFIlePreview] Error:`, __err);
    }
})();
//# sourceMappingURL=storeFIlePreview.js.map