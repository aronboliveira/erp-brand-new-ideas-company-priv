(() => {
  const toast = m => {
    try {
      if (window.bootstrap && window.bootstrap.Toast) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          c.className = "position-fixed bottom-0 end-0 p-3";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast align-items-center text-bg-danger border-0";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        t.querySelector(".toast-body").textContent = m;
        c.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t, { delay: 4000 }).show();
        return;
      }
    } catch (_) {}
    alert(m);
  };
  const ai = document.getElementById("event-generate-ai-link");
  if (ai && ai.getAttribute("data-listener-active") !== "true") {
    ai.setAttribute("data-listener-active", "true");
    ai.addEventListener(
      "click",
      e => {
        const u = ai.getAttribute("data-url");
        if (!u || u === "#") {
          e.preventDefault();
          toast(ai.getAttribute("data-guard-msg") || "#");
        }
      },
      { passive: false }
    );
  }
})();
