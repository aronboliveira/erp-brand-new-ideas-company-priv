(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;
  const dataFilterGuard = "data-filter-guard";
  const dataPrintGuard = "data-print-guard";

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleError(getMsg("pdf_unavailable"), "click");
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
        scheduleError(getMsg("plugin_unavailable"), "click");
        return;
      }
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      scheduleError(getMsg("pdf_unavailable"), "click");
    }
  };
  window.saveAsPDF = saveAsPDF;

  const bindFilterToggle = () => {
    if (!$ || !$.fn) {
      scheduleError(getMsg("toggle_unavailable"), "click");
      return;
    }
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
          scheduleError(getMsg("toggle_unavailable"), "click");
        }
      } catch (_) {
        scheduleError(getMsg("toggle_unavailable"), "click");
      }
    };
    $(btn).on("click", handler);
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
        scheduleError(getMsg("print_unavailable"), "click");
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
