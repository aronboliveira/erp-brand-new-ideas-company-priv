/** @requires ERPGuard */
(() => {
  const DATA_LISTENER_ADDED = "data-listener-added";
  const ERR_FB = "# ERROR";
  const DATA_CLIENT_LOCALIZED = "data-client-localized";
  const DATA_GUARD_MSG = "data-guard-msg";

  const getLocalizedMessage = (el, key) => {
    let msg = ERR_FB;
    if (
      el?.getAttribute("data-sv-localized") === "true" ||
      el?.getAttribute(DATA_CLIENT_LOCALIZED) === "true"
    ) {
      msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
    } else {
      let lang = (
        sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el?.getAttribute(DATA_GUARD_MSG) ||
        window.translations?.en?.[key] ||
        ERR_FB;
      if (msg !== ERR_FB) {
        el?.setAttribute(DATA_GUARD_MSG, msg);
        el?.setAttribute(DATA_CLIENT_LOCALIZED, "true");
      }
    }
    return msg;
  };

  const handleErrorDisplay = (el, key) => {
    const message = getLocalizedMessage(el || document.body, key);
    const hasBootstrap =
      document.querySelector('link[href*="bootstrap"]') &&
      window.bootstrap?.Toast;
    if (hasBootstrap) {
      if (!document.querySelector("#error-toast")) {
        const t = document.createElement("div");
        t.id = "error-toast";
        t.className = "toast align-items-center text-bg-danger border-0";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
        document.body.appendChild(t);
      }
      new bootstrap.Toast(document.querySelector("#error-toast")).show();
    } else {
      alert(message);
    }
  };

  const attachPointerGuard = (el, key) => {
    if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
    const onceHandler = () => handleErrorDisplay(el, key);
    el.addEventListener("pointerup", onceHandler, { once: true });
    el.setAttribute(DATA_LISTENER_ADDED, "true");
    const mo = new MutationObserver((_, o) => {
      if (!document.body.contains(el)) {
        el.removeEventListener("pointerup", onceHandler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  try {
    if (typeof $ === "undefined") {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery is required");
      return;
    }

    const initialFilename = $("#filename").val() || "report";
    window.saveAsPDF = () => {
      const el = document.getElementById("printableArea");
      try {
        if (!el || typeof html2pdf === "undefined")
          throw new Error("html2pdf missing or target not found");
        const currentName = $("#filename").val() || initialFilename;
        const opt = {
          margin: 0.3,
          filename: currentName,
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "A2" },
        };
        html2pdf().set(opt).from(el).save();
      } catch (e) {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("PDF generation failed: library or target missing", e);
        attachPointerGuard(el || document.body, "report_pdf_unavailable");
      }
    };

    $(() => {
      const $table = $("#reportTable");
      if (!$table.length) return;
      try {
        // Try jQuery DataTables first
        if ($.fn.DataTable) {
          if ($.fn.DataTable.isDataTable($table)) return;
          const currentName = $("#filename").val() || initialFilename;
          $table.DataTable({
            dom: "Bfrtip",
            buttons: [
              { extend: "excelHtml5", title: currentName },
              { extend: "csvHtml5", title: currentName },
              { extend: "pdfHtml5", title: currentName },
            ],
            language:
              typeof window.dataTabelLang !== "undefined" && window.dataTabelLang
                ? window.dataTabelLang
                : {},
          });
          return;
        }
        // Fallback: simple-datatables (vanilla)
        if (window.simpleDatatables && window.simpleDatatables.DataTable) {
          const el = $table.get(0);
          if (!el.classList.contains("dataTable-table"))
            new window.simpleDatatables.DataTable(el);
          return;
        }
        // Neither available — show localized notice
        const lang = (document.documentElement.lang || "pt-br").toLowerCase().replace(/_/g, "-");
        const isPt = lang === "pt-br" || lang === "pt";
        const notice = isPt
          ? "Uma biblioteca necessária não foi carregada."
          : "A required library was not loaded.";
        attachPointerGuard($table.get(0), "datatable_unavailable", notice);
      } catch {
        attachPointerGuard($table.get(0), "datatable_unavailable");
      }
    });
  } catch (e) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Initialization failed", e);
  }
})();
