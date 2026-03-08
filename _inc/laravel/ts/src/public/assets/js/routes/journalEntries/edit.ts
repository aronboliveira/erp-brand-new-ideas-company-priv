/**
 * @fileoverview TypeScript version of public/assets/js/routes/journalEntries/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

((): void => {
  const toast = (m: unknown): void=> {
    try {
      const hasBs =
        !!document.querySelector('link[href*="bootstrap"]') &&
        !!window.bootstrap.Toast;
      if (hasBs) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = String(m);
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(String(m));
      }
    } catch {
      alert(String(m));
    }
  };

  const fm = document.getElementById("journalEntry-edit-form");
  if (!fm) return;

  fm.addEventListener("submit", (e: Event) => {
    const url = (
      fm.getAttribute("data-url") ??
      fm.getAttribute("action") ??
      "#"
    ).trim();
    if (!url || url === "#") {
      e.preventDefault();
      toast(fm.getAttribute("data-guard-msg") ?? "Route unavailable.");
    }
  });

  const cancelBtn = document.querySelector('.modal-footer [value="Cancel"]');
  if (cancelBtn) {
    cancelBtn.addEventListener("click", (e: Event) => {
      const url = (cancelBtn.getAttribute("data-index-url") ?? "#").trim();
      if (!url || url === "#") {
        e.preventDefault();
        toast(cancelBtn.getAttribute("data-guard-msg") ?? "Route unavailable.");
      }
    });
  }
})();

export {};
