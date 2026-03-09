(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;
  const PRINTABLE_AREA = "printableArea";
  const FILENAME_INPUT = "#filename";

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const saveAsPDF = () => {
    try {
      if (
        typeof html2pdf !== "object" ||
        typeof html2pdf().set !== "function"
      ) {
        scheduleError(getMsg("plugin_unavailable"), "click");
        return;
      }

      const printable = document.getElementById(PRINTABLE_AREA);
      if (!printable) {
        scheduleError(getMsg("pdf_unavailable"), "click");
        return;
      }

      let filename = "document.pdf";
      try {
        filename = $(FILENAME_INPUT).val() || filename;
      } catch {
        const input = document.querySelector(FILENAME_INPUT);
        if (input) filename = input.value || filename;
      }

      html2pdf()
        .set({
          margin: 0.3,
          filename,
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "a2" },
        })
        .from(printable)
        .save();
    } catch (e) {
      scheduleError(getMsg("pdf_unavailable"), "click");
    }
  };

  window.saveAsPDF = saveAsPDF;
})();
