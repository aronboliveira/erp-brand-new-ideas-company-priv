/**
 * @fileoverview TypeScript version of public/assets/js/routes/features/highlight/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */


((): void => {
  const form = document.getElementById("highlight-feature-store-form");
  if (form) {
    form.addEventListener(
      "submit",
      (e: Event) => {
        const url =
          form.getAttribute("action") ?? form.getAttribute("data-url") ?? "#";
        if (!url || url === "#") {
          e.preventDefault();
          const msg =
            form.getAttribute("data-guard-msg") ??
            "Store Highlight Feature route is unavailable. Please contact technical support or your domain administrator.";
          try {
            if (window.bootstrap.Toast) {
              const c =
                document.getElementById("toast-container") ??
                ((): HTMLElement => {
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
        }
      },
      { passive: false },
    );
  }
  const input = document.getElementById(
    "highlight_feature_image",
  ) as HTMLInputElement | null;
  const img = document.getElementById("image1") as HTMLImageElement | null;
  if (input && img) {
    input.addEventListener("change", (): void => {
      const f = input.files?.[0];
      if (!f) return;
      img.src = URL.createObjectURL(f);
    });
  }
})();

export {};
