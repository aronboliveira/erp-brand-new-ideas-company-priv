(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleError(getMsg("pdf_unavailable"));
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
        scheduleError(getMsg("plugin_unavailable"));
        return;
      }
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      scheduleError(getMsg("pdf_unavailable"));
    }
  };

  window.saveAsPDF = saveAsPDF;
})();
