(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;
  const dataListenerGuard = "data-listener-guard";

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  if (!$) {
    scheduleError(getMsg("print_unavailable"), "click");
    return;
  }

  const printAreaSafely = () => {
    const src = document.querySelector("#printableArea");
    if (!src) {
      scheduleError(getMsg("print_unavailable"), "click");
      return;
    }
    let iframe = document.querySelector("#np-print-iframe");
    if (!iframe) {
      iframe = document.createElement("iframe");
      iframe.id = "np-print-iframe";
      iframe.style.position = "fixed";
      iframe.style.right = "0";
      iframe.style.bottom = "0";
      iframe.style.width = "0";
      iframe.style.height = "0";
      iframe.style.border = "0";
      document.body.appendChild(iframe);
    }
    const doc = iframe.contentWindow?.document;
    if (!doc) {
      scheduleError(getMsg("print_unavailable"), "click");
      return;
    }
    const cssNodes = Array.from(
      document.head.querySelectorAll('link[rel="stylesheet"], style'),
    ).map(n => n.cloneNode(true));
    doc.open();
    doc.write("<!doctype html><html><head></head><body></body></html>");
    doc.close();
    cssNodes.forEach(n => doc.head.appendChild(n));
    const wrapper = doc.createElement("div");
    wrapper.innerHTML = src.innerHTML;
    doc.body.appendChild(wrapper);
    const done = () => {
      try {
        iframe.contentWindow?.focus?.();
        iframe.contentWindow?.print?.();
      } catch (_) {
        scheduleError(getMsg("print_unavailable"), "click");
      }
    };
    if (doc.readyState === "complete") {
      setTimeout(done, 0);
    } else {
      doc.addEventListener("readystatechange", function onr() {
        if (doc.readyState === "complete") {
          doc.removeEventListener("readystatechange", onr);
          done();
        }
      });
    }
  };
  window.saveAsPDF = printAreaSafely;

  const onFilterClick = () => {
    $("#show_filter").toggle();
  };

  const init = () => {
    const filterBtn = document.getElementById("filter");
    if (
      filterBtn &&
      filterBtn.getAttribute(dataListenerGuard + "-filter") !== "true"
    ) {
      filterBtn.setAttribute(dataListenerGuard + "-filter", "true");
      $(filterBtn).on("click", onFilterClick);
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
