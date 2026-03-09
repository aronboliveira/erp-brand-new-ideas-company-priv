/**
 * @fileoverview TypeScript version of public/assets/js/routes/customQuestions/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */

((): void => {
  const btn = document.getElementById("custom-question-create-btn");
  if (!btn || btn.getAttribute("data-listener-active") === "true") return;
  btn.setAttribute("data-listener-active", "true");

  if (!btn.getAttribute("data-listener-bound-click")) {
    btn.setAttribute("data-listener-bound-click", "1");
    btn.addEventListener("click", (e: Event) => {
      try {
        const url = btn.getAttribute("data-url") ?? "#";
        if (url !== "#") return; // valid route, proceed with AJAX popup

        e.preventDefault();
        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR",
          bsLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (bsLink && window.bootstrap) {
          const toastEl = document.createElement("div");
          toastEl.className = "toast";
          for (const [k, v] of Object.entries({
            role: "alert",
            "aria-live": "assertive",
            "aria-atomic": "true",
          }))
            toastEl.setAttribute(k, v);
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toastEl.appendChild(body);
          container.appendChild(toastEl);
          bootstrap.Toast.getOrCreateInstance(toastEl).show();
        } else {
          alert(msg);
        }
        btn.setAttribute("data-failed-route", "true");
      } catch (err) {
        console.error(`[create] Error:`, err);
      }
    });
  }
})();

export {};
