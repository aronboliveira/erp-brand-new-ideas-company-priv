(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const out = document.getElementById("ai-description");
    const copy = document.getElementById("grammar-copy-btn");
    if (!out || !copy) return;
    if (copy.getAttribute("data-listener-active") === "true") return;
    copy.setAttribute("data-listener-active", "true");

    const doCopy = async (text, okMsg, errMsg) => {
      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(text);
          scheduleError(okMsg, "click");
        } else {
          const tmp = document.createElement("textarea");
          tmp.value = text;
          tmp.style.position = "fixed";
          tmp.style.opacity = "0";
          document.body.appendChild(tmp);
          tmp.select();
          document.execCommand("copy");
          document.body.removeChild(tmp);
          scheduleError(okMsg, "click");
        }
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/aiGrammar/clipboard.js] Copy error:",
            err?.constructor?.name ?? "Error",
            err?.message ?? "Unknown error",
          );
        scheduleError(errMsg, "click");
      }
    };

    copy.addEventListener("click", e => {
      try {
        e.preventDefault();
        const ok =
          out.getAttribute("data-copy-ok-msg") || getMsg("copy_success");
        const err =
          out.getAttribute("data-copy-err-msg") || getMsg("copy_failed");
        doCopy(out.value ?? "", ok, err);
      } catch (err2) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/aiGrammar/clipboard.js] Click handler error:",
            err2?.constructor?.name ?? "Error",
            err2?.message ?? "Unknown error",
          );
      }
    });
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/aiGrammar/clipboard.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error",
      );
  }
})();
