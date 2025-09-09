(() => {
  try {
    const fm = document.getElementById("bills-custom-debit-note-create-form");
    if (!fm) {
      return;
    }
    if (fm.getAttribute("data-submit-guarded") === "true") {
      return;
    }
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", e => {
      try {
        const action = fm.getAttribute("action") ?? "#";
        const url = fm.getAttribute("data-url") ?? action ?? "#";
        if (url !== "#" && action !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          fm.getAttribute("data-guard-msg") ??
          "Create custom debit note route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
          const toast = document.createElement("div");
          toast.className = "toast";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toast.appendChild(body);
          container.appendChild(toast);
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        fm.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();
