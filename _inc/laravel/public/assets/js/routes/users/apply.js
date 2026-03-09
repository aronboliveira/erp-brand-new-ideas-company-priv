(() => {
  if (typeof window !== "undefined" && window.ERPGuard) {
    const guard = window.ERPGuard;

    try {
      const f = document.getElementById("user_userlog");
      if (!f || f.getAttribute("data-listener-active") === "true") return;
      f.setAttribute("data-listener-active", "true");

      const resolved = f.getAttribute("data-resolved-action") || "#";
      if (
        (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
        resolved !== "#"
      ) {
        f.setAttribute("action", resolved);
      }

      const apply = document.getElementById("userlog-apply-btn");
      if (apply && apply.getAttribute("data-listener-active") !== "true") {
        apply.setAttribute("data-listener-active", "true");
        apply.addEventListener("click", e => {
          e.preventDefault();
          const action = f.getAttribute("action") || "#";
          if (action && action !== "#") {
            f.submit();
            return;
          }
          const msg =
            f.getAttribute("data-guard-msg") ||
            "User logs route is unavailable. Please contact technical support or your domain administrator.";
          guard.showToast(msg);
          } catch {
            alert(msg);
          }
        } else {
          alert(msg);
        }
        f.setAttribute("data-failed-route", "true");
      });
    }
  } catch {}
})();
