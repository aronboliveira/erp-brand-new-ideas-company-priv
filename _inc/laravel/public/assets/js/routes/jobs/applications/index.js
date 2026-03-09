/**
 * @file Job Applications Index Route Guard
 * @description Guards job applications routes, initializes tooltips, manages kanban counts, and sets up dragula using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  const initTooltips = () => {
    try {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
          window.bootstrap?.Tooltip.getOrCreateInstance(el);
        } catch {}
      });
    } catch {}
  };

  const updateCounts = () => {
    document.querySelectorAll(".kanban-box").forEach(box => {
      const cnt = box.querySelectorAll("> .card").length;
      const header = box.closest(".card")?.querySelector(".card-header .count");
      if (header) header.textContent = String(cnt);
    });
  };

  const initDragula = () => {
    const wrap = document.querySelector(".kanban-wrapper");
    if (!wrap || typeof dragula !== "function") return;
    let ids = [];
    try {
      ids = JSON.parse(wrap.getAttribute("data-containers") || "[]");
    } catch {
      ids = [];
    }
    const containers = ids
      .map(id => document.getElementById(id))
      .filter(Boolean);
    if (!containers.length) return;
    dragula(containers).on("drop", () => updateCounts());
  };

  document.addEventListener("DOMContentLoaded", () => {
    guard.bindClickGuard("a[data-guard-msg],a[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindSubmitGuard("form[data-guard-msg],form[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    initTooltips();
    updateCounts();
    initDragula();
  });
})();
