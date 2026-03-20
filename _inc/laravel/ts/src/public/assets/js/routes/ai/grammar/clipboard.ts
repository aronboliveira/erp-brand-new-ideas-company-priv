/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/grammar/clipboard.js
 * @generated from original JavaScript — automated migration
 * @module clipboard
 */
((): void => {
  try {
    const out = document.getElementById("ai-description");
    const copy = document.getElementById("grammar-copy-btn");
    if (!out || !copy) return;
    if (copy.getAttribute("data-listener-active") === "true") return;
    copy.setAttribute("data-listener-active", "true");

    const toast = (msg: string) => {
      try {
        if (!msg) return;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        const bsLink = document.querySelector('link[href*="bootstrap"]');
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

    const doCopy = async (text: string, okMsg: string, errMsg: string) => {
      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(text);
          toast(okMsg);
        } else {
          const tmp = document.createElement("textarea");
          (tmp as HTMLTextAreaElement).value = text;
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
            "[assets/js/routes/aiGrammar/clipboard.js] Copy error:",
            (err as Error)?.constructor?.name ?? "Error",
            (err as Error)?.message ?? "Unknown error"
          );
        toast(errMsg);
      }
    };

    copy.addEventListener("click", e => {
      try {
        e.preventDefault();
        const ok =
          out.getAttribute("data-copy-ok-msg") ?? "Text copied to clipboard.";
        const err =
          out.getAttribute("data-copy-err-msg") ??
          "Copy failed. Please try again.";
        doCopy((out as HTMLTextAreaElement).value ?? "", ok, err);
      } catch (err2) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error(
            "[assets/js/routes/aiGrammar/clipboard.js] Click handler error:",
            (err2 as Error)?.constructor?.name ?? "Error",
            (err2 as Error)?.message ?? "Unknown error"
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
        (error as Error)?.constructor?.name ?? "Error",
        (error as Error)?.message ?? "Unknown error"
      );
  }
})();
