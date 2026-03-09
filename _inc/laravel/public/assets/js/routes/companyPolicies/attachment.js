(() => {
  const el = document.getElementById("attachment");
  if (!el || el.getAttribute("data-listener-active") === "true") return;
  el.setAttribute("data-listener-active", "true");

  el.addEventListener("change", function (e) {
    try {
      const file = e.target.files?.[0];
      if (!file) return;
      const img = document.getElementById("image");
      if (!img) throw new Error("noIMG");
      img.src = URL.createObjectURL(file);
    } catch {
      const guard = window.ERPGuard;
      if (guard) {
        guard.showToast(
          el.getAttribute("data-guard-msg") || "Preview failed",
          "error",
        );
      }
    }
  });
})();
