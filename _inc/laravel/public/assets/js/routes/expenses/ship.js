(function () {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBound = "data-shipping-bound";
  const dataArmed = "data-shipping-error-armed";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = () => {
    let c = qs("#np-toast-container");
    if (c) return c;
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showToast = message => {
    const container = ensureToastContainer();
    let t = qs("#np-toast", container);
    if (!t) {
      t = document.createElement("div");
      t.id = "np-toast";
      t.className = "toast";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      t.innerHTML =
        '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
      container.appendChild(t);
    }
    const body = t.querySelector(".toast-body");
    if (body) body.textContent = message ?? errFb;
    new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
  };
  const notifyError = (host, msg) => {
    if (!host || host.getAttribute(dataArmed) === "true") return;
    host.setAttribute(dataArmed, "true");
    const handler = () => {
      try {
        hasBS() ? showToast(msg) : alert(msg);
      } finally {
        host.removeAttribute(dataArmed);
      }
    };
    document.addEventListener("pointerup", handler, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", handler);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const localize = function (el, msgKey) {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const k = msgKey;
      msg =
        window.translations?.[lang]?.[k] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.["en"]?.[k] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const bindOnce = el => {
    if (!el || el.getAttribute(dataBound) === "true") return;
    el.setAttribute(dataBound, "true");
    const handler = function () {
      const $ = window.jQuery;
      if (!$ || !$.ajax) {
        try {
          console.error("jQuery or $.ajax unavailable");
        } catch (_) {}
        notifyError(document.body, localize(el, "shipping_unavailable"));
        return;
      }
      const url = el.getAttribute("data-url");
      let href = null;
      const isAnchor = el.tagName && el.tagName.toLowerCase() === "a";
      if (isAnchor) href = el.getAttribute("href");
      else if (el.form) href = el.form.getAttribute("action");
      if ((!url || url === "#") && (!href || href === "#")) {
        hasBS()
          ? showToast(localize(el, "shipping_unavailable"))
          : alert(localize(el, "shipping_unavailable"));
        return;
      }
      const is_display = $("#shipping").is(":checked");
      try {
        $.ajax({
          url: url || href,
          type: "get",
          data: { is_display: is_display },
        }).fail(function () {
          hasBS()
            ? showToast(localize(el, "shipping_unavailable"))
            : alert(localize(el, "shipping_unavailable"));
        });
      } catch (_) {
        hasBS()
          ? showToast(localize(el, "shipping_unavailable"))
          : alert(localize(el, "shipping_unavailable"));
      }
    };
    window.jQuery(el).on("pointerup", handler);
    const mo = new MutationObserver(function () {
      if (!document.body.contains(el)) {
        try {
          window.jQuery(el).off("pointerup", handler);
        } catch (_) {}
        mo.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const init = () => {
    const el = qs("#shipping");
    if (el) bindOnce(el);
    const mo = new MutationObserver(function () {
      const s = qs("#shipping");
      if (s) bindOnce(s);
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
