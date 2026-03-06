/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applyPreview.js
 * @generated from original JavaScript - manual review recommended
 * @module applyPreview
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access */

((): void => {
  try {
    const safeURL = file => {
      try {
        return URL.createObjectURL(file);
      } catch (e) {
        return null;
      }
    };

    const wirePreview = (inputId, imgId) => {
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
          const f = input.files?.[0] ? input.files[0] : null;
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
