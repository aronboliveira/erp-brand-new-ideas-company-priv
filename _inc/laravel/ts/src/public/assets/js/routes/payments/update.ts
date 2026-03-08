/**
 * @fileoverview TypeScript version of public/assets/js/routes/payments/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  const form = document.getElementById("payment-update-form");
  if (!form) return;

  const toast = (msg: string): void=> {
    try {
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
            const d = document.createElement("div");
            d.id = "toast-container";
            document.body.appendChild(d);
            return d;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
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
          "Update route is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
      }
    },
    { passive: false },
  );

  const fileInput = document.getElementById(
    "payment-files",
  ) as HTMLInputElement | null;
  const img = document.getElementById(
    "payment-image",
  ) as HTMLImageElement | null;

  if (fileInput && img) {
    fileInput.addEventListener("change", function (): void {
      if (fileInput.files?.[0]) {
        const src = URL.createObjectURL(fileInput.files[0]);
        img.src = src;
        img.style.display = "";
      }
    });
  }
})();

export {};
