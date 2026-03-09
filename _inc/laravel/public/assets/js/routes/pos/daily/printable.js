(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;
  const PRINTABLE_AREA_ID = "printableArea";
  const FILENAME_INPUT = "#filename";

  let filename = "document.pdf";

  const saveAsPDF = () => {
    try {
      const element = document.getElementById(PRINTABLE_AREA_ID);
      if (!element) {
        scheduleError(getMsg("printable_not_found"));
        return;
      }

      if (typeof $ === "function") {
        filename = $(FILENAME_INPUT).val() ?? filename;
      } else {
        const input = document.querySelector(FILENAME_INPUT);
        if (input) filename = input.value || filename;
      }

      if (typeof html2pdf !== "function") {
        scheduleError(getMsg("pdf_save_failed"));
        return;
      }

      html2pdf()
        .set({
          margin: 0.3,
          filename,
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "A2" },
        })
        .from(element)
        .save();
    } catch (_) {
      scheduleError(getMsg("pdf_save_failed"));
    }
  };

  window.saveAsPDF = saveAsPDF;
})();
