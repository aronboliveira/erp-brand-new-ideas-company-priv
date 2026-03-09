/**
 * @file Product/Service Update Route Guard
 * @description Guards product/service update form and manages quantity visibility using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard("#productService-update-form", {
      fallbackMsg:
        "Update Product/Service route is unavailable. Please contact technical support or your domain administrator.",
    });

    const qtyWrap = document.querySelector(".quantity");
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const qtyInput = document.querySelector('input[name="quantity"]');

    const syncQtyVisibility = () => {
      const val = document.querySelector('input[name="type"]:checked')?.value;
      if (!qtyWrap || !qtyInput) return;
      if (val === "service") {
        qtyWrap.classList.add("d-none");
        qtyInput.removeAttribute("required");
      } else {
        qtyWrap.classList.remove("d-none");
        qtyInput.setAttribute("required", "required");
      }
    };

    typeRadios.forEach(r => r.addEventListener("change", syncQtyVisibility));
    syncQtyVisibility();

    const file = document.getElementById("pro_image");
    const img = document.getElementById("image");
    if (file && img)
      file.addEventListener("change", () => {
        const f = file.files?.[0];
        if (f) img.src = URL.createObjectURL(f);
      });
  } catch {}
})();
