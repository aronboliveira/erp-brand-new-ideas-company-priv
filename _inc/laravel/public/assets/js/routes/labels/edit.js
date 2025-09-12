(() => {
  const DEFAULT_MSG =
    "The requested route is unavailable. Please contact technical support or your domain administrator.";

  const showError = message => {
    try {
      const hasBootstrapToast = !!window.bootstrap?.Toast;
      if (hasBootstrapToast) {
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.style.position = "fixed";
          container.style.top = "1rem";
          container.style.right = "1rem";
          container.style.zIndex = "2000";
          document.body.appendChild(container);
        }
        const toastEl = document.createElement("div");
        toastEl.className = "toast show";
        toastEl.setAttribute("role", "alert");
        toastEl.setAttribute("aria-live", "assertive");
        toastEl.setAttribute("aria-atomic", "true");
        toastEl.style.minWidth = "280px";
        toastEl.innerHTML = `<div class="toast-body">${message}</div>`;
        container.appendChild(toastEl);
        setTimeout(() => toastEl.remove(), 4000);
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };

  const attachGuard = form => {
    if (!form) return;
    const guardMsg = form.getAttribute("data-guard-msg") || DEFAULT_MSG;

    form.addEventListener("submit", e => {
      const action = (form.getAttribute("action") || "").trim();
      if (!action || action === "#") {
        e.preventDefault();
        showError(guardMsg);
      }
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    // Primary target by id
    const mainForm = document.getElementById("label-edit-form");
    attachGuard(mainForm);

    // Fallback: any form with a guard message
    document
      .querySelectorAll("form[data-guard-msg]")
      .forEach(f => f !== mainForm && attachGuard(f));
  });
})();
