/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/sources.js
 * @generated from original JavaScript - manual review recommended
 * @module sources
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const $ = window.jQuery;
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery not found for sourcesGuard.js");
    } catch (_) {}
    return;
  }

  const ERR_FB = "# ERROR";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  const DPL = "data-pointer-listener";
  const FORM_ID = "leads-sources-form";
  const MSG_KEY = "leads_sources_update_route_unavailable";

  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');

  const getMsg = el => {
    let msg = ERR_FB;
    if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true") {
      msg = el.getAttribute(DGM) || ERR_FB;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
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

  const showError = el => {
    const msg = getMsg(el);
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    if (hasBootstrapCss() && window.bootstrap) {
      let wrap = document.getElementById("toast-wrap-leads-sources");
      if (!wrap) {
        wrap = document.createElement("div");
        wrap.id = "toast-wrap-leads-sources";
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

  const bindFormPointerGuard = form => {
    if (!form || form.getAttribute(DPL) === "true") return;
    form.setAttribute(DPL, "true");
    const $btns = $(form).find('button[type="submit"], input[type="submit"]');
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (!$btns.length) return;
    const h = e => {
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

  const unbindFormPointerGuard = form => {
    const h = handlersPointer.get(form);
    if (h) {
      $(form)
        .find('button[type="submit"], input[type="submit"]')
        .each(function (): void {
          $(this).off("pointerup", h);
        });
      handlersPointer.delete(form);
    }
    form?.removeAttribute?.(DPL);
  };

  const scan = root => {
    const form = (root || document).getElementById(FORM_ID);
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
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      m.addedNodes &&
        m.addedNodes.forEach(n => {
          if (n.nodeType === 1) scan(n);
        });
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      m.removedNodes &&
        m.removedNodes.forEach(n => {
          if (n.nodeType === 1) {
            if (n.id === FORM_ID) unbindFormPointerGuard(n);
            n.querySelectorAll?.("#" + FORM_ID).forEach(unbindFormPointerGuard);
          }
        });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();

export {};
