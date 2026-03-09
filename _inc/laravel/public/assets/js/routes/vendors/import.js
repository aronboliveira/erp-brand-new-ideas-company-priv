(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const f = document.getElementById("vendor-import-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") || "#";
    if (
      (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    f.addEventListener("submit", e => {
      const action = f.getAttribute("action") || "#";
      if (action && action !== "#") return;
      e.preventDefault();

      const msg =
        f.getAttribute("data-guard-msg") || getMsg("import_vendor_unavailable");
      scheduleError(msg, "submit");
      f.setAttribute("data-failed-route", "true");
    });

    const fileInput = document.getElementById("file");
    if (fileInput) {
      fileInput.addEventListener("change", () => {
        const target = document.querySelector(
          "." + (fileInput.getAttribute("data-filename") || "upload_file"),
        );
        if (target) target.textContent = fileInput.files?.[0]?.name || "";
      });
    }
  } catch {}
})();
