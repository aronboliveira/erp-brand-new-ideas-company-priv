/**
 * @file Payment Update Route Guard
 * @description Guards payment update form and manages file input preview using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard("#payment-update-form", {
      fallbackMsg:
        "Update route is unavailable. Please contact technical support or your domain administrator.",
    });

    const fileInput = document.getElementById("payment-files");
    const img = document.getElementById("payment-image");
    if (fileInput && img)
      fileInput.addEventListener("change", function () {
        if (this.files?.[0]) img.src = URL.createObjectURL(this.files[0]);
      });
  } catch {}
})();
