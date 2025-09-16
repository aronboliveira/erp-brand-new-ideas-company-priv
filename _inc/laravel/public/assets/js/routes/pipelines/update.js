(() => {
  const form = document.getElementById("pipeline-update-form");
  if (!form) return;

  const showToast = msg => {
    try {
      if (window.bootstrap?.Toast) {
        const container =
          document.getElementById("toast-container") ||
          (() => {
            const c = document.createElement("div");
            c.id = "toast-container";
            document.body.appendChild(c);
            return c;
          })();
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        t.appendChild(body);
        container.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  form.addEventListener(
    "submit",
    e => {
      const url =
        form.getAttribute("action") || form.getAttribute("data-url") || "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          form.getAttribute("data-guard-msg") ||
          "Update Pipeline route is unavailable. Please contact technical support or your domain administrator.";
        showToast(msg);
      }
    },
    { passive: false }
  );
})();
