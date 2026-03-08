/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/leaves/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */

interface DataTablesStaticExt {
  (options?: DataTablesSettings): DataTablesApi;
  isDataTable(selector: JQuery | string): boolean;
}

interface DataTablesJQueryFnExtension {
  dataTable?: {
    Buttons?: unknown;
  };
}

type EventHandler = (this: HTMLElement, e: JQueryEventObject) => void;

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const qs = <T extends Element = Element>(
    s: string,
    r: ParentNode = document,
  ): T | null => r.querySelector<T>(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataListenerGuard = "data-listener-guard";

  const ensureToastContainer = (): HTMLDivElement => {
    const id = "np-toast-container";
    const existing = qs<HTMLDivElement>("#" + id);
    if (existing) {
      return existing;
    }
    const c = document.createElement("div");
    c.id = id;
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };

  const showErrorNow = (message: string): void => {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      let t = qs<HTMLDivElement>("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
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

  const scheduleInteractiveError = (message: string): void=> {
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
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
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
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const bindWithObserver = (
    el: HTMLElement,
    evt: string,
    handler: EventHandler,
    flag: string,
  ): void => {
    if (!el || el.getAttribute(flag) === "true") {
      return;
    }
    el.setAttribute(flag, "true");
    $(el).on(evt, handler as unknown as (e: JQueryEventObject) => void);
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(el)) {
        $(el).off(evt);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const saveAsPDF = (): void => {
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

  window.saveAsPDF = saveAsPDF;

  const initDataTable = (): void => {
    const $table = $("#report-dataTable");
    if (!$table.length) {
      return;
    }
    const dtFn = $.fn.DataTable as DataTablesStaticExt | undefined;
    if (!dtFn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("DataTables unavailable");
      } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
      scheduleInteractiveError(
        getMsg($table.get(0), "plugin_unavailable"),
      );
      return;
    }
    if (dtFn.isDataTable($table)) {
      return;
    }
    const title = ($("#filename").val() ?? "").toString().trim();
    const hasButtons = ($.fn as DataTablesJQueryFnExtension).dataTable?.Buttons;
    const opts: DataTablesSettings = hasButtons
      ? {
          dom: "lBfrtip",
          buttons: [
            { extend: "pdf", title },
            { extend: "excel", title },
            { extend: "csv", title },
          ],
        }
      : {};
    if (!hasButtons) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("DataTables Buttons unavailable");
      } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
      scheduleInteractiveError(
        getMsg($table.get(0), "datatable_unavailable"),
      );
    }
    try {
      $table.DataTable(opts);
    } catch (_) {
      scheduleInteractiveError(
        getMsg($table.get(0), "datatable_unavailable"),
      );
    }
  };

  const onTypeChange = function (this: HTMLElement): void {
    const v = $(this).val();
    if (v === "monthly") {
      $(".month").addClass("d-block").removeClass("d-none");
      $(".year").addClass("d-none").removeClass("d-block");
    } else {
      $(".year").addClass("d-block").removeClass("d-none");
      $(".month").addClass("d-none").removeClass("d-block");
    }
  };

  const initTypeRadios = (): void => {
    const radios = document.querySelectorAll<HTMLInputElement>(
      'input[name="type"][type="radio"]',
    );
    radios.forEach((el: HTMLInputElement, i: number) => {
      bindWithObserver(
        el,
        "change",
        onTypeChange as EventHandler,
        dataListenerGuard + "-type-" + i,
      );
    });
    const checked = document.querySelector<HTMLInputElement>(
      'input[name="type"][type="radio"]:checked',
    );
    if (checked) {
      onTypeChange.call(checked);
    }
  };

  const init = (): void => {
    initDataTable();
    initTypeRadios();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
