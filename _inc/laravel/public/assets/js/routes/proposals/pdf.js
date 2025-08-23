(() => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const DATA_BOUND = "data-np-bound";

  const localize = (el, msgKey) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const showErrorOnPointer = key => {
    const target = document.body;
    if (!target || target.getAttribute(DATA_BOUND) === "true") return;
    const handler = () => {
      const text = localize(document.body, key);
      const hasBootstrap =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap?.Toast;
      if (hasBootstrap) {
        let toast = document.querySelector("#np-error-toast");
        if (!toast) {
          toast = document.createElement("div");
          toast.id = "np-error-toast";
          toast.className =
            "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          toast.innerHTML = `<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
          document.body.appendChild(toast);
        }
        new bootstrap.Toast(toast).show();
      } else {
        alert(text);
      }
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
      console.error("jQuery failed to load");
      return;
    }
    $(window).on("load", () => {
      try {
        const el = document.getElementById("boxes");
        if (!el || typeof html2pdf === "undefined") {
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
    console.error("Initialization failed", e);
  }
})();
