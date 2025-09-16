(function () {
  const listened = "data-listener-active";
  function toast(message) {
    const text = message || "Requested route is unavailable.";
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      window.bootstrap
    );
    if (hasBs) {
      let box = document.getElementById("toast-container");
      if (!box) {
        box = document.createElement("div");
        box.id = "toast-container";
        document.body.appendChild(box);
      }
      const t = document.createElement("div");
      t.className = "toast";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      const b = document.createElement("div");
      b.className = "toast-body";
      b.textContent = text;
      t.appendChild(b);
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(text);
    }
  }
  function guardLink(a) {
    if (!a || a.getAttribute(listened) === "true") return;
    a.setAttribute(listened, "true");
    a.addEventListener("click", function (e) {
      const href = (a.getAttribute("href") || "#").trim();
      const url = (a.getAttribute("data-url") || href || "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(a.getAttribute("data-guard-msg") || "");
    });
  }
  function guardForm(f) {
    if (!f || f.getAttribute(listened) === "true") return;
    f.setAttribute(listened, "true");
    f.addEventListener("submit", function (e) {
      const action = (f.getAttribute("action") || "#").trim();
      const url = (f.getAttribute("data-url") || action || "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      toast(f.getAttribute("data-guard-msg") || "");
    });
  }
  function init() {
    document
      .querySelectorAll("a[data-guard-msg],a[data-url]")
      .forEach(guardLink);
    document
      .querySelectorAll("form[data-guard-msg],form[data-url]")
      .forEach(guardForm);
    try {
      document
        .querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(function (el) {
          try {
            bootstrap.Tooltip.getOrCreateInstance(el);
          } catch (_) {}
        });
    } catch (_) {}
  }
  document.addEventListener("DOMContentLoaded", function () {
    init();
    const mo = new MutationObserver(function () {
      init();
    });
    mo.observe(document.body, { childList: true, subtree: true });
  });
})();
