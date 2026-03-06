/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/toggleType.js
 * @generated from original JavaScript - manual review recommended
 * @module toggleType
 */
/* eslint-disable @typescript-eslint/no-unsafe-assignment */

((): void => {
  try {
    const qtyWrap = document.querySelector<HTMLElement>(".quantity");
    const qtyInput = document.getElementById("quantity");
    const radios = Array.from(
      document.querySelectorAll('input.type[name="type"]')
    );
    if (!qtyWrap || !qtyInput || (radios.length === 0)) return;

    const apply = (): void => {
      // eslint-disable-next-line @typescript-eslint/prefer-optional-chain
      const val = (radios.find(r => r.checked) ?? {}).value ?? "product";
      const isService = val === "service";
      qtyWrap.style.display = isService ? "none" : "";
      if (isService) {
        qtyInput.removeAttribute("required");
      } else {
        qtyInput.setAttribute("required", "required");
      }
    };

    radios.forEach(r => { r.addEventListener("change", apply); });
    apply();
  } catch {}
})();

export {};
