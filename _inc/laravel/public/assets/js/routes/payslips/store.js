(function () {
  function toast(msg) {
    try {
      let c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      if (window.bootstrap && window.bootstrap.Toast) {
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

  try {
    const f = document.getElementById("create_employee");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") || "#";
    if (
      (f.getAttribute("action") === "" || f.getAttribute("action") === "#") &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    f.addEventListener("submit", function (e) {
      try {
        const action = f.getAttribute("action") || "#";
        if (action && action !== "#") return;
        e.preventDefault();
        const msg =
          f.getAttribute("data-guard-msg") ||
          "Requested route is unavailable. Please contact technical support or your domain administrator.";
        toast(msg);
        f.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  } catch (_) {}
})();
