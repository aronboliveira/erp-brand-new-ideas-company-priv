(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;
  const dataListenerGuard = "data-listener-guard";

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleError(getMsg("pdf_unavailable"), "click");
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
        scheduleError(getMsg("plugin_unavailable"), "click");
        return;
      }
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      scheduleError(getMsg("pdf_unavailable"), "click");
    }
  };
  window.saveAsPDF = saveAsPDF;

  const initScrollSpy = () => {
    try {
      if (window.bootstrap && window.bootstrap.ScrollSpy) {
        new window.bootstrap.ScrollSpy(document.body, {
          target: "#useradd-sidenav",
          offset: 300,
        });
      } else {
        scheduleError(getMsg("plugin_unavailable"), "click");
      }
    } catch (_) {
      scheduleError(getMsg("plugin_unavailable"), "click");
    }
  };

  const onListItemClick = function () {
    $(".list-group-item").parent().removeClass("text-primary");
    $(this).parent().addClass("text-primary");
  };

  const initListGroup = () => {
    document.querySelectorAll(".list-group-item").forEach(el => {
      if (el.getAttribute(dataListenerGuard + "-lgi") !== "true") {
        el.setAttribute(dataListenerGuard + "-lgi", "true");
        $(el).on("click", onListItemClick);
      }
    });
  };

  const check_theme = color_val => {
    $("#theme_color").prop("checked", false);
    $('input[value="' + color_val + '"]').prop("checked", true);
  };
  window.check_theme = check_theme;

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
