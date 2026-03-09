(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const MSG_KEY = "pos_route_unavailable";

  guard.bindClickGuard("a[data-guard-msg]", {
    fallbackMsg:
      "POS route is unavailable. Please contact technical support or your domain administrator.",
    validateUrl: true,
    updateHref: true,
  });

  // DataTable initialization
  const init = () => {
    try {
      // Try jQuery DataTables first
      if ($.fn && $.fn.DataTable) {
        const $t = $(".datatable").filter(
          (i, el) => el.getAttribute("data-dt-init") !== "true",
        );
        if (!$t.length) return;
        $t.each(function () {
          $(this).attr("data-dt-init", "true").DataTable({ order: [] });
        });
        return;
      }
      // Fallback: simple-datatables (vanilla)
      if (window.simpleDatatables && window.simpleDatatables.DataTable) {
        document.querySelectorAll(".datatable").forEach(el => {
          if (el.getAttribute("data-dt-init") === "true") return;
          el.setAttribute("data-dt-init", "true");
          new window.simpleDatatables.DataTable(el);
        });
      }
    } catch (_) {}
  };

  if (document.readyState === "loading") {
    $(init);
  } else {
    init();
  }
})();
