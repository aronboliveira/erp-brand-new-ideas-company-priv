/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/header.js
 * @generated from original JavaScript - manual review recommended
 * @module header
 */

document.querySelectorAll(".theme-avatar img").forEach(img => {
  if (img.hasAttribute("data-reloading-active")) return;
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
  img.addEventListener("error", function (): void {
    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-call
    let attempt = parseInt(this.getAttribute("data-reload-attempt") ?? "0", 10);
    if (!Number.isFinite(attempt) || attempt < 0) attempt = 0;
    if (attempt === 0) {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
      this.setAttribute(
        "data-original-opacity",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        getComputedStyle(this).opacity
      );
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      this.style.transition =
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        (this.style.transition ?? "") + "opacity 0.25s ease-in-out";
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      this.style.opacity = "0";
    }
    if (attempt >= fallbacks.length) {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-assignment
      this.style.opacity = this.getAttribute("data-original-opacity") ?? "1";
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
      this.removeAttribute("data-reload-attempt");
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
      this.removeAttribute("data-original-opacity");
    } else {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
      this.setAttribute("data-reload-attempt", String(attempt + 1));
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      this.src = window.location.origin + fallbacks[attempt];
    }
  });
  img.addEventListener("load", function (): void {
    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-assignment
    this.style.opacity = this.getAttribute("data-original-opacity") ?? "1";
    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
    this.removeAttribute("data-reload-attempt");
    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
    this.removeAttribute("data-original-opacity");
  });
});
