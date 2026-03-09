(() => {
  const $ = window.jQuery;
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery not found for callsGuard.js");
    } catch (_) {}
    return;
  }

  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const DGM = "data-guard-msg";
  const DLA = "data-listener-active";
  const DPL = "data-pointer-listener";
  const DMK = "data-msg-key";

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
          const key = el.getAttribute(DMK) || "ld_call_route_unavailable";
          const msg = el.getAttribute(DGM) || getMsg(key);
          scheduleError(msg, "click");
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
          const key = form.getAttribute(DMK) || "ld_call_route_unavailable";
          const msg = form.getAttribute(DGM) || getMsg(key);
          scheduleError(msg, "pointerup");
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
              unbindFormPointerGuard,
            );
          }
        });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();
