/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;

  const dataInit = "data-zoomdel-bound";
  const dataErr = "data-zoomdel-error";
  const ns = "._npZoomDel";
  const qs = (s, r = document) => r.querySelector(s);
  const ensureConfirm = () => {
    const btn = $(".confirm_yes");
    if (!btn.length) {
      guard.scheduleInteractiveError(guard.getMsg("confirm_unavailable"));
      return null;
    }
    return btn;
  };
  const buildDeleteUrl = (id, el) => {
    const url = el?.getAttribute?.("data-url");
    const href = el?.getAttribute?.("href");
    if ((!url || url === "#") && (!href || href === "#")) {
      const fallback =
        "{{ url('zoom-meeting') }}".replace(/\/$/, "") + "/" + id;
      return fallback;
    }
    return url && url !== "#" ? url : href;
  };
  const onOpenConfirm = function () {
    const rid = this.getAttribute("data-id") || $(this).attr("data-id") || "";
    const $c = ensureConfirm();
    if (!$c) return;
    $c.removeClass("m_remove");
    $c.addClass("m_remove");
    $c.attr("uid", rid);
    try {
      $("#cModal").modal("show");
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("confirm_unavailable"));
    }
  };
  const onConfirmDelete = function () {
    const id = this.getAttribute("uid") || "";
    const targetEl = this;
    const url = buildDeleteUrl(id, targetEl);
    const urlAttr = targetEl.getAttribute("data-url");
    const hrefAttr = targetEl.getAttribute("href");
    if (
      (!urlAttr || urlAttr === "#") &&
      (!hrefAttr || hrefAttr === "#") &&
      (!url || url === "#")
    ) {
      guard.scheduleInteractiveError(guard.getMsg("route_unavailable"));
      return;
    }
    if (typeof window.deleteAjax !== "function") {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("deleteAjax unavailable");
      } catch (_) {}
      guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
      return;
    }
    const data = { id: id };
    window.deleteAjax(url, data, function (res) {
      try {
        if (typeof window.toastrs === "function") {
          window.toastrs(res?.flag, res?.msg);
        }
        if (res?.flag === 1) {
          window.location.reload();
        }
        try {
          $("#cModal").modal("hide");
        } catch (_) {}
      } catch (_) {
        guard.scheduleInteractiveError(guard.getMsg("delete_unavailable"));
      }
    });
  };
  const bind = () => {
    if (!(window.jQuery && window.jQuery.fn)) return;
    const host = document.body;
    if (host.getAttribute(dataInit) === "true") return;
    host.setAttribute(dataInit, "true");
    $(document).on("click" + ns, ".member_remove", onOpenConfirm);
    $(document).on("click" + ns, ".confirm_yes.m_remove", onConfirmDelete);
    const mo = new MutationObserver(function () {
      if (!$(".member_remove").length && !$(".confirm_yes.m_remove").length) {
        $(document).off("click" + ns, ".member_remove", onOpenConfirm);
        $(document).off("click" + ns, ".confirm_yes.m_remove", onConfirmDelete);
        host.removeAttribute(dataInit);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  } else {
    bind();
  }
})();
