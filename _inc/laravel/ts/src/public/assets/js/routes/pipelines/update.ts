/**
 * @fileoverview TypeScript version of public/assets/js/routes/pipelines/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  const form = document.getElementById("pipeline-update-form");
  if (!form) return;

  const showToast = (msg: string): void=> {
    try {
      if (window.bootstrap.Toast) {
        const container =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
            const c = document.createElement("div");
            c.id = "toast-container";
            document.body.appendChild(c);
            return c;
          })();
        const t = document.createElement("div");
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        t.appendChild(body);
        container.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
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
          "Update Pipeline route is unavailable. Please contact technical support or your domain administrator.";
        showToast(msg);
      }
    },
    { passive: false },
  );
})();

export {};
