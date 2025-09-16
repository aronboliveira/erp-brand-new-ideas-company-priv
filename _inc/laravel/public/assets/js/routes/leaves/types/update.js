(() => {
  "use strict";

  const form = document.getElementById("leaveType-edit-form");
  if (!form) return;

  const guardMsg =
    form.getAttribute("data-guard-msg") || "Update route is unavailable.";
  const submitBtn = form.querySelector('input[type="submit"]');
  const titleEl = form.querySelector("#title");
  const daysEl = form.querySelector("#days");

  const actionIsBlocked = () => {
    const act = form.getAttribute("action") || "#";
    return !act || act === "#";
  };

  const showErr = msg => {
    try {
      alert(msg);
    } catch {
      /* no-op */
    }
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
      showErr(guardMsg);
    });
  }

  form.addEventListener("submit", e => {
    if (actionIsBlocked()) {
      e.preventDefault();
      showErr(guardMsg);
      return;
    }
    if (!validate()) {
      e.preventDefault();
    }
  });
})();
