/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applyPreview.js
 * @generated from original JavaScript - manual review recommended
 * @module applyPreview
 */
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    try {
        // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
        const safeURL = (file) => {
            try {
                return URL.createObjectURL(file);
            }
            catch (_e) {
                return null;
            }
        };
        const wirePreview = (inputId, imgId) => {
            const input = document.getElementById(inputId), img = document.getElementById(imgId);
            if (!input || !img)
                return;
            if (input.getAttribute("data-listener-active") === "true")
                return;
            input.setAttribute("data-listener-active", "true");
            if (!input.getAttribute("data-listener-bound-change")) {
                input.setAttribute("data-listener-bound-change", "1");
                input.addEventListener("change", () => {
                    try {
                        const inputEl = input, f = inputEl.files?.[0] ? inputEl.files[0] : null, url = f ? safeURL(f) : null;
                        if (url == null || url === "") {
                            img.style.display = "none";
                            img.removeAttribute("src");
                            return;
                        }
                        img.setAttribute("src", url);
                        img.style.display = "";
                    }
                    catch (err) {
                        console.error(`[applyPreview] Error:`, err);
                    }
                });
            }
        };
        wirePreview("profile", "profile_preview");
        wirePreview("resume", "resume_preview");
    }
    catch (err) {
        console.error(`[applyPreview] Error:`, err);
    }
})();
//# sourceMappingURL=applyPreview.js.map