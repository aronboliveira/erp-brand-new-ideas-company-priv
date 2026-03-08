/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/trials/balance/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

(function (): void {
  const $ = window.jQuery!;
  function qs<T extends Element = Element>(
    s: string,
    r: Document | Element = document,
  ): T | null {
    return r.querySelector<T>(s);
  }
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const qsa = (s: string, r: Document | Element = document) =>
    Array.from(r.querySelectorAll(s));
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataListenerGuard = "data-listener-guard";
  const getMsg = (el: HTMLElement | null, key: string): string => {
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
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
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
    scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
    return;
  }
  const ensureToastContainer = (): HTMLElement => {
    const id = "np-toast-container";
    const c = qs<HTMLElement>("#" + id);
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
  const showErrorNow = (message: string): void=> {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      const tid = "np-toast";
      let t = qs("#" + tid, container);
      if (!t) {
        t = document.createElement("div");
        t.id = tid;
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
  const bindWithObserver = (
    el: HTMLElement,
    evt: string,
    handler: (e: Event) => void,
    flag: string,
  ): void => {
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
  const printAreaSafely = (): void => {
    const src = qs<HTMLElement>("#printableArea");
    if (!src) {
      scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
      return;
    }
    let iframe = qs<HTMLIFrameElement>("#np-print-iframe");
    if (!iframe) {
      const newIframe = document.createElement("iframe");
      newIframe.id = "np-print-iframe";
      newIframe.style.position = "fixed";
      newIframe.style.right = "0";
      newIframe.style.bottom = "0";
      newIframe.style.width = "0";
      newIframe.style.height = "0";
      newIframe.style.border = "0";
      document.body.appendChild(newIframe);
      iframe = newIframe;
    }
    const doc = iframe.contentWindow?.document;
    if (!doc) {
      scheduleInteractiveError(getMsg(src, "print_unavailable"));
      return;
    }
    const cssNodes = qsa(
      'link[rel="stylesheet"], style',
      document.head ?? document,
    ).map(n => n.cloneNode(true));
    doc.open();
    doc.write("<!doctype html><html><head></head><body></body></html>");
    doc.close();
    cssNodes.forEach(n => doc.head.appendChild(n));
    const wrapper = doc.createElement("div");
    wrapper.innerHTML = src.innerHTML;
    doc.body.appendChild(wrapper);
    const done = (): void => {
      try {
        iframe.contentWindow?.focus();
        iframe.contentWindow?.print();
      } catch (_) {
        scheduleInteractiveError(getMsg(src, "print_unavailable"));
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
  const onFilterClick = (): void => {
    $("#show_filter").toggle();
  };
  const init = (): void => {
    const filterBtn = document.getElementById("filter");
    if (!filterBtn) return;
    bindWithObserver(
      filterBtn,
      "click",
      onFilterClick,
      dataListenerGuard + "-filter",
    );
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
