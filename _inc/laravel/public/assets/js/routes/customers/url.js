(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  if (!guard || !utils) return;

  let errorMessage = "";

  const getLocalizedMessage = (key, el) => {
    return utils.getTranslation(key) || "# ERROR";
  };

  const showToast = (message, isError = false) => {
    guard.showToast(message);
  };

  const onPointerUp = () => {
    if (errorMessage) {
      showToast(errorMessage, true);
      errorMessage = "";
    }
  };

  document.addEventListener("pointerup", onPointerUp);

  new MutationObserver((m, obs) => {
    m.forEach(mut =>
      Array.from(mut.removedNodes).forEach(node => {
        if (node === document.documentElement) {
          document.removeEventListener("pointerup", onPointerUp);
          obs.disconnect();
        }
      }),
    );
  }).observe(document.body, { childList: true, subtree: true });

  window.copyToClipboard = element => {
    try {
      const text = element?.id ?? "";
      if (!navigator.clipboard) throw new Error("url_copy_failed");
      navigator.clipboard
        .writeText(text)
        .then(() => {
          const msg = getLocalizedMessage("url_copy_success", element);
          showToast(msg);
        })
        .catch(() => {
          throw new Error("url_copy_failed");
        });
    } catch (e) {
      errorMessage = getLocalizedMessage(e.message, element || document.body);
    }
  };
})();
