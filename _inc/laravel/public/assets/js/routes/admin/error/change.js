(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const showToastOrAlert = msg => {
    scheduleError(msg, "pointerup");
  };

  const previewBinder = (inputId, imgId) => {
    try {
      const input = document.getElementById(inputId);
      const img = document.getElementById(imgId);
      if (!input || !img)
        throw new Error(
          `${getMsg("element_unavailable")} (${!input ? inputId : imgId})`,
        );
      input.addEventListener("change", () => {
        try {
          const file = input.files && input.files[0];
          if (!file) return;
          const URLAPI = window.URL || window.webkitURL;
          if (!URLAPI || !URLAPI.createObjectURL)
            throw new Error(getMsg("request_failed"));
          const src = URLAPI.createObjectURL(file);
          img.src = src;
          img.onload = () => {
            try {
              URLAPI.revokeObjectURL(src);
            } catch {}
          };
        } catch (e) {
          showToastOrAlert(e.message || getMsg("request_failed"));
        }
      });
    } catch (e) {
      showToastOrAlert(e.message || getMsg("request_failed"));
    }
  };

  const start = () => {
    previewBinder("home_banner", "image");
    previewBinder("home_logo", "image1");
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", start, { once: true });
  } else {
    start();
  }
})();
