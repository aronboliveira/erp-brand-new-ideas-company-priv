(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

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
          fm.getAttribute("data-guard-msg") ||
          getMsg("product_csv_import_unavailable");
        scheduleError(msg, "submit");
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
