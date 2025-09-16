(() => {
  const form = document.getElementById("productService-update-form");
  if (!form) return;

  const toast = msg => {
    try {
      if (window.bootstrap?.Toast) {
        const c =
          document.getElementById("toast-container") ||
          (() => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        el.appendChild(body);
        c.appendChild(el);
        window.bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  form.addEventListener(
    "submit",
    e => {
      const url =
        form.getAttribute("action") || form.getAttribute("data-url") || "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          form.getAttribute("data-guard-msg") ||
          "Update Product/Service route is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
      }
    },
    { passive: false }
  );

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
  if (file && img) {
    file.addEventListener("change", () => {
      const f = file.files?.[0];
      if (f) img.src = URL.createObjectURL(f);
    });
  }
})();
