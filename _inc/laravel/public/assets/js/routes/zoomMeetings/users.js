(() => {
  const $ = window.jQuery;
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("click", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";
  const routeGuard = url => !url || url === "#";

  if (!$ || !$.fn) {
    scheduleError(getMsg("plugin_unavailable"));
    return;
  }
  const BASE = "{{ url('zoom-meeting/projects/select') }}";
  const userDiv = $("#user_div");
  const SELECT_ID = "user_id";
  const SELECT_HTML = `<select class="form-control" id="${SELECT_ID}" name="user_id[]" multiple></select>`;

  const buildOrReuseSelect = () => {
    let $sel = $("#" + SELECT_ID);
    if (!$sel.length) {
      if (!userDiv.children("#" + SELECT_ID).length)
        userDiv.append(SELECT_HTML);
      $sel = $("#" + SELECT_ID);
    } else {
      $sel.empty();
    }
    return $sel;
  };

  const choicesKey = "_npChoicesInstance";
  const ensureChoices = sel => {
    if (typeof window.Choices !== "function") {
      scheduleError(getMsg("plugin_unavailable"));
      return null;
    }
    if (sel[0][choicesKey]) {
      try {
        sel[0][choicesKey].destroy();
      } catch {}
      sel[0][choicesKey] = null;
    }
    const inst = new Choices(sel[0], { removeItemButton: true });
    sel[0][choicesKey] = inst;
    const mo = new MutationObserver((_, o) => {
      if (!document.body.contains(sel[0])) {
        try {
          inst.destroy();
        } catch {}
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
    return inst;
  };

  const fetchUsers = projectId => {
    const pid = projectId ?? "";
    const url = `${BASE}/${encodeURIComponent(pid)}`;
    if (routeGuard(url)) {
      scheduleError(getMsg("zoom_users_unavailable"));
      return;
    }
    $.ajax({
      url,
      type: "GET",
      success: data => {
        const list = Array.isArray(data) ? data : [];
        const $sel = buildOrReuseSelect();
        const frag = document.createDocumentFragment();
        for (const it of list) {
          const id = String(it?.id ?? "");
          const name = String(it?.name ?? "");
          if (!id) continue;
          const opt = document.createElement("option");
          opt.value = id;
          opt.textContent = name;
          frag.appendChild(opt);
        }
        $sel.append(frag);
        ensureChoices($sel);
        if (list.length === 0) $sel.empty();
      },
      error: () => scheduleError(getMsg("ajax_unavailable")),
    });
  };

  if (document.body.getAttribute("data-zoom-users-bound") !== "true") {
    $(document).on("change", ".project_select", function () {
      const projectId = $(this).val() ?? "";
      fetchUsers(projectId);
    });
    document.body.setAttribute("data-zoom-users-bound", "true");
  }
})();
