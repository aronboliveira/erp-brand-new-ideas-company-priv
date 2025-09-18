(() => {
  const wrapper = document.querySelector(
    '[data-listener-alias="qrcode-copy-link"]'
  );
  if (!wrapper || wrapper.getAttribute("data-listener-active") === "true")
    return;
  wrapper.setAttribute("data-listener-active", "true");
  wrapper.addEventListener("click", event => {
    try {
      const url = wrapper.getAttribute("data-url") ?? "#";
      if (url !== "#") return;
      event.preventDefault();
      const msg = wrapper.getAttribute("data-guard-msg") ?? "# ERROR";
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
      wrapper.setAttribute("data-failed-route", "true");
    } catch (e) {}
  });
})();
