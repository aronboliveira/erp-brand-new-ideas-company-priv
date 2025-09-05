(function () {
  try {
    var f = document.getElementById("store_client");
    if (!f) return;

    var guardMsg = f.getAttribute("data-guard-msg") || "Route unavailable";
    var actionHref = f.getAttribute("data-action-href") || "";

    if (!f.getAttribute("action") && actionHref && actionHref !== "#") {
      f.setAttribute("action", actionHref);
    }

    function toastOrAlert(msg) {
      try {
        var hasBootstrap = !!(window.bootstrap && window.bootstrap.Toast);
        if (!hasBootstrap) {
          alert(msg);
          return;
        }
        var t = document.getElementById("route-guard-toast");
        if (!t) {
          t = document.createElement("div");
          t.id = "route-guard-toast";
          t.className =
            "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          t.innerHTML =
            '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
          document.body.appendChild(t);
        }
        var body = t.querySelector(".toast-body");
        if (body) body.textContent = msg;
        new window.bootstrap.Toast(t, { delay: 4000 }).show();
      } catch (e) {
        alert(msg);
      }
    }

    f.addEventListener("submit", function (e) {
      try {
        var a = f.getAttribute("action") || actionHref || "";
        if (!a || a === "#") {
          e.preventDefault();
          toastOrAlert(guardMsg);
        }
      } catch (_) {
        e.preventDefault();
        alert(guardMsg);
      }
    });
  } catch (_) {}
})();
