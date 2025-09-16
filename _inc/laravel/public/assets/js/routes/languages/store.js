(() => {
  const listenerAttr = "data-language-create-listener-active";
  const form = document.getElementById("language-create-form");
  if (!form || form.getAttribute(listenerAttr) === "true") return;
  form.setAttribute(listenerAttr, "true");

  form.addEventListener("submit", event => {
    try {
      const action = form.getAttribute("action");
      if (!action || action === "#") {
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
      }
    } catch (error) {}
  });

  const observer = new MutationObserver(() => {
    if (!document.getElementById("language-create-form")) observer.disconnect();
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();
