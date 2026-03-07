/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applyPreview.js
 * @generated from original JavaScript - manual review recommended
 * @module applyPreview
 */

((): void => {
  try {
    const safeURL = (file: Blob) => {
      try {
        return URL.createObjectURL(file);
      } catch (e) {
        return null;
      }
    };

    const wirePreview = (inputId: string, imgId: string) => {
      const input = document.getElementById(inputId);
      const img = document.getElementById(imgId);
      if (!input || !img) {
        return;
      }
      if (input.getAttribute("data-listener-active") === "true") {
        return;
      }
      input.setAttribute("data-listener-active", "true");

      input.addEventListener("change", (): void => {
        try {
          const inputEl = input as HTMLInputElement;
          const f = inputEl.files?.[0] ? inputEl.files[0] : null;
          const url = f ? safeURL(f) : null;
          if (url == null || url === "") {
            img.style.display = "none";
            img.removeAttribute("src");
            return;
          }
          img.setAttribute("src", url);
          img.style.display = "";
        } catch (err) {}
      });
    };

    wirePreview("profile", "profile_preview");
    wirePreview("resume", "resume_preview");
  } catch (err) {}
})();

export {};
