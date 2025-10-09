(() => {
  try {
    const desc = document.getElementById("ai-description");
    const copyAll = document.getElementById("ai-copy-all-btn");
    const copySel = document.getElementById("ai-copy-selected-btn");

    if (!desc) return;

    const toast = msg => {
      try {
        if (!msg) return;
        const bsLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (
          bsLink &&
          typeof window.bootstrap !== "undefined" &&
          window.bootstrap?.Toast
        ) {
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
          window.bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
      } catch (_) {
        alert(msg);
      }
    };

    const doCopy = async (text, okMsg, errMsg) => {
      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(text);
          toast(okMsg);
        } else {
          const tmp = document.createElement("textarea");
          tmp.value = text;
          tmp.style.position = "fixed";
          tmp.style.opacity = "0";
          document.body.appendChild(tmp);
          tmp.select();
          document.execCommand("copy");
          document.body.removeChild(tmp);
          toast(okMsg);
        }
      } catch (err) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/aiTemplates/clipboard.js] Copy error:",
            err?.constructor?.name ?? "Error",
            err?.message ?? "Unknown error"
          );
        toast(errMsg);
      }
    };

    if (copyAll && copyAll.getAttribute("data-listener-active") !== "true") {
      copyAll.setAttribute("data-listener-active", "true");
      copyAll.addEventListener("click", e => {
        try {
          e.preventDefault();
          const ok =
            desc.getAttribute("data-copy-all-msg") ??
            "Text copied to clipboard.";
          const err =
            desc.getAttribute("data-copy-err-msg") ??
            "Copy failed. Please try again.";
          doCopy(desc.value ?? "", ok, err);
        } catch (err2) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error(
              "[assets/js/routes/aiTemplates/clipboard.js] Copy-all click error:",
              err2?.constructor?.name ?? "Error",
              err2?.message ?? "Unknown error"
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
              : desc.value ?? "";
          const ok =
            desc.getAttribute("data-copy-sel-msg") ??
            "Selected text copied to clipboard.";
          const err =
            desc.getAttribute("data-copy-err-msg") ??
            "Copy failed. Please try again.";
          doCopy(selected, ok, err);
        } catch (err2) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error(
              "[assets/js/routes/aiTemplates/clipboard.js] Copy-selected click error:",
              err2?.constructor?.name ?? "Error",
              err2?.message ?? "Unknown error"
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
        error?.message ?? "Unknown error"
      );
  }
})();
