(function () {
  try {
    let a = document.getElementById("transfer-create-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    let url = a.getAttribute("data-url") || "#";
    if (
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    )
      a.setAttribute("href", url);
    a.addEventListener("click", function (e) {
      try {
        let href = a.getAttribute("href") || "#";
        if (href !== "#") return;
        e.preventDefault();
        let msg =
          a.getAttribute("data-guard-msg") ||
          "Create transfer route is unavailable. Please contact technical support or your domain administrator.";
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
        a.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}
})();
