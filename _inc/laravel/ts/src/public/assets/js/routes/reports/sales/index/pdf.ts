/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/sales/index/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery as JQueryStatic;
  const qs = <T extends HTMLElement = HTMLElement>(
    s: string,
    r: Document | HTMLElement = document,
  ): T | null => r.querySelector<T>(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataFilterGuard = "data-filter-guard";
  const dataNavGuard = "data-nav-guard";

  const ensureToastContainer = (): HTMLElement => {
    const id = "np-toast-container";
    let c = qs<HTMLDivElement>("#" + id);
    if (c) {
      return c;
    }
    const div = document.createElement("div");
    div.id = id;
    div.setAttribute("aria-live", "polite");
    div.setAttribute("aria-atomic", "true");
    div.style.position = "fixed";
    div.style.top = "1rem";
    div.style.right = "1rem";
    document.body.appendChild(div);
    return div;
  };

  const showErrorNow = (message: string) => {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap?.Toast;
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
        new window.bootstrap.Toast(t as HTMLElement, {
          autohide: true,
          delay: 4000,
        }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };

  const scheduleInteractiveError = (message: string) => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
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

  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el?.getAttribute(dataSvLocalized) === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const saveAsPDF = (): void => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
      return;
    }
    const name = String($("#filename").val() ?? "").trim() || "export";
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
        scheduleInteractiveError(getMsg(area, "plugin_unavailable"));
        return;
      }
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      scheduleInteractiveError(getMsg(area, "pdf_unavailable"));
    }
  };

  window.saveAsPDF = saveAsPDF;

  const bindFilterToggle = (): void => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
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
    const handler = function (this: HTMLElement): void {
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

  const syncDates = (): void => {
    try {
      const startVal = String($(".startDate").val() ?? "");
      const endVal = String($(".endDate").val() ?? "");
      $(".start_date").val(startVal);
      $(".end_date").val(endVal);
    } catch (_) {
      scheduleInteractiveError(getMsg(document.body, "date_sync_unavailable"));
    }
  };

  const initReportTab = (): void => {
    const setReport = (href: unknown) => {
      if (!href) {
        scheduleInteractiveError(getMsg(document.body, "report_unavailable"));
        return;
      }
      $(".report").val(String(href));
    };
    const initial =
      $(".nav-item .active").attr("href") ??
      $("ul.nav-pills > li > a.active").attr("href") ??
      "";
    if (initial !== "") {
      setReport(initial);
    }
    document
      .querySelectorAll<HTMLElement>("ul.nav-pills > li > a")
      .forEach((el: HTMLElement, i: number) => {
        if (el.getAttribute(dataNavGuard) === "true") {
          return;
        }
        el.setAttribute(dataNavGuard, "true");
        const h = function (this: HTMLElement): void {
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

  const init = (): void => {
    bindFilterToggle();
    syncDates();
    initReportTab();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
