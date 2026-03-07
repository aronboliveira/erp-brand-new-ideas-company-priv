/**
 * @fileoverview TypeScript version of public/assets/js/routes/proposals/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */


declare const html2pdf:
  | (() => {
      set: (opt: unknown) => {
        from: (el: HTMLElement) => {
          save: () => {
            then: (fn: () => void) => {
              catch: (fn: (err: unknown) => void) => void;
            };
          };
        };
      };
    })
  | undefined;

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const DATA_BOUND = "data-np-bound";
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const localize = (el: HTMLElement, msgKey: string) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
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

  const showErrorOnPointer = (key: string): void=> {
    const target = document.body;
    if (!target || target.getAttribute(DATA_BOUND) === "true") return;
    const handler = (): void => {
      const text = localize(document.body, key);
      const hasBootstrap =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (hasBootstrap) {
        let toast = document.querySelector<HTMLElement>("#np-error-toast");
        if (!toast) {
          toast = document.createElement("div");
          toast.id = "np-error-toast";
          toast.className =
            "toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          {
            toast.replaceChildren();
            const _d = document.createElement("div");
            _d.className = "d-flex";
            const _b = document.createElement("div");
            _b.className = "toast-body";
            _b.textContent = text;
            const _c = document.createElement("button");
            _c.type = "button";
            _c.className = "btn-close btn-close-white me-2 m-auto";
            _c.dataset.bsDismiss = "toast";
            _c.setAttribute("aria-label", "Close");
            _d.append(_b, _c);
            toast.append(_d);
          }
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

  const closeWindowSafely = (): void => {
    try {
      setTimeout((): void => {
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
    $(window as unknown as Element).on("load", (): void => {
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
          .catch((): void => {
            showErrorOnPointer("proposal_pdf_unavailable");
          });
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

export {};
