/**
 * @file Leaves Report PDF Guard
 * @description Guards leaves report PDF generation using ERPGuard/ERPUtils
 * @requires ERPGuard
 * @requires ERPUtils
 * @requires html2pdf
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const $ = window.jQuery;

  if (!guard || !utils) {
    
    return;
  }

  const MSG_KEY = "pdf_unavailable";
  const PLUGIN_KEY = "plugin_unavailable";

  /**
   * Initialize ScrollSpy if Bootstrap available
   */
  const initScrollSpy = () => {
    try {
      if (window.bootstrap?.ScrollSpy) {
        new window.bootstrap.ScrollSpy(document.body, {
          target: "#useradd-sidenav",
          offset: 300,
        });
      }
    } catch (_) {}
  };

  /**
   * Initialize list group click handlers
   */
  const initListGroup = () => {
    if (!$) return;
    document.querySelectorAll(".list-group-item").forEach(el => {
      if (el.getAttribute("data-listener-guard-lgi") === "true") return;
      el.setAttribute("data-listener-guard-lgi", "true");
      $(el).on("click", function () {
        $(".list-group-item").parent().removeClass("text-primary");
        $(this).parent().addClass("text-primary");
      });
    });
  };

  /**
   * Theme color checker
   * @param {string} color_val - Color value
   */
  const check_theme = color_val => {
    if (!$) return;
    $("#theme_color").prop("checked", false);
    $('input[value="' + color_val + '"]').prop("checked", true);
  };
  window.check_theme = check_theme;

  /**
   * Save report as PDF
   */
  const saveAsPDF = () => {
    utils.saveAsPDF({
      areaSelector: "#printableArea",
      filenameSelector: "#filename",
      defaultFilename: "leaves_report",
      format: "A2",
      msgKey: MSG_KEY,
    });
  };
  window.saveAsPDF = saveAsPDF;

  /**
   * Initialize on DOM ready
   */
  const init = () => {
    initScrollSpy();
    initListGroup();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
