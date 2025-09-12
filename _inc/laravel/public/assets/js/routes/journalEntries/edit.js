(() => {
  const toast = m => {
    try {
      const hasBs =
        !!document.querySelector('link[href*="bootstrap"]') &&
        !!window.bootstrap?.Toast;
      if (hasBs) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
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

  const fm = document.getElementById("journalEntry-edit-form");
  if (!fm) return;

  fm.addEventListener("submit", e => {
    const url = (
      fm.getAttribute("data-url") ||
      fm.getAttribute("action") ||
      "#"
    ).trim();
    if (!url || url === "#") {
      e.preventDefault();
      toast(fm.getAttribute("data-guard-msg") || "Route unavailable.");
    }
  });

  const cancelBtn = document.querySelector('.modal-footer [value="Cancel"]');
  if (cancelBtn) {
    cancelBtn.addEventListener("click", e => {
      const url = (cancelBtn.getAttribute("data-index-url") || "#").trim();
      if (!url || url === "#") {
        e.preventDefault();
        toast(cancelBtn.getAttribute("data-guard-msg") || "Route unavailable.");
      }
    });
  }
})();
