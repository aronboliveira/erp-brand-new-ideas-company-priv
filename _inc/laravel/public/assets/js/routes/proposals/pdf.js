(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils) return;

  const DATA_BOUND = "data-np-bound";

  const localize = (el, msgKey) => {
    return utils.getTranslation(msgKey) || "# ERROR";
  };

  const showErrorOnPointer = key => {
    const target = document.body;
    if (!target || target.getAttribute(DATA_BOUND) === "true") return;
    const handler = () => {
      const text = localize(document.body, key);
      guard.showToast(text);
    };
    target.addEventListener("pointerup", handler, { once: true });
    target.setAttribute(DATA_BOUND, "true");
    const mo = new MutationObserver((_, obs) => {
      if (!document.body.contains(target)) {
        target.removeEventListener("pointerup", handler);
        obs.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const closeWindowSafely = () => {
    try {
      setTimeout(() => {
        window.open(window.location.href, "_self");
        window.close();
      }, 1000);
    } catch {}
  };

  try {
    if (typeof $ === "undefined") {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery failed to load");
      return;
    }
    $(window).on("load", () => {
      try {
        const el = document.getElementById("boxes");
        if (!el || typeof html2pdf === "undefined") {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("html2pdf not available or target missing");
          showErrorOnPointer("proposal_pdf_unavailable");
          return;
        }
        const opt = {
          filename:
            "{{Utility::customerProposalNumberFormat($proposal->proposal_id)}}",
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "A4" },
        };
        html2pdf()
          .set(opt)
          .from(el)
          .save()
          .then(closeWindowSafely)
          .catch(() => showErrorOnPointer("proposal_pdf_unavailable"));
      } catch {
        showErrorOnPointer("proposal_pdf_unavailable");
      }
    });
  } catch (e) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Initialization failed", e);
  }
})();
