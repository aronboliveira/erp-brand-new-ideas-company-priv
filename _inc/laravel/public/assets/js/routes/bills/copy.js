(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  if (!guard) return;

  const showError = key => {
    const utils = window.ERPUtils;
    if (!utils) return;
    const msg = utils.getTranslation(key) || "# ERROR";
    guard.showToast(msg);
  };

  try {
    document.querySelectorAll(".copy_link").forEach(el => {
      if (el.dataset.copyListener) return;
      el.dataset.copyListener = "true";
      el.addEventListener("click", e => {
        e.preventDefault();
        const href = el.getAttribute("href") ?? "";
        if (!href) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("No href to copy");
          showError("copy_link_unavailable");
          return;
        }
        try {
          const onCopy = evt => {
            evt.clipboardData.setData("text/plain", href);
            evt.preventDefault();
          };
          document.addEventListener("copy", onCopy, true);
          const success = document.execCommand("copy");
          document.removeEventListener("copy", onCopy, true);
          if (!success) throw new Error("execCommand returned false");
          show_toastr(
            "success",
            window.translations?.["en"]?.copy_link_success || "Link copied",
            "success",
          );
        } catch (err) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("Copy command failed:", err);
          showError("copy_link_unavailable");
        }
      });
    });
  } catch (err) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Failed to bind copy_link handlers:", err);
  }
})();
