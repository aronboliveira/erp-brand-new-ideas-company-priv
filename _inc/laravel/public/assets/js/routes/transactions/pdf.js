(() => {
  const { guard, utils } = window.ERPBootstrap.require("ERPGuard", "ERPUtils");
  if (!guard) return;
  const $ = window.jQuery;
  const showError = message => {
    guard.showToast(message);
  };

  const schedulePointerupError = message => {
    document.addEventListener("pointerup", () => showError(message), {
      once: true,
    });
  };

  const getMsg = (el, key) => {
    return utils.getTranslation(key) || "# ERROR";
  };

  const filenameFrom = () => {
    try {
      return ($("#filename").val() ?? "").toString().trim() || "download";
    } catch (_) {
      return "download";
    }
  };

  const ensureHtml2Pdf = () => {
    if (typeof window.html2pdf === "function") return true;
    guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
    return false;
  };

  const doSave = el => {
    if (!ensureHtml2Pdf()) return;

    const area = document.getElementById("printableArea");
    if (!area) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
      return;
    }

    const opt = {
      margin: 0.3,
      filename: filenameFrom(),
      image: { type: "jpeg", quality: 1 },
      html2canvas: { scale: 4, dpi: 72, letterRendering: true },
      jsPDF: { unit: "in", format: "A4" },
    };

    try {
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
    }
  };

  if (!window.saveAsPDF) {
    window.saveAsPDF = function () {
      doSave(this || document.body);
    };
  }
})();
