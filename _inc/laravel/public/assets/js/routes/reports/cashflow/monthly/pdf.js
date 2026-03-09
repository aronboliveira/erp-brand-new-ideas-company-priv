(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;
  const year = "{{$currentYear}}";
  window.currentYear = year;

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
})();
