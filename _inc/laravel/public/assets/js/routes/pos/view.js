(function () {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  if (!guard) return;

  try {
    guard.bindClickGuard("a[data-guard-msg], a[data-url]", {
      fallbackMsg: "# ERROR",
      validateUrl: true,
      updateHref: true,
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to initialize detailGuards");
    } catch (__) {}
  }
})();
