/**
 * @fileoverview TypeScript version of public/assets/js/routes/attendances/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

// assets/js/routes/employeeAttendance/index.js
((): void => {
  try {
    const form = document.getElementById("employeeAttendance_filter");
    const alias = "data-listening-employeeattendancefilter";
    if (form && !form.hasAttribute(alias)) {
      form.setAttribute(alias, "true");
      form.addEventListener("submit", event => {
        try {
          const url =
            form.getAttribute("data-url") ?? form.getAttribute("data-url");
          if (url !== "#" || (form as HTMLFormElement).action !== "#") return;
          event.preventDefault();
          const msg =
            form.getAttribute("data-guard-msg") ??
            "Employee attendance index route is unavailable. Please contact technical support or your domain administrator.";
          const hasBS = Array.from(document.scripts).some(
            s =>
              s.src.includes("bootstrap.min.js") &&
              window.bootstrap &&
              typeof window.bootstrap.Toast === "function",
          );
          if (hasBS) {
            const container =
              document.getElementById("toast-container") ??
              ((): HTMLDivElement => {
                const c = document.createElement("div");
                c.id = "toast-container";
                c.className =
                  "toast-container position-fixed bottom-0 end-0 p-3";
                document.body.appendChild(c);
                return c;
              })();
            const toastEl = document.createElement("div");
            toastEl.className =
              "toast align-items-center text-bg-danger border-0";
            for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);
            toastEl.innerHTML =
              '<div class="d-flex"><div class="toast-body">' +
              msg +
              '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
            container.appendChild(toastEl);
            new bootstrap.Toast(toastEl, { delay: 5000 }).show();
          } else {
            alert(msg);
          }
        } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
      });
    }
  } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
  try {
    const selector = ".reset-employee-attendance-link";
    const alias2 = "data-listening-resetemployeeattendanceclick";
    document.querySelectorAll(selector).forEach((el: Element): void => {
      try {
        if (!el.hasAttribute(alias2)) {
          el.setAttribute(alias2, "true");
          el.addEventListener("click", event => {
            try {
              const url = el.getAttribute("data-url");
              const href = el.getAttribute("href") ?? "";
              if ((url && url !== "#") || (href && href !== "#")) return;
              event.preventDefault();
              const msg =
                el.getAttribute("data-guard-msg") ??
                "Employee attendance index route is unavailable. Please contact technical support or your domain administrator.";
              const hasBS = Array.from(document.scripts).some(
                s =>
                  s.src.includes("bootstrap.min.js") &&
                  window.bootstrap &&
                  typeof window.bootstrap.Toast === "function",
              );
              if (hasBS) {
                const container =
                  document.getElementById("toast-container") ??
                  ((): HTMLDivElement => {
                    const c = document.createElement("div");
                    c.id = "toast-container";
                    c.className =
                      "toast-container position-fixed bottom-0 end-0 p-3";
                    document.body.appendChild(c);
                    return c;
                  })();
                const toastEl = document.createElement("div");
                toastEl.className =
                  "toast align-items-center text-bg-danger border-0";
                for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);
                toastEl.innerHTML =
                  '<div class="d-flex"><div class="toast-body">' +
                  msg +
                  '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
                container.appendChild(toastEl);
                new bootstrap.Toast(toastEl, { delay: 5000 }).show();
              } else {
                alert(msg);
              }
            } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
          });
        }
      } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
    });
  } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
})();
