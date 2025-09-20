(() => {
  const $ = window.jQuery;
  if (!$) {
    try {
      console.error("jQuery not found for callsGuard.js");
    } catch (_) {}
    return;
  }

  const ERR_FB = "# ERROR";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  const DLA = "data-listener-active";
  const DPL = "data-pointer-listener";
  const DMK = "data-msg-key";

  const hasBootstrapCss = () =>
    !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');

  const getMsg = (el, fallbackKey) => {
    let msg = ERR_FB;
    if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true") {
      msg = el.getAttribute(DGM) || ERR_FB;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const key = el.getAttribute(DMK) || fallbackKey;
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(DGM) ||
        window.translations?.en?.[key] ||
        ERR_FB;
      if (msg !== ERR_FB) {
        el.setAttribute(DGM, msg);
        el.setAttribute(DCL, "true");
      }
    }
    return msg;
  };

  const showError = (el, key) => {
    const msg = getMsg(el, key);
    if (hasBootstrapCss() && window.bootstrap) {
      let wrap = document.getElementById("toast-wrap-ld-calls");
      if (!wrap) {
        wrap = document.createElement("div");
        wrap.id = "toast-wrap-ld-calls";
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

  const handlersClick = new WeakMap();
  const handlersPointer = new WeakMap();

  const bindAnchorGuard = el => {
    if (!el || el.getAttribute(DLA) === "true") return;
    el.setAttribute(DLA, "true");
    const h = e => {
      try {
        const url = el.getAttribute("data-url");
        const href = el.getAttribute("href");
        if ((!url || url === "#") && (!href || href === "#")) {
          e.preventDefault();
          showError(el, "ld_call_route_unavailable");
        }
      } catch (_) {}
    };
    handlersClick.set(el, h);
    $(el).on("click", h);
  };

  const bindFormPointerGuard = form => {
    if (!form || form.getAttribute(DPL) === "true") return;
    form.setAttribute(DPL, "true");
    const $btns = $(form).find('button[type="submit"], input[type="submit"]');
    if (!$btns.length) return;
    const h = e => {
      try {
        const url = form.getAttribute("data-url");
        const action = form.getAttribute("action");
        if ((!url || url === "#") && (!action || action === "#")) {
          e.preventDefault();
          e.stopPropagation();
          showError(form, "ld_call_route_unavailable");
        }
      } catch (_) {}
    };
    handlersPointer.set(form, h);
    $btns.each(function () {
      $(this).on("pointerup", h);
    });
  };

  const unbindAnchorGuard = el => {
    const h = handlersClick.get(el);
    if (h) {
      $(el).off("click", h);
      handlersClick.delete(el);
    }
    el?.removeAttribute?.(DLA);
  };

  const unbindFormPointerGuard = form => {
    const h = handlersPointer.get(form);
    if (h) {
      $(form)
        .find('button[type="submit"], input[type="submit"]')
        .each(function () {
          $(this).off("pointerup", h);
        });
      handlersPointer.delete(form);
    }
    form?.removeAttribute?.(DPL);
  };

  const scan = root => {
    const scope = root || document;
    scope
      .querySelectorAll("a[" + DGM + "]:not([" + DLA + '="true"])')
      .forEach(bindAnchorGuard);
    const form = document.getElementById("ld-call-form");
    if (form) bindFormPointerGuard(form);
  };

  const ready = () => {
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
          if (n.nodeType === 1) scan(n);
        });
      m.removedNodes &&
        m.removedNodes.forEach(n => {
          if (n.nodeType === 1) {
            if (n.matches?.("a[" + DLA + "]")) unbindAnchorGuard(n);
            n.querySelectorAll?.("a[" + DLA + "]").forEach(unbindAnchorGuard);
            if (n.id === "ld-call-form") unbindFormPointerGuard(n);
            n.querySelectorAll?.("#ld-call-form").forEach(
              unbindFormPointerGuard
            );
          }
        });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();
