/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/filename.js
 * @generated from original JavaScript - manual review recommended
 * @module filename
 */

((): void => {
  try {
    const inputs = Array.from(
      document.querySelectorAll('input[type="file"][data-filename]'),
    );
    if (inputs.length === 0) return;
    inputs.forEach(inp => {
      if (inp.getAttribute("data-filename-guarded") === "true") return;
      inp.setAttribute("data-filename-guarded", "true");
      inp.addEventListener("change", (): void => {
        try {
          const sel = inp.getAttribute("data-filename") ?? "";
          const out = sel ? document.querySelector("." + sel) : null;
          if (!out) return;
          const inputEl = inp as HTMLInputElement;
          const file = inputEl.files?.[0] ? inputEl.files[0] : null;
          out.textContent = file ? file.name : "";
        } catch {}
      });
    });
  } catch {}
})();

export {};
