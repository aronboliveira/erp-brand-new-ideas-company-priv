/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;
  const qs = (s, r = document) => r.querySelector(s);

  const dataFilterGuard = "data-filter-guard";
  const dataNavGuard = "data-nav-guard";

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
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

  const bindSave = () => {
    window.saveAsPDF = saveAsPDF;
  };

  const bindFilterToggle = () => {
    if (!$ || !$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      guard.scheduleInteractiveError(guard.getMsg("toggle_unavailable"));
      return;
    }
    const btn = document.getElementById("filter");
    const panel = document.getElementById("show_filter");
    if (!btn || btn.getAttribute(dataFilterGuard) === "true") {
      return;
    }
    btn.setAttribute(dataFilterGuard, "true");
    const handler = function () {
      try {
        if (panel) {
          $("#show_filter").toggle();
        } else {
          guard.scheduleInteractiveError(guard.getMsg("toggle_unavailable"));
        }
      } catch (_) {
        guard.scheduleInteractiveError(guard.getMsg("toggle_unavailable"));
      }
    };
    $(btn).on("click", handler);
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(btn)) {
        $(btn).off("click", handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const syncDates = () => {
    try {
      const s = $(".startDate").val() ?? "";
      const e = $(".endDate").val() ?? "";
      $(".start_date").val(s);
      $(".end_date").val(e);
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("date_sync_unavailable"));
    }
  };

  const initReportTab = () => {
    const setReport = href => {
      if (!href) {
        guard.scheduleInteractiveError(guard.getMsg("report_unavailable"));
        return;
      }
      $(".report").val(href);
    };
    const initial =
      $(".nav-item .active").attr("href") ||
      $("ul.nav-pills > li > a.active").attr("href") ||
      "";
    if (initial) {
      setReport(initial);
    }
    document.querySelectorAll("ul.nav-pills > li > a").forEach(el => {
      if (el.getAttribute(dataNavGuard) === "true") {
        return;
      }
      el.setAttribute(dataNavGuard, "true");
      const h = function () {
        const href = $(this).attr("href");
        setReport(href);
      };
      $(el).on("click", h);
      const mo = new MutationObserver((m, o) => {
        if (!document.body.contains(el)) {
          $(el).off("click", h);
          o.disconnect();
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    });
  };

  const initDataTable = () => {
    const table = document.getElementById("report-dataTable");
    if (!table) {
      return;
    }
    // Try jQuery DataTables first, then simple-datatables fallback
    if ($ && $.fn && $.fn.DataTable) {
      if ($.fn.dataTable && $.fn.dataTable.isDataTable(table)) {
        return;
      }
      try {
        const name =
          (($ && $("#filename").val()) ?? "").toString().trim() || "export";
        $(table).DataTable({
          dom: "lBfrtip",
          buttons: [
            { extend: "excel", title: name },
            { extend: "pdf", title: name },
            { extend: "csv", title: name },
          ],
        });
        return;
      } catch (_) {}
    }
    // Fallback: simple-datatables (vanilla)
    if (window.simpleDatatables && window.simpleDatatables.DataTable) {
      try {
        if (table.classList.contains("dataTable-table")) return;
        new window.simpleDatatables.DataTable(table);
        return;
      } catch (_) {}
    }
    // Neither library available — show localized notice
    const lang = (
      window.sessionStorage.getItem("erp-np-lang") ||
      document.documentElement.lang ||
      "pt-br"
    ).toLowerCase().replace(/_/g, "-");
    const isPt = lang === "pt-br" || lang === "pt";
    const notice = isPt
      ? "Uma biblioteca necessária não foi carregada. Tabela exibida sem recursos avançados."
      : "A required library was not loaded. Table displayed without advanced features.";
    guard.scheduleInteractiveError(notice);
  };

  const init = () => {
    bindSave();
    bindFilterToggle();
    syncDates();
    initReportTab();
    initDataTable();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
