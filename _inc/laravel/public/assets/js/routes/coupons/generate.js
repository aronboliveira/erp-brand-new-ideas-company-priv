/**
 * Coupons Generate Route Guards
 * Handles AI generation button, code type toggles, and manual code generation
 * @module routes/coupons/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#coupon-generate-ai-btn", {
    fallbackMsg:
      "Coupon AI generation route is unavailable. Please contact technical support or your domain administrator.",
  });

  // Manual/Auto code toggle
  const manualRad = document.getElementById("manual_code");
  const autoRad = document.getElementById("auto_code");
  const manualDiv = document.getElementById("manual");
  const autoDiv = document.getElementById("auto");

  if (manualRad && autoRad && manualDiv && autoDiv) {
    const toggle = () => {
      if (manualRad.checked) {
        manualDiv.classList.remove("d-none");
        autoDiv.classList.add("d-none");
      } else {
        autoDiv.classList.remove("d-none");
        manualDiv.classList.add("d-none");
      }
    };
    manualRad.addEventListener("change", toggle);
    autoRad.addEventListener("change", toggle);
    toggle();
  }

  // Code generation button
  const generateBtn = document.getElementById("code-generate");
  if (
    generateBtn &&
    generateBtn.getAttribute("data-listener-active") !== "true"
  ) {
    generateBtn.setAttribute("data-listener-active", "true");
    generateBtn.addEventListener("click", event => {
      try {
        event.preventDefault();
        const input = document.getElementById("auto-code");
        if (!input) return;
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        let code = "";
        for (let i = 0; i < 8; i++) {
          code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        input.value = code;
      } catch (e) {}
    });
  }
})();
