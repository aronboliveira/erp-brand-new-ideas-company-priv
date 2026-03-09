/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;
  const qs = (s, r = document) => r.querySelector(s);

  const dataFilterGuard = "data-filter-guard";
  const dataPrintGuard = "data-print-guard";

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
      return;
    }
    const name =
      (($ && $("#filename").val()) ?? "").toString().trim() || "export";
    const opt = {
      margin: 0.3,
      filename: name,
      image: { type: "jpeg", quality: 1 },
      html2canvas: { scale: 4, dpi: 72, letterRendering: true },
      jsPDF: { unit: "in", format: "A2" },
    };
    try {
      if (typeof window.html2pdf !== "function") {
        try {
                if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )console.error("html2pdf unavailable");
        } catch (_) {}
        guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
        return;
      }
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
    }
  };

  window.saveAsPDF = saveAsPDF;

  const bindFilterToggle = () => {
    const btn = document.getElementById("filter");
    const panel = document.getElementById("show_filter");
    if (!btn || btn.getAttribute(dataFilterGuard) === "true") {
      return;
    }
    btn.setAttribute(dataFilterGuard, "true");
    const handler = function () {
      try {
        if (panel) {
          $("#show_filter").toggle();
        } else {
          guard.scheduleInteractiveError(guard.getMsg("toggle_unavailable"));
        }
      } catch (_) {
        guard.scheduleInteractiveError(guard.getMsg("toggle_unavailable"));
      }
    };
    $(btn).on("click", handler);
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(btn)) {
        $(btn).off("click", handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const ensurePrintHandlers = () => {
    const root = document.documentElement;
    if (root.getAttribute(dataPrintGuard) === "true") {
      return;
    }
    root.setAttribute(dataPrintGuard, "true");
    const back = () => {
      try {
        window.close();
      } catch (_) {}
      try {
        window.history.back();
      } catch (_) {}
    };
    try {
      window.addEventListener("afterprint", back, { once: true });
    } catch (_) {}
    const doPrint = () => {
      try {
        window.print();
      } catch (_) {
        guard.scheduleInteractiveError(guard.getMsg("print_unavailable"));
      }
    };
    if (document.readyState === "complete") {
      doPrint();
    } else {
      window.addEventListener("load", doPrint, { once: true });
    }
  };

  const init = () => {
    bindFilterToggle();
    ensurePrintHandlers();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
