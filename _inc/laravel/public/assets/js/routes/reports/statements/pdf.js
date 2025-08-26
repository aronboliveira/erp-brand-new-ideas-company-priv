(function () {
  const $ = window.jQuery;
  const qs = (s, r = document) => r.querySelector(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataFilterGuard = "data-filter-guard";
  const dataNavGuard = "data-nav-guard";

  const ensureToastContainer = () => {
    const id = "np-toast-container";
    let c = qs("#" + id);
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = id;
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };

  const showErrorNow = message => {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };

  const scheduleInteractiveError = message => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = () => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("click", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };

  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute(dataSvLocalized) === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.["en"]?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
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
          console.error("html2pdf unavailable");
        } catch (_) {}
        scheduleInteractiveError(getMsg(area, "plugin_unavailable"));
        return;
      }
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      scheduleInteractiveError(getMsg(area, "pdf_unavailable"));
    }
  };

  const bindSave = () => {
    window.saveAsPDF = saveAsPDF;
  };

  const bindFilterToggle = () => {
    if (!$ || !$.fn) {
      try {
        console.error("jQuery unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
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
          scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
        }
      } catch (_) {
        scheduleInteractiveError(getMsg(document.body, "toggle_unavailable"));
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
      scheduleInteractiveError(getMsg(document.body, "date_sync_unavailable"));
    }
  };

  const initReportTab = () => {
    const setReport = href => {
      if (!href) {
        scheduleInteractiveError(getMsg(document.body, "report_unavailable"));
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
    if (!$ || !$.fn || !$.fn.DataTable) {
      try {
        console.error("DataTables unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(table, "plugin_unavailable"));
      return;
    }
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
    } catch (_) {
      scheduleInteractiveError(getMsg(table, "plugin_unavailable"));
    }
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
