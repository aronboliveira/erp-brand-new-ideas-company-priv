(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;
  const dataListenerGuard = "data-listener-guard";

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  if (!$) {
    scheduleError(getMsg("plugin_unavailable"), "click");
    return;
  }

  const exportPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleError(getMsg("pdf_unavailable"), "click");
      return;
    }
    const name = ($("#filename").val() ?? "").toString().trim() || "export";
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
  window.saveAsPDF = exportPDF;

  const onFilterClick = () => {
    $("#show_filter").toggle();
  };

  const triggerPrint = () => {
    try {
      if (typeof window.print === "function") {
        window.print();
      } else {
        scheduleError(getMsg("print_unavailable"), "click");
      }
    } catch (_) {
      scheduleError(getMsg("print_unavailable"), "click");
    }
  };

  const onAfterPrint = () => {
    try {
      window.close();
    } catch (_) {}
    try {
      window.history && window.history.back && window.history.back();
    } catch (_) {}
  };

  const init = () => {
    const filterBtn = document.getElementById("filter");
    if (
      filterBtn &&
      filterBtn.getAttribute(dataListenerGuard + "-filter") !== "true"
    ) {
      filterBtn.setAttribute(dataListenerGuard + "-filter", "true");
      $(filterBtn).on("click", onFilterClick);
    }
    const exportBtnCandidates = Array.from(
      document.querySelectorAll(
        '[data-export-pdf], [data-action="export-pdf"], #saveAsPDF',
      ),
    );
    exportBtnCandidates.forEach(el => {
      if (el.getAttribute(dataListenerGuard + "-export") !== "true") {
        el.setAttribute(dataListenerGuard + "-export", "true");
        $(el).on("click", exportPDF);
      }
    });
    if (!window._afterPrintBound) {
      window._afterPrintBound = true;
      window.addEventListener("afterprint", onAfterPrint, { once: false });
    }
    triggerPrint();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
