(() => {
  try {
    const a = document.getElementById("bc-expense-index-link");
    if (!a) {
      return;
    }
    if (a.getAttribute("data-listener-active") === "true") {
      return;
    }
    a.setAttribute("data-listener-active", "true");
    a.addEventListener("click", e => {
      try {
        const href = (a.getAttribute("href") ?? "#").trim();
        const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ??
          "Expense index route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
          const t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          const b = document.createElement("div");
          b.className = "toast-body";
          b.textContent = msg;
          t.appendChild(b);
          container.appendChild(t);
          bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
        a.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });
  } catch (err) {}
})();
