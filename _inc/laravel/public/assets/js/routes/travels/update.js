(function () {
  try {
    let form = document.getElementById("edit_travel");
    if (!form) return;
    if (form.getAttribute("data-listener-active") === "true") return;
    form.setAttribute("data-listener-active", "true");

    form.addEventListener("submit", function (e) {
      try {
        let action = form.getAttribute("action") || "#";
        if (!action || action === "#") {
          e.preventDefault();
          let msg =
            form.getAttribute("data-guard-msg") ||
            "Update travel route is unavailable. Please contact technical support or your domain administrator.";
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className =
              "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
          }
          let bs =
            typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
          if (bs && bs.Toast) {
            let t = document.createElement("div");
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            let b = document.createElement("div");
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

        let sd = form.querySelector('input[name="start_date"]');
        let ed = form.querySelector('input[name="end_date"]');
        if (sd && ed && sd.value && ed.value) {
          let s = new Date(sd.value);
          let en = new Date(ed.value);
          if (en < s) {
            e.preventDefault();
            let m = "End Date cannot be earlier than Start Date.";
            let c = document.getElementById("toast-container");
            if (!c) {
              c = document.createElement("div");
              c.id = "toast-container";
              document.body.appendChild(c);
            }
            let bs2 =
              typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
            if (bs2 && bs2.Toast) {
              let t2 = document.createElement("div");
              t2.className = "toast";
              t2.setAttribute("role", "alert");
              t2.setAttribute("aria-live", "assertive");
              t2.setAttribute("aria-atomic", "true");
              let b2 = document.createElement("div");
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
