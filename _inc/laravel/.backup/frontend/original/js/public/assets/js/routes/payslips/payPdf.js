(() => {
  const Q = s => document.querySelector(s);
  const QA = s => Array.from(document.querySelectorAll(s));
  const CLICK_SEL = '[data-action="save-pdf"]';
  const ATTR_GUARD = "data-guard-msg";
  const ATTR_LOCALIZED = "data-sv-localized";
  const ONCE = "data-guard-once";
  const DEFAULT_ERR =
    "Save as PDF is unavailable. Please contact technical support or your domain administrator.";

  const hasBs = () =>
    !!(
      document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]') &&
      window.bootstrap
    );

  const getLang = () => {
    const fromStorage = (
      window.sessionStorage.getItem("erp-np-lang") || ""
    ).trim();
    const fromDoc = (document.documentElement.lang || "").trim();
    const lang = (fromStorage || fromDoc || "en")
      .toLowerCase()
      .replace(/_/g, "-");
    return lang === "pt-br" ? lang : lang.slice(0, 2);
  };

  const localizeGuard = el => {
    if (!el) return DEFAULT_ERR;
    if (el.getAttribute(ATTR_LOCALIZED) === "true")
      return el.getAttribute(ATTR_GUARD) || DEFAULT_ERR;
    const lang = getLang();
    const key = "savepdf_unavailable";
    const msg =
      (window.translations &&
        window.translations[lang] &&
        window.translations[lang][key]) ||
      el.getAttribute(ATTR_GUARD) ||
      (window.translations &&
        window.translations["en"] &&
        window.translations["en"][key]) ||
      DEFAULT_ERR;
    el.setAttribute(ATTR_GUARD, msg);
    el.setAttribute(ATTR_LOCALIZED, "true");
    return msg;
  };

  const toast = message => {
    const text = message || DEFAULT_ERR;
    if (hasBs()) {
      const wrapId = "toast-wrap-guard";
      if (!Q("#" + wrapId)) {
        const wrap = document.createElement("div");
        wrap.id = wrapId;
        wrap.className = "position-fixed top-0 end-0 p-3";
        wrap.style.zIndex = "1080";
        document.body.appendChild(wrap);
      }
      const id = "toast-savepdf-error";
      const node = document.createElement("div");
      node.id = id;
      node.className = "toast align-items-center text-bg-danger border-0";
      node.setAttribute("role", "alert");
      node.setAttribute("aria-live", "assertive");
      node.setAttribute("aria-atomic", "true");
      node.innerHTML =
        '<div class="d-flex"><div class="toast-body">' +
        text +
        '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
      Q("#" + wrapId).appendChild(node);
      new window.bootstrap.Toast(node, { autohide: true, delay: 4000 }).show();
    } else {
      alert(text);
    }
  };

  const doSavePdf = btn => {
    try {
      const area = Q("#printableArea");
      if (!area || typeof html2pdf === "undefined" || !html2pdf?.().set)
        throw new Error("missing");
      const filename = (
        Q(".invoice .invoice-title h4")?.textContent || "document"
      ).trim();
      const opt = {
        margin: 0.3,
        filename,
        image: { type: "jpeg", quality: 1 },
        html2canvas: { scale: 4, dpi: 72, letterRendering: true },
        jsPDF: { unit: "in", format: "A4" },
      };
      html2pdf()
        .set(opt)
        .from(area)
        .save()
        .catch(() => toast(localizeGuard(btn)));
    } catch (e) {
      toast(localizeGuard(btn));
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("html2pdf unavailable or printable area missing");
      } catch (_) {}
    }
  };

  const bind = btn => {
    if (!btn || btn.getAttribute(ONCE) === "true") return;
    btn.setAttribute(ONCE, "true");
    localizeGuard(btn);
    btn.addEventListener(
      "click",
      ev => {
        ev.preventDefault();
        doSavePdf(btn);
      },
      { passive: true }
    );
  };

  document.addEventListener("DOMContentLoaded", () => {
    QA(CLICK_SEL).forEach(bind);
  });

  const mo = new MutationObserver(m => {
    m.forEach(
      r =>
        r.addedNodes &&
        r.addedNodes.forEach(n =>
          n.matches?.(CLICK_SEL)
            ? bind(n)
            : n.querySelectorAll?.(CLICK_SEL).forEach(bind)
        )
    );
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });

  window.saveAsPDF = () => doSavePdf(Q(CLICK_SEL) || document.body);
})();
