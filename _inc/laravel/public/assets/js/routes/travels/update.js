(function () {
  try {
    var form = document.getElementById("edit_travel");
    if (!form) return;
    if (form.getAttribute("data-listener-active") === "true") return;
    form.setAttribute("data-listener-active", "true");

    form.addEventListener("submit", function (e) {
      try {
        var action = form.getAttribute("action") || "#";
        if (!action || action === "#") {
          e.preventDefault();
          var msg =
            form.getAttribute("data-guard-msg") ||
            "Update travel route is unavailable. Please contact technical support or your domain administrator.";
          var container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
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
          return;
        }

        var sd = form.querySelector('input[name="start_date"]');
        var ed = form.querySelector('input[name="end_date"]');
        if (sd && ed && sd.value && ed.value) {
          var s = new Date(sd.value);
          var en = new Date(ed.value);
          if (en < s) {
            e.preventDefault();
            var m = "End Date cannot be earlier than Start Date.";
            var c = document.getElementById("toast-container");
            if (!c) {
              c = document.createElement("div");
              c.id = "toast-container";
              document.body.appendChild(c);
            }
            var bs2 =
              typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
            if (bs2 && bs2.Toast) {
              var t2 = document.createElement("div");
              t2.className = "toast";
              t2.setAttribute("role", "alert");
              t2.setAttribute("aria-live", "assertive");
              t2.setAttribute("aria-atomic", "true");
              var b2 = document.createElement("div");
              b2.className = "toast-body";
              b2.textContent = m;
              t2.appendChild(b2);
              c.appendChild(t2);
              bs2.Toast.getOrCreateInstance(t2).show();
            } else {
              alert(m);
            }
          }
        }
      } catch {}
    });
  } catch {}
})();
