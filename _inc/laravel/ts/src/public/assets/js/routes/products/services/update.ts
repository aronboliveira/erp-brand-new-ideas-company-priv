/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  const form = document.getElementById("productService-update-form");
  if (!form) return;
  const toast = (msg: string): void => {
    try {
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          el.setAttribute(k, v);
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
    (e: Event) => {
      const url =
        form.getAttribute("action") ?? form.getAttribute("data-url") ?? "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          form.getAttribute("data-guard-msg") ??
          "Update Product/Service route is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
      }
    },
    { passive: false },
  );

  const qtyWrap = document.querySelector<HTMLElement>(".quantity"),
    typeRadios = document.querySelectorAll('input[name="type"]'),
    qtyInput = document.querySelector('input[name="quantity"]');
  const syncQtyVisibility = (): void => {
    const checked = document.querySelector<HTMLInputElement>(
        'input[name="type"]:checked',
      ),
      val = checked?.value;
    if (!qtyWrap || !qtyInput) return;
    if (val === "service") {
      qtyWrap.classList.add("d-none");
      qtyInput.removeAttribute("required");
    } else {
      qtyWrap.classList.remove("d-none");
      qtyInput.setAttribute("required", "required");
    }
  };

  typeRadios.forEach(r => {
    r.addEventListener("change", syncQtyVisibility);
  });
  syncQtyVisibility();

  const file = document.getElementById("pro_image") as HTMLInputElement | null,
    img = document.getElementById("image") as HTMLImageElement | null;
  if (file && img) {
    if (!file.getAttribute("data-listener-bound-change")) {
      file.setAttribute("data-listener-bound-change", "1");
      file.addEventListener("change", (): void => {
        const f = file.files?.[0];
        if (f) img.src = URL.createObjectURL(f);
      });
    }
  }
})();

export {};
