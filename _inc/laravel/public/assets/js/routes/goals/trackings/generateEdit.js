(() => {
  try {
    const guardToast = msg => {
      const text =
        msg ||
        "Requested route is unavailable. Please contact technical support or your domain administrator.";
      const hasBootstrap = !!(
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap
      );
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        document.body.appendChild(container);
      }
      if (hasBootstrap) {
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = text;
        t.appendChild(b);
        container.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(text);
      }
    };
    const aiBtn = document.getElementById("goal-ai-generate-btn");
    if (!aiBtn) return;
    if (aiBtn.getAttribute("data-listener-active") === "true") return;
    aiBtn.setAttribute("data-listener-active", "true");
    aiBtn.addEventListener("click", e => {
      try {
        const href = (aiBtn.getAttribute("href") ?? "#").trim();
        const url = (aiBtn.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        guardToast(aiBtn.getAttribute("data-guard-msg") || "");
        aiBtn.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();
