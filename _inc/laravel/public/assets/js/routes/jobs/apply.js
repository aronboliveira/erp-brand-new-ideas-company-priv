/**
 * @file Job Apply Route Guard
 * @description Guards job apply routes, initializes tooltips, and handles file input previews using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  const Q = s => document.querySelector(s);
  const QA = s => Array.from(document.querySelectorAll(s));

  const initTooltips = () => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
          window.bootstrap?.Tooltip.getOrCreateInstance(el);
        } catch {}
      });
    } catch {}
  };

  const filenameFromInput = inp => {
    if (!inp || !inp.files) return "";
    if (inp.files.length === 0) return "";
    if (inp.files.length === 1) return inp.files[0].name || "";
    return Array.from(inp.files)
      .map(f => f.name || "")
      .filter(Boolean)
      .join(", ");
  };

  const previewImage = (file, imgEl) => {
    if (!file || !imgEl) return;
    try {
      const url = URL.createObjectURL(file);
      imgEl.src = url;
      imgEl.onload = () => {
        try {
          URL.revokeObjectURL(url);
        } catch {}
      };
    } catch {}
  };

  const bindFileInputs = () => {
    QA('input[type="file"][data-filename]').forEach(inp => {
      if (inp.getAttribute("data-file-listener") === "true") return;
      inp.setAttribute("data-file-listener", "true");
      const outClass = inp.getAttribute("data-filename") || "";
      const out = outClass ? Q(`.${CSS.escape(outClass)}`) : null;
      inp.addEventListener("change", () => {
        const txt = filenameFromInput(inp);
        if (out) out.textContent = txt || "";
        const id = inp.id || "";
        if (id === "profile") previewImage(inp.files?.[0], Q("#blah"));
        if (id === "resume") previewImage(inp.files?.[0], Q("#blah1"));
      });
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    guard.bindClickGuard("a[data-guard-msg], a[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindSubmitGuard("form[data-guard-msg], form[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    initTooltips();
    bindFileInputs();
  });
})();
