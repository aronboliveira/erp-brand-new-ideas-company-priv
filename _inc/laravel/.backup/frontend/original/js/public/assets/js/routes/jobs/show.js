(() => {
  const showMsg = msg => {
    try {
      if (window.bootstrap?.Toast) {
        const c =
          document.getElementById("toast-container") ||
          (() => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        el.appendChild(body);
        c.appendChild(el);
        window.bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  const link = document.getElementById("job-edit-link");
  if (!link) return;

  link.addEventListener(
    "click",
    e => {
      const url =
        link.getAttribute("href") || link.getAttribute("data-url") || "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg =
          link.getAttribute("data-guard-msg") ||
          "Edit Job route is unavailable. Please contact technical support or your domain administrator.";
        showMsg(msg);
      }
    },
    { passive: false }
  );
})();
