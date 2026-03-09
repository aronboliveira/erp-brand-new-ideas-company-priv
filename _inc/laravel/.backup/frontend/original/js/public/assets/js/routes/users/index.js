(() => {
  try {
    const attach = (id, fallbackMsg) => {
      const a = document.getElementById(id);
      if (!a || a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      const url = a.getAttribute("data-url") ?? "#";
      if (
        (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
        url !== "#"
      )
        a.setAttribute("href", url);
      a.addEventListener("click", e => {
        const href = a.getAttribute("href") ?? "#";
        if (href && href !== "#") return;
        e.preventDefault();
        const msg = a.getAttribute("data-guard-msg") || fallbackMsg;
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const hasBS =
          document.querySelector('link[href*="bootstrap"]') &&
          window.bootstrap &&
          window.bootstrap.Toast;
        if (hasBS) {
          const t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          const b = document.createElement("div");
          b.className = "toast-body";
          b.textContent = msg;
          t.appendChild(b);
          c.appendChild(t);
          try {
            window.bootstrap.Toast.getOrCreateInstance(t).show();
          } catch {
            alert(msg);
          }
        } else {
          alert(msg);
        }
        a.setAttribute("data-failed-route", "true");
      });
    };

    attach(
      "users-log-link",
      "User logs route is unavailable. Please contact technical support or your domain administrator."
    );
    attach(
      "user-create-link",
      "Create user route is unavailable. Please contact technical support or your domain administrator."
    );
  } catch {}
})();
