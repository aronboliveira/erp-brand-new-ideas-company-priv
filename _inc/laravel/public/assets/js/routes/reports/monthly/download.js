(() => {
  try {
    document.querySelectorAll(".download-pdf-link").forEach(el => {
      try {
        const alias = "data-listening-downloadpdfclick";
        if (!el.hasAttribute(alias)) {
          el.setAttribute(alias, "true");
          el.addEventListener("click", event => {
            try {
              const url = el.getAttribute("data-url");
              const href = el.getAttribute("href") ?? "";
              if ((url && url !== "#") || (href && href !== "#")) return;
              event.preventDefault();
              const funcName = el.getAttribute("data-func-name") ?? "";
              const fn = window[funcName];
              if (typeof fn !== "function") {
                const msg =
                  el.getAttribute("data-guard-msg") ??
                  "Download function for monthly reports is unavailable. Please contact technical support or your domain administrator.";
                const hasBS = Array.from(document.scripts).some(
                  s =>
                    (s.src ?? "").includes("bootstrap.min.js") &&
                    window.bootstrap &&
                    typeof bootstrap.Toast === "function"
                );
                if (hasBS) {
                  const container =
                    document.getElementById("toast-container") ??
                    (() => {
                      const d = document.createElement("div");
                      d.id = "toast-container";
                      d.className =
                        "toast-container position-fixed bottom-0 end-0 p-3";
                      document.body.appendChild(d);
                      return d;
                    })();
                  const toastEl = document.createElement("div");
                  toastEl.className =
                    "toast align-items-center text-bg-danger border-0";
                  toastEl.setAttribute("role", "alert");
                  toastEl.setAttribute("aria-live", "assertive");
                  toastEl.setAttribute("aria-atomic", "true");
                  toastEl.innerHTML =
                    '<div class="d-flex"><div class="toast-body">' +
                    msg +
                    '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
                  container.appendChild(toastEl);
                  new bootstrap.Toast(toastEl, { delay: 5000 }).show();
                } else {
                  alert(msg);
                }
                return;
              }
              fn();
            } catch {}
          });
        }
      } catch {}
    });
  } catch {}
})();
