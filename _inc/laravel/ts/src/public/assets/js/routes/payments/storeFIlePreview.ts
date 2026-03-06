/**
 * @fileoverview TypeScript version of public/assets/js/routes/payments/storeFIlePreview.js
 * @generated from original JavaScript - manual review recommended
 * @module storeFIlePreview
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access */

((): void => {
  try {
    const inputs = Array.from(
      document.querySelectorAll('input[type="file"][data-preview-target]')
    );
    if (inputs.length === 0) return;
    inputs.forEach(inp => {
      if (inp.getAttribute("data-preview-guarded") === "true") return;
      inp.setAttribute("data-preview-guarded", "true");
      inp.addEventListener("change", (): void => {
        try {
          const targetSel = inp.getAttribute("data-preview-target");
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          const target = targetSel ? document.querySelector(targetSel) : null;
          const file = inp.files?.[0] ? inp.files[0] : null;
          if (!target || !file) return;
          const url = URL.createObjectURL(file);
          target.setAttribute("src", url);
        } catch {}
      });
    });
  } catch {}
})();

export {};
