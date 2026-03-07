/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/labels.js
 * @generated from original JavaScript - manual review recommended
 * @module labels
 */

/* global bootstrap, $, jQuery */
((): void => {
  const $ = window.jQuery;
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery not found for labelsGuard.js");
    } catch (_) {}
    return;
  }

  const ERR_FB = "# ERROR";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  const DPL = "data-pointer-listener";
  const FORM_ID = "leads-labels-form";
  const MSG_KEY = "leads_labels_store_route_unavailable";

  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');

  const getMsg = (el: HTMLElement) => {
    let msg = ERR_FB;
    if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true") {
      msg = el.getAttribute(DGM) || ERR_FB;
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
        window.translations?.[lang]?.[MSG_KEY] ||
        el.getAttribute(DGM) ||
        window.translations?.en?.[MSG_KEY] ||
        ERR_FB;
      if (msg !== ERR_FB) {
        el.setAttribute(DGM, msg);
        el.setAttribute(DCL, "true");
      }
    }
    return msg;
  };

  const showError = (el: HTMLElement) => {
    const msg = getMsg(el);
    if (hasBootstrapCss() && window.bootstrap) {
      let wrap = document.getElementById("toast-wrap-leads-labels");
      if (!wrap) {
        wrap = document.createElement("div");
        wrap.id = "toast-wrap-leads-labels";
        wrap.className = "position-fixed top-0 end-0 p-3";
        wrap.style.zIndex = "1080";
        document.body.appendChild(wrap);
      }
      const t = document.createElement("div");
      t.className = "toast align-items-center text-bg-danger border-0";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      t.innerHTML =
        '<div class="d-flex"><div class="toast-body">' +
        msg +
        '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
      wrap.appendChild(t);
      new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
    } else {
      alert(msg);
    }
  };

  const handlersPointer = new WeakMap();

  const bindFormPointerGuard = (form: HTMLFormElement) => {
    if (form.getAttribute(DPL) === "true") return;
    form.setAttribute(DPL, "true");
    const $btns = $(form).find('button[type="submit"], input[type="submit"]');
    if (!$btns.length) return;
    const h = (e: Event) => {
      try {
        const url = form.getAttribute("data-url");
        const action = form.getAttribute("action");
        if ((!url || url === "#") && (!action || action === "#")) {
          e.preventDefault();
          e.stopPropagation();
          showError(form);
        }
      } catch (_) {}
    };
    handlersPointer.set(form, h);
    $btns.each(function (): void {
      $(this).on("pointerup", h);
    });
  };

  const unbindFormPointerGuard = (form: HTMLFormElement) => {
    const h = handlersPointer.get(form);
    if (h) {
      $(form)
        .find('button[type="submit"], input[type="submit"]')
        .each(function (): void {
          $(this).off("pointerup", h);
        });
      handlersPointer.delete(form);
    }
    form.removeAttribute(DPL);
  };

  const scan = (root: Document | Element) => {
    const form = root.querySelector<HTMLFormElement>("#" + FORM_ID);
    if (form) bindFormPointerGuard(form);
  };

  const ready = (): void => {
    try {
      scan(document);
    } catch (_) {}
  };
  if (document.readyState === "loading") {
    $(ready);
  } else {
    ready();
  }

  const mo = new MutationObserver(muts => {
    muts.forEach(m => {
      m.addedNodes &&
        m.addedNodes.forEach(n => {
          if (n.nodeType === 1) scan(n as Element);
        });
      m.removedNodes &&
        m.removedNodes.forEach(n => {
          if (n.nodeType === 1) {
            const el = n as HTMLElement;
            if (el.id === FORM_ID)
              unbindFormPointerGuard(el as HTMLFormElement);
            el.querySelectorAll<HTMLFormElement>("#" + FORM_ID).forEach(
              unbindFormPointerGuard,
            );
          }
        });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();

export {};
