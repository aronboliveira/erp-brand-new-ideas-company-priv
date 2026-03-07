/**
 * @fileoverview TypeScript version of public/assets/js/routes/events/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

/* global bootstrap */
const showToast = (m: string): void=> {
  try {
    if (window.bootstrap.Toast) {
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
      const toastBody = t.querySelector(".toast-body");
      if (toastBody) toastBody.textContent = m;
      c.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t, { delay: 4000 }).show();
      return;
    }
  } catch (_) {}
  alert(m);
};
const form = document.getElementById("edit_event_form");
if (form && form.getAttribute("data-listener-active") !== "true") {
  form.setAttribute("data-listener-active", "true");
  form.addEventListener(
    "submit",
    (e: Event) => {
      const a =
        form.getAttribute("action") ?? form.getAttribute("data-action-url");
      if (!a || a === "#") {
        e.preventDefault();
        showToast(form.getAttribute("data-guard-msg") ?? "#");
      }
    },
    { passive: false },
  );
}
