(() => {
  try {
    const fm = document.getElementById("prd-sv-import-form");
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", e => {
      try {
        const action = (fm.getAttribute("action") ?? "#").trim();
        const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        const msg =
          fm.getAttribute("data-guard-msg") ??
          "Product CSV import route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
        );
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
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
        fm.setAttribute("data-failed-route", "true");
      } catch {}
    });
  } catch {}

  try {
    const inp = document.getElementById("file");
    if (!inp) return;
    if (inp.getAttribute("data-filename-guarded") === "true") return;
    inp.setAttribute("data-filename-guarded", "true");
    inp.addEventListener("change", () => {
      try {
        const sel = inp.getAttribute("data-filename") || "";
        const out = sel ? document.querySelector("." + sel) : null;
        if (!out) return;
        const file = inp.files && inp.files[0] ? inp.files[0] : null;
        out.textContent = file ? file.name : "";
      } catch {}
    });
  } catch {}
})();
