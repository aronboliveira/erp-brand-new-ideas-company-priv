/**
 * @requires ERPUtils (URL handling uses native APIs)
 */
(() => {
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

      input.addEventListener("change", () => {
        try {
          const f = input.files && input.files[0] ? input.files[0] : null;
          const url = f ? safeURL(f) : null;
          if (!url) {
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
