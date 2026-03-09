(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const desc = document.getElementById("ai-description");
    const copyAll = document.getElementById("ai-copy-all-btn");
    const copySel = document.getElementById("ai-copy-selected-btn");

    if (!desc) return;

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
            "[assets/js/routes/aiTemplates/clipboard.js] Copy error:",
            err?.constructor?.name ?? "Error",
            err?.message ?? "Unknown error",
          );
        scheduleError(errMsg, "click");
      }
    };

    if (copyAll && copyAll.getAttribute("data-listener-active") !== "true") {
      copyAll.setAttribute("data-listener-active", "true");
      copyAll.addEventListener("click", e => {
        try {
          e.preventDefault();
          const ok =
            desc.getAttribute("data-copy-all-msg") ||
            getMsg("copy_text_success");
          const err =
            desc.getAttribute("data-copy-err-msg") || getMsg("copy_failed");
          doCopy(desc.value ?? "", ok, err);
        } catch (err2) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error(
              "[assets/js/routes/aiTemplates/clipboard.js] Copy-all click error:",
              err2?.constructor?.name ?? "Error",
              err2?.message ?? "Unknown error",
            );
        }
      });
    }

    if (copySel && copySel.getAttribute("data-listener-active") !== "true") {
      copySel.setAttribute("data-listener-active", "true");
      copySel.addEventListener("click", e => {
        try {
          e.preventDefault();
          const start = desc.selectionStart ?? 0;
          const end = desc.selectionEnd ?? 0;
          const selected =
            start !== end
              ? (desc.value ?? "").substring(start, end)
              : (desc.value ?? "");
          const ok =
            desc.getAttribute("data-copy-sel-msg") ||
            getMsg("copy_selected_success");
          const err =
            desc.getAttribute("data-copy-err-msg") || getMsg("copy_failed");
          doCopy(selected, ok, err);
        } catch (err2) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error(
              "[assets/js/routes/aiTemplates/clipboard.js] Copy-selected click error:",
              err2?.constructor?.name ?? "Error",
              err2?.message ?? "Unknown error",
            );
        }
      });
    }
  } catch (error) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error(
        "[assets/js/routes/aiTemplates/clipboard.js] Initialization error:",
        error?.constructor?.name ?? "Error",
        error?.message ?? "Unknown error",
      );
  }
})();
