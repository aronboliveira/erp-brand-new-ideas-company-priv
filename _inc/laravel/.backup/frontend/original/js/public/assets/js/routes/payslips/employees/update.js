(function () {
  try {
    const f = document.getElementById("update_employee_form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") || "#";
    if (
      (f.getAttribute("action") === "" || f.getAttribute("action") === "#") &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    function notify(msg) {
      try {
        if (window.bootstrap && window.bootstrap.Toast) {
          const c =
            document.getElementById("toast-container") ||
            (function () {
              const d = document.createElement("div");
              d.id = "toast-container";
              document.body.appendChild(d);
              return d;
            })();
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
          window.bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
      } catch (_) {
        alert(msg);
      }
    }

    f.addEventListener("submit", function (e) {
      try {
        const action = f.getAttribute("action") || "#";
        if (action && action !== "#") return;
        e.preventDefault();
        const msg =
          f.getAttribute("data-guard-msg") ||
          "Requested route is unavailable. Please contact technical support or your domain administrator.";
        notify(msg);
        f.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  } catch (_) {}
})();
