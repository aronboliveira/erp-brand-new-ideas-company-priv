(function () {
  try {
    var a = document.getElementById("transfer-create-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    var url = a.getAttribute("data-url") || "#";
    if (
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    )
      a.setAttribute("href", url);
    a.addEventListener("click", function (e) {
      try {
        var href = a.getAttribute("href") || "#";
        if (href !== "#") return;
        e.preventDefault();
        var msg =
          a.getAttribute("data-guard-msg") ||
          "Create transfer route is unavailable. Please contact technical support or your domain administrator.";
        var container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }
        var bs =
          typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
        if (bs && bs.Toast) {
          var t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          var b = document.createElement("div");
          b.className = "toast-body";
          b.textContent = msg;
          t.appendChild(b);
          container.appendChild(t);
          bs.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
        a.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();
