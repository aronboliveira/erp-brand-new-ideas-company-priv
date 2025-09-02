(function () {
  try {
    var f = document.getElementById("update_employee_form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    var resolved = f.getAttribute("data-resolved-action") || "#";
    if (
      (f.getAttribute("action") === "" || f.getAttribute("action") === "#") &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    function notify(msg) {
      try {
        if (window.bootstrap && window.bootstrap.Toast) {
          var c =
            document.getElementById("toast-container") ||
            (function () {
              var d = document.createElement("div");
              d.id = "toast-container";
              document.body.appendChild(d);
              return d;
            })();
          var t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          var b = document.createElement("div");
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
        var action = f.getAttribute("action") || "#";
        if (action && action !== "#") return;
        e.preventDefault();
        var msg =
          f.getAttribute("data-guard-msg") ||
          "Requested route is unavailable. Please contact technical support or your domain administrator.";
        notify(msg);
        f.setAttribute("data-failed-route", "true");
      } catch (_) {}
    });
  } catch (_) {}
})();
