(() => {
  const form = document.getElementById("xendit-payment-form");
  if (!form || form.getAttribute("data-listener-active") === "true") return;
  form.setAttribute("data-listener-active", "true");

  form.addEventListener("submit", event => {
    try {
      const url = form.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      event.preventDefault();
      const msg = form.getAttribute("data-guard-msg") ?? "# ERROR";
      const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        document.body.appendChild(container);
      }
      if (bootstrapLink && window.bootstrap) {
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
      form.setAttribute("data-failed-route", "true");
    } catch {}
  });
})();
