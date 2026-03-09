/**
 * @fileoverview TypeScript version of public/assets/js/routes/journalEntries/generateEdit.js
 * @generated from original JavaScript - manual review recommended
 * @module generateEdit
 */

((): void => {
  const toast = (m: string): void => {
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
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = m;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(m);
      }
    } catch {
      alert(m);
    }
  };

  const selector = '[data-ajax-popup-over="true"][data-url]';
  const bind = (a: Element): void => {
    if (a.getAttribute("data-gen-guarded") === "true") return;
    a.setAttribute("data-gen-guarded", "true");
    a.addEventListener("click", (e: Event) => {
      const url = (a.getAttribute("data-url") ?? "#").trim();
      if (!url || url === "#") {
        e.preventDefault();
        toast(
          a.getAttribute("data-guard-msg") ??
            "AI generation route unavailable.",
        );
      }
    });
  };

  document.querySelectorAll(selector).forEach(bind);
  new MutationObserver((): void => {
    document.querySelectorAll(selector).forEach(bind);
  }).observe(document.body, { childList: true, subtree: true });
})();

export {};
