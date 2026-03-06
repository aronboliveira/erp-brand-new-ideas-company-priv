/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/inputPreview.js
 * @generated from original JavaScript - manual review recommended
 * @module inputPreview
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
          const targetId = inp.getAttribute("data-preview-target");
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          const img = targetId ? document.getElementById(targetId) : null;
          const file = inp.files?.[0] ? inp.files[0] : null;
          if (!img || !file) return;
          img.src = URL.createObjectURL(file);
        } catch {}
      });
    });
  } catch {}
})();

export {};
