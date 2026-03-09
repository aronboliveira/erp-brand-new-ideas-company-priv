(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const a = document.querySelector(".auth-forgot-link");
    if (!a) return;
    if (a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");

    const url = a.getAttribute("data-url") ?? "#";
    if (
      a.hasAttribute("href") &&
      (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
      url !== "#"
    ) {
      a.setAttribute("href", url);
    }

    a.addEventListener("click", e => {
      try {
        const href = a.getAttribute("href") ?? "#";
        if (href && href !== "#") return;
        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ||
          getMsg("forgot_password_unavailable");
        scheduleError(msg, "click");
        a.setAttribute("data-failed-route", "true");
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/auth/forgotPasswordLink.js] Click handler error:",
            err?.constructor?.name ?? "Error",
            err?.message ?? "Unknown error",
          );
      }
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/auth/forgotPasswordLink.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error",
      );
  }
})();
