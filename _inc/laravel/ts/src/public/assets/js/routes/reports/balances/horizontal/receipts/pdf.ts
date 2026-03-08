/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/balances/horizontal/receipts/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

(function (): void {
  const $ = window.jQuery;
  const qs = <T extends Element = HTMLElement>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector<T>(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataListenerGuard = "data-listener-guard";
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery unavailable");
    } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
    scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
    return;
  }
  const ensureToastContainer = (): HTMLElement => {
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
  const showErrorNow = (message: string): void=> {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      const toastId = "np-toast";
      let t = qs("#" + toastId, container);
      if (!t) {
        t = document.createElement("div");
        t.id = toastId;
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
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
  function scheduleInteractiveError(message: string): void{
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
  }
  function getMsg(el: HTMLElement, key: string): string {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
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
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  }
  const bindWithObserver = (
    el: HTMLElement,
    evt: string,
    handler: (e: Event) => void,
    flag: string,
  ): void=> {
    if (!el || el.getAttribute(flag) === "true") {
      return;
    }
    el.setAttribute(flag, "true");
    $(el).on(evt, handler);
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(el)) {
        $(el).off(evt, handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const exportPDF = (): void => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
      return;
    }
    const name = String($("#filename").val() ?? "").trim();
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
        } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
        scheduleInteractiveError(getMsg(area, "plugin_unavailable"));
        return;
      }
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      scheduleInteractiveError(getMsg(area, "pdf_unavailable"));
    }
  };
  window.saveAsPDF = exportPDF;
  const onFilterClick = (): void => {
    $("#show_filter").toggle();
  };
  const triggerPrint = (): void => {
    try {
      if (typeof window.print === "function") {
        window.print();
      } else {
        scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
      }
    } catch (_) {
      scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
    }
  };
  const onAfterPrint = (): void => {
    try {
      window.close();
    } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
    try {
      window.history.back && window.history.back();
    } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
  };
  const init = (): void => {
    const filterBtn = document.getElementById("filter");
    if (filterBtn) {
      bindWithObserver(
        filterBtn,
        "click",
        onFilterClick,
        dataListenerGuard + "-filter",
      );
    }
    const exportBtnCandidates = Array.from(
      document.querySelectorAll(
        '[data-export-pdf], [data-action="export-pdf"], #saveAsPDF',
      ),
    );
    exportBtnCandidates.forEach((el: Element): void => {
      bindWithObserver(
        el as HTMLElement,
        "click",
        exportPDF,
        dataListenerGuard + "-export",
      );
    });
    triggerPrint();
    if (!window._afterPrintBound) {
      window._afterPrintBound = true;
      window.addEventListener("afterprint", onAfterPrint, { once: false });
    }
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
