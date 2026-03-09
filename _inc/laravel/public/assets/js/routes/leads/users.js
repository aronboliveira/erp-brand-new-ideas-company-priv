(() => {
  const $ = window.jQuery;
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery not found for usersGuard.js");
    } catch (_) {}
    return;
  }

  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const DGM = "data-guard-msg";
  const DPL = "data-pointer-listener";
  const FORM_ID = "leads-users-form";

  const handlersPointer = new WeakMap();

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
          const msg =
            form.getAttribute(DGM) ||
            getMsg("leads_users_update_route_unavailable");
          scheduleError(msg, "pointerup");
        }
      } catch (_) {}
    };
    handlersPointer.set(form, h);
    $btns.each(function () {
      $(this).on("pointerup", h);
    });
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
    const form = (root || document).getElementById(FORM_ID);
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
            if (n.id === FORM_ID) unbindFormPointerGuard(n);
            n.querySelectorAll?.("#" + FORM_ID).forEach(unbindFormPointerGuard);
          }
        });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();
