/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/grammar/regenerate.js
 * @generated from original JavaScript - manual review recommended
 * @module regenerate
 */

((): void => {
  try {
    const out = document.getElementById("ai-description"),
      copy = document.getElementById("grammar-copy-btn");
    if (!out || !copy) return;
    if (copy.getAttribute("data-listener-active") === "true") return;
    copy.setAttribute("data-listener-active", "true");

    const toast = (msg: string): void => {
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
        if (bsLink && window.bootstrap.Toast) {
          const t = document.createElement("div");
          t.className = "toast";
          for (const [k, v] of Object.entries({
            role: "alert",
            "aria-live": "assertive",
            "aria-atomic": "true",
          }))
            t.setAttribute(k, v);
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

    const doCopy = async (
      text: string,
      okMsg: string,
      errMsg: string,
    ): Promise<void> => {
      try {
        if (navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(text);
          toast(okMsg);
        } else {
          const tmp = document.createElement("textarea");
          tmp.value = text;
          Object.assign(tmp.style, { position: "fixed", opacity: "0" });
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
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            err?.constructor?.name ?? "Error",
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            (err as Error)?.message ?? "Unknown error",
          );
        toast(errMsg);
      }
    };

    if (!copy.getAttribute("data-listener-bound-click")) {
      copy.setAttribute("data-listener-bound-click", "1");
      copy.addEventListener("click", (e: Event) => {
        try {
          e.preventDefault();
          const ok =
              out.getAttribute("data-copy-ok-msg") ??
              "Text copied to clipboard.",
            err =
              out.getAttribute("data-copy-err-msg") ??
              "Copy failed. Please try again.";
          void doCopy((out as HTMLTextAreaElement).value ?? "", ok, err);
        } catch (err2) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error(
              "[assets/js/routes/aiGrammar/clipboard.js] Click handler error:",
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
              err2?.constructor?.name ?? "Error",
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
              (err2 as Error)?.message ?? "Unknown error",
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
        "[assets/js/routes/aiGrammar/clipboard.js] Initialization error:",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        error?.constructor?.name ?? "Error",
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        (error as Error)?.message ?? "Unknown error",
      );
  }
})();

export {};
