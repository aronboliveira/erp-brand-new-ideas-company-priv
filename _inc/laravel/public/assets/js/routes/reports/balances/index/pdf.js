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

  const copyDatesOnce = () => {
    const startVal = $(".startDate").val() ?? "";
    const endVal = $(".endDate").val() ?? "";
    $(".start_date").val(startVal);
    $(".end_date").val(endVal);
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
    copyDatesOnce();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
