(() => {
  try {
    const qtyWrap = document.querySelector(".quantity");
    const qtyInput = document.getElementById("quantity");
    const radios = Array.from(
      document.querySelectorAll('input.type[name="type"]')
    );
    if (!qtyWrap || !qtyInput || !radios.length) return;

    const apply = () => {
      const val = (radios.find(r => r.checked) || {}).value || "product";
      const isService = val === "service";
      qtyWrap.style.display = isService ? "none" : "";
      if (isService) {
        qtyInput.removeAttribute("required");
      } else {
        qtyInput.setAttribute("required", "required");
      }
    };

    radios.forEach(r => r.addEventListener("change", apply));
    apply();
  } catch {}
})();
