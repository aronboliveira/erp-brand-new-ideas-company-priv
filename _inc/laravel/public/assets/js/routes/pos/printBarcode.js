(() => {
  const toast = msg => {
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

  const bindGuard = el => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      const url = el.getAttribute("href") || el.getAttribute("data-url") || "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg = el.getAttribute("data-guard-msg") || "Action unavailable.";
        toast(msg);
      }
    });
  };

  bindGuard(document.getElementById("pos-print"));
  bindGuard(document.getElementById("pos-setting"));
})();

(function () {
  function toast(msg) {
    try {
      var c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      if (window.bootstrap && window.bootstrap.Toast) {
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

  try {
    var links = document.querySelectorAll(
      'a[data-guard-msg]:not([data-listener-active="true"])'
    );
    links.forEach(function (a) {
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", function (e) {
        var url = a.getAttribute("href") || a.getAttribute("data-url") || "#";
        if (!url || url === "#") {
          e.preventDefault();
          var msg = a.getAttribute("data-guard-msg") || "Action unavailable.";
          toast(msg);
        }
      });
    });
  } catch (_) {}
})();
