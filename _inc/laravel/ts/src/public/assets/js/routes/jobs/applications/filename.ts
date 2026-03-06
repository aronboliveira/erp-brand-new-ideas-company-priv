/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/filename.js
 * @generated from original JavaScript - manual review recommended
 * @module filename
 */
/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access */

((): void => {
  try {
    const inputs = Array.from(
      document.querySelectorAll('input[type="file"][data-filename]')
    );
    if (inputs.length === 0) return;
    inputs.forEach(inp => {
      if (inp.getAttribute("data-filename-guarded") === "true") return;
      inp.setAttribute("data-filename-guarded", "true");
      inp.addEventListener("change", (): void => {
        try {
          const sel = inp.getAttribute("data-filename") ?? "";
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          const out = sel ? document.querySelector("." + sel) : null;
          if (!out) return;
          const file = inp.files?.[0] ? inp.files[0] : null;
          out.textContent = file ? file.name : "";
        } catch {}
      });
    });
  } catch {}
})();

export {};
