(() => {
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".email-template-toggle").forEach(function (el) {
      if (el.dataset.guardBound === "1") return;
      el.dataset.guardBound = "1";
      el.addEventListener("change", function (e) {
        const url = el.getAttribute("data-url") || "#";
        if (url !== "#") return;
        e.preventDefault();
        el.checked = !el.checked;
        var msg = el.getAttribute("data-guard-msg") || "";
        var hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        var container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
          var toast = document.createElement("div");
          toast.className = "toast";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          var body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toast.appendChild(body);
          container.appendChild(toast);
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
      });
    });
  });
})();
