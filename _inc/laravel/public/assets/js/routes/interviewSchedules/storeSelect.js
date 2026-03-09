(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;

  try {
    if (!$) {
      scheduleError(getMsg("plugin_unavailable"));
      return;
    }

    const candidate = $("select#candidate");
    const el = candidate.get(0);
    if (!el) return;

    const url = el.getAttribute("data-url");
    const href = (el.href || "")
      .replace(window.location.origin, "")
      .replace(window.location.pathname, "");
    if ((!url || url === "#") && (!href || href === "#")) return;

    const candidateVal = candidate.val();
    if (candidateVal == null) return;

    candidate.val(candidateVal).trigger("change");
  } catch (_) {
    scheduleError(getMsg("candidate_unavailable"));
  }
})();
