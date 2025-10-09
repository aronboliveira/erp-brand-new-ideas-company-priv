(() => {
  const link = document.getElementById("breadcrumb-invoice-link");
  if (!link || link.getAttribute("data-listener-active") === "true") return;
  link.setAttribute("data-listener-active", "true");
  link.addEventListener("click", event => {
    try {
      const url = link.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      event.preventDefault();

      const msg = link.getAttribute("data-guard-msg") ?? "# ERROR";
      const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
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

      link.setAttribute("data-failed-route", "true");
    } catch (e) {}
  });
})();
