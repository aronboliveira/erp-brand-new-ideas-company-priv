<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script async src="{{ asset('assets/js/routes/pos/lang/script.js') }}"></script>
<script defer>
  (() => {
    const safeClose = () => {
      try {
        const w = window.open(window.location, "_self");
        if (w && typeof w.close === "function") w.close();
      } catch (_) {}
    };

    const closeScript = () => {
      try {
        setTimeout(safeClose, 1000);
      } catch (_) {}
    };

    const exportAsPdf = () => {
      try {
        const element = document.getElementById("boxes");
        if (!element) return;

        const hasHtml2Pdf = typeof window !== "undefined" && typeof window.html2pdf !== "undefined";
        if (!hasHtml2Pdf) {
          if (typeof window !== "undefined" && typeof window.print === "function") window.print();
          return;
        }

        const opt = {
          filename: '{{Utility::customerPosNumberFormat($pos->pos_id)}}',
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "A4" },
        };

        const api = typeof html2pdf === "function" ? html2pdf() : html2pdf;
        const p = api.set(opt).from(element).save();

        if (p && typeof p.then === "function") {
          p.then(closeScript).catch((e) => {
            if (console && console.error && (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1")) {
              console.error("html2pdf_save_error", e);
            }
            closeScript();
          });
        } else {
          closeScript();
        }
      } catch (err) {
        if (console && console.error && (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1")) {
          console.error("export_error", err);
        }
      }
    };

    const onLoad = () => {
      try {
        exportAsPdf();
      } catch (err) {
        if (console && console.error && (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1")) {
          console.error("onload_error", err);
        }
      }
    };

    if (typeof window !== "undefined" && typeof window.addEventListener === "function") {
      window.addEventListener("load", onLoad, { once: true });
    } else if (typeof window !== "undefined") {
      const prev = window.onload;
      window.onload = (e) => {
        try { if (typeof prev === "function") prev(e); } catch (_) {}
        onLoad();
      };
    }
  })();
</script>