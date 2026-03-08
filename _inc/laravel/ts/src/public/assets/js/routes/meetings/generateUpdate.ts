/**
 * @fileoverview TypeScript version of public/assets/js/routes/meetings/generateUpdate.js
 * @generated from original JavaScript - manual review recommended
 * @module generateUpdate
 */

((): void => {
  const btn = document.getElementById("ai-generate-meeting-btn");
  if (!btn) return;

  btn.addEventListener(
    "click",
    (e: Event) => {
      const url = btn.getAttribute("data-url") ?? "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          btn.getAttribute("data-guard-msg") ??
          "Generate route is unavailable. Please contact technical support or your domain administrator.";

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

            const toastEl = document.createElement("div");
            toastEl.className = "toast";
            for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);

            const body = document.createElement("div");
            body.className = "toast-body";
            body.textContent = msg;

            toastEl.appendChild(body);
            (container).appendChild(toastEl);
            window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
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
})();

export {};
