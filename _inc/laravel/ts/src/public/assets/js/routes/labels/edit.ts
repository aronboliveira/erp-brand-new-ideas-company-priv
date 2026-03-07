/**
 * @fileoverview TypeScript version of public/assets/js/routes/labels/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */


((): void => {
  const DEFAULT_MSG =
    "The requested route is unavailable. Please contact technical support or your domain administrator.";

  const showError = (message: string): void=> {
    try {
      const hasBootstrapToast = !!window.bootstrap.Toast;
      if (hasBootstrapToast) {
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
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
        {
          const _b = document.createElement("div");
          _b.className = "toast-body";
          _b.textContent = message;
          toastEl.replaceChildren(_b);
        }
        container.appendChild(toastEl);
        setTimeout((): void => {
          toastEl.remove();
        }, 4000);
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };

  const attachGuard = (form: HTMLFormElement | null): void=> {
    if (!form) return;
    const guardMsg = form.getAttribute("data-guard-msg") || DEFAULT_MSG;

    form.addEventListener("submit", (e: Event) => {
      const action = (form.getAttribute("action") ?? "").trim();
      if (!action || action === "#") {
        e.preventDefault();
        showError(guardMsg);
      }
    });
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    // Primary target by id
    const mainForm = document.getElementById(
      "label-edit-form",
    ) as HTMLFormElement | null;
    attachGuard(mainForm);

    // Fallback: any form with a guard message
    document
      .querySelectorAll("form[data-guard-msg]")
      .forEach(f => f !== mainForm && attachGuard(f as HTMLFormElement));
  });
})();

export {};
