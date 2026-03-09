/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;
  const qs = (s, r = document) => r.querySelector(s);

  const year = "{{$currentYear}}";
  window.currentYear = year;

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
      return;
    }
    const name =
      ((window.jQuery && $("#filename").val()) ?? "").toString().trim() ||
      "export";
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
          )
            console.error("html2pdf unavailable");
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
})();
