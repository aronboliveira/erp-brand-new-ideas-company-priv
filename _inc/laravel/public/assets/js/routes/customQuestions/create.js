(() => {
  const btn = document.getElementById("custom-question-create-btn");
  if (!btn || btn.getAttribute("data-listener-active") === "true") return;
  btn.setAttribute("data-listener-active", "true");

  btn.addEventListener("click", e => {
    try {
      const url = btn.getAttribute("data-url") || "#";
      if (url !== "#") return; // valid route, proceed with AJAX popup

      e.preventDefault();
      const msg = btn.getAttribute("data-guard-msg") || "# ERROR";
      const bsLink = document.querySelector('link[href*="bootstrap"]');
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        document.body.appendChild(container);
      }
      if (bsLink && window.bootstrap) {
        const toastEl = document.createElement("div");
        toastEl.className = "toast";
        toastEl.setAttribute("role", "alert");
        toastEl.setAttribute("aria-live", "assertive");
        toastEl.setAttribute("aria-atomic", "true");
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
    } catch (err) {}
  });
})();
