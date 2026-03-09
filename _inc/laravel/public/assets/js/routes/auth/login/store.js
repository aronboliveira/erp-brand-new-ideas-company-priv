(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;

  const scheduleError =
    guard?.scheduleError?.bind(guard) ??
    (msg => {
      try {
        const c =
          document.getElementById("erp-toast-container") ||
          (() => {
            const d = document.createElement("div");
            d.id = "erp-toast-container";
            d.className = "toast-container position-fixed top-0 end-0 p-3";
            d.style.zIndex = "1100";
            document.body.appendChild(d);
            return d;
          })();
        if (window.bootstrap?.Toast) {
          const t = document.createElement("div");
          t.className = "toast fade";
          t.setAttribute("role", "alert");
          t.innerHTML = `<div class="toast-header bg-danger text-white"><strong class="me-auto">Notice</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button></div><div class="toast-body">${msg}</div>`;
          c.appendChild(t);
          new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
          t.addEventListener("hidden.bs.toast", () => t.remove());
        } else {
          alert(msg);
        }
      } catch (_) {
        alert(msg);
      }
    });

  const getMsg = utils?.getMsg?.bind(utils) ?? (key => null);

  try {
    const fm = document.getElementById("loginForm");
    if (!fm) {
      return;
    }
    if (fm.getAttribute("data-listener-active") === "true") {
      return;
    }
    fm.setAttribute("data-listener-active", "true");
    fm.addEventListener("submit", e => {
      try {
        const action = fm.getAttribute("action") ?? "#";
        const url = fm.getAttribute("data-url") ?? action ?? "#";
        // Only block if BOTH action and data-url are invalid (# or empty)
        if (action && action !== "#") {
          return; // valid action — let submit proceed
        }
        if (url && url !== "#") {
          return; // valid data-url — let submit proceed
        }
        e.preventDefault();
        const msg =
          fm.getAttribute("data-guard-msg") ||
          getMsg("login_submit_unavailable") ||
          "Login submit route is unavailable.";
        scheduleError(msg, "submit");
        fm.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
    const pwd = document.getElementById("password-request-link");
    if (pwd && pwd.getAttribute("data-listener-active") !== "true") {
      pwd.setAttribute("data-listener-active", "true");
      pwd.addEventListener("click", e => {
        try {
          const href = pwd.getAttribute("href") ?? "#";
          const url = pwd.getAttribute("data-url") ?? href ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            pwd.getAttribute("data-guard-msg") ||
            getMsg("password_request_unavailable");
          scheduleError(msg, "click");
          pwd.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
    const reg = document.getElementById("register-link");
    if (reg && reg.getAttribute("data-listener-active") !== "true") {
      reg.setAttribute("data-listener-active", "true");
      reg.addEventListener("click", e => {
        try {
          const href = reg.getAttribute("href") ?? "#";
          const url = reg.getAttribute("data-url") ?? href ?? "#";
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            reg.getAttribute("data-guard-msg") ||
            getMsg("register_link_unavailable");
          scheduleError(msg, "click");
          reg.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();
