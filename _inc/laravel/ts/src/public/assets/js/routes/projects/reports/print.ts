/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/reports/print.js
 * @generated from original JavaScript - manual review recommended
 * @module print
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
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
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
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
    const message = getLocalizedMessage(el ?? document.body, key);
    const hasBootstrap =
      document.querySelector('link[href*="bootstrap"]') &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      if (!document.querySelector<HTMLElement>("#error-toast")) {
        const t = document.createElement("div");
        t.id = "error-toast";
        t.className = "toast align-items-center text-bg-danger border-0";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        { t.replaceChildren(); const _d = document.createElement("div"); _d.className = "d-flex"; const _b = document.createElement("div"); _b.className = "toast-body"; _b.textContent = message; const _c = document.createElement("button"); _c.type = "button"; _c.className = "btn-close btn-close-white me-2 m-auto"; _c.dataset.bsDismiss = "toast"; _c.setAttribute("aria-label", "Close"); _d.append(_b, _c); t.append(_d); }
        document.body.appendChild(t);
      }
      new bootstrap.Toast(document.querySelector<HTMLElement>("#error-toast")).show();
    } else {
      alert(message);
    }
  };

  const attachPointerGuard = (el, key) => {
    if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
    const onceHandler = (): void => { handleErrorDisplay(el, key); };
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

    const initialFilename = $("#filename").val() ?? "report";
    window.saveAsPDF = (): void => {
      const el = document.getElementById("printableArea");
      try {
        if (!el || typeof html2pdf === "undefined")
          throw new Error("html2pdf missing or target not found");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        const currentName = $("#filename").val() ?? initialFilename;
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
        attachPointerGuard(el ?? document.body, "report_pdf_unavailable");
      }
    };

    $((): void => {
      const $table = $("#reportTable");
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!$table.length) return;
      try {
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        if (!$.fn.DataTable) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("DataTables library is required");
          attachPointerGuard($table.get(0), "datatable_unavailable");
          return;
        }
        if ($.fn.DataTable.isDataTable($table)) return;
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        const currentName = $("#filename").val() ?? initialFilename;
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

export {};
