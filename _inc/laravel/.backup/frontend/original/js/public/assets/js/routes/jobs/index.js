(() => {
  const QA = s => Array.from(document.querySelectorAll(s));
  const T = window.JOBS_I18N || {};
  const DEFAULT_ROUTE_MSG =
    T.routeUnavailable ||
    "Requested route is unavailable. Please contact technical support or your domain administrator.";
  const COPIED = T.copySuccess || "Link copied to clipboard";
  const COPY_FAIL = T.copyFail || "Failed to copy link";

  const toast = message => {
    const text = message || DEFAULT_ROUTE_MSG;
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      window.bootstrap
    );
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      box.style.position = "fixed";
      box.style.top = "1rem";
      box.style.right = "1rem";
      box.style.zIndex = "1060";
      document.body.appendChild(box);
    }
    if (hasBs) {
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
  };

  const bindLinkGuard = el => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      const href = (el.getAttribute("href") ?? "#").trim();
      const url = (el.getAttribute("data-url") ?? href ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(el.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      el.setAttribute("data-failed-route", "true");
    });
  };

  const bindFormGuard = fm => {
    if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", e => {
      const action = (fm.getAttribute("action") ?? "#").trim();
      const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      toast(fm.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      fm.setAttribute("data-failed-route", "true");
    });
  };

  const initTooltips = () => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  };

  const copyToClipboard = text =>
    navigator.clipboard
      ? navigator.clipboard.writeText(text)
      : Promise.reject();

  const bindCopy = () => {
    QA("a.copy-link").forEach(a => {
      if (a.getAttribute("data-copy-bound") === "true") return;
      a.setAttribute("data-copy-bound", "true");
      a.addEventListener("click", e => {
        const href = (a.getAttribute("href") ?? "#").trim();
        const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
        if (url === "#" || href === "#") {
          e.preventDefault();
          toast(a.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
          return;
        }
        e.preventDefault();
        copyToClipboard(url)
          .then(() => toast(COPIED))
          .catch(() => toast(COPY_FAIL));
      });
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    QA("a.route-guard, a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
    QA("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
    bindCopy();
    initTooltips();
  });
})();
