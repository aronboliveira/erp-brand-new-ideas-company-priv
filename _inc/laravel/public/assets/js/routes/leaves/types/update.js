(() => {
  "use strict";

  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const form = document.getElementById("leaveType-edit-form");
  if (!form) return;

  const guardMsg =
    form.getAttribute("data-guard-msg") ||
    getMsg("update_leavetype_unavailable");
  const submitBtn = form.querySelector('input[type="submit"]');
  const titleEl = form.querySelector("#title");
  const daysEl = form.querySelector("#days");

  const actionIsBlocked = () => {
    const act = form.getAttribute("action") || "#";
    return !act || act === "#";
  };

  const validate = () => {
    if (titleEl && !titleEl.value.trim()) {
      titleEl.focus();
      return false;
    }
    if (daysEl) {
      const v = Number(daysEl.value);
      if (Number.isNaN(v) || v < 0) {
        daysEl.focus();
        return false;
      }
    }
    return true;
  };

  if (actionIsBlocked() && submitBtn) {
    submitBtn.addEventListener("click", e => {
      e.preventDefault();
      scheduleError(guardMsg, "click");
    });
  }

  form.addEventListener("submit", e => {
    if (actionIsBlocked()) {
      e.preventDefault();
      scheduleError(guardMsg, "submit");
      return;
    }
    if (!validate()) {
      e.preventDefault();
    }
  });
})();
