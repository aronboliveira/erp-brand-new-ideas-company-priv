/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/header.js
 * @generated from original JavaScript - manual review recommended
 * @module header
 */
document.querySelectorAll(".theme-avatar img").forEach(img => {
    if (img.hasAttribute("data-reloading-active"))
        return;
    img.setAttribute("data-reloading-active", "true");
    const fallbacks = [
        "public/assets/images/user/defaults/fictional_tech_lead.webp",
        "assets/images/user/defaults/fictional_tech_lead.png",
        "/public/uploads/avatar/avatar.png",
        "/public/avatar/avatar.png",
        "/public/avatar.png",
        "/public/Modules/landingpage/images/user/avatar.png",
        "/public/Modules/landingpage/images/avatar.png",
        "/public/assets/imgs/avatar.png",
        "/public/assets/avatar.png",
        "/public/storage/avatar/avatar.png",
        "/public/storage/avatar.png",
    ];
    if (!img.getAttribute("data-listener-bound-error")) {
        img.setAttribute("data-listener-bound-error", "1");
        const imgEl = img;
        img.addEventListener("error", () => {
            let attempt = parseInt(imgEl.getAttribute("data-reload-attempt") ?? "0", 10);
            if (!Number.isFinite(attempt) || attempt < 0)
                attempt = 0;
            if (attempt === 0) {
                imgEl.setAttribute("data-original-opacity", getComputedStyle(imgEl).opacity);
                imgEl.style.transition =
                    (imgEl.style.transition ?? "") + "opacity 0.25s ease-in-out";
                imgEl.style.opacity = "0";
            }
            if (attempt >= fallbacks.length) {
                imgEl.style.opacity =
                    imgEl.getAttribute("data-original-opacity") ?? "1";
                imgEl.removeAttribute("data-reload-attempt");
                imgEl.removeAttribute("data-original-opacity");
            }
            else {
                imgEl.setAttribute("data-reload-attempt", String(attempt + 1));
                imgEl.src = window.location.origin + fallbacks[attempt];
            }
        });
    }
    if (!img.getAttribute("data-listener-bound-load")) {
        img.setAttribute("data-listener-bound-load", "1");
        const imgEl = img;
        img.addEventListener("load", () => {
            imgEl.style.opacity = imgEl.getAttribute("data-original-opacity") ?? "1";
            imgEl.removeAttribute("data-reload-attempt");
            imgEl.removeAttribute("data-original-opacity");
        });
    }
});
//# sourceMappingURL=header.js.map