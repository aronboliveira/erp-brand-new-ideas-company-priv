(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const fm = document.getElementById("fm-bind-store-form");
    if (fm && fm.getAttribute("data-submit-guarded") !== "true") {
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", e => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim();
          const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
          if (url !== "#" && action !== "#") return;
          e.preventDefault();
          const msg = fm.getAttribute("data-guard-msg") || getMsg("form_bind_store_unavailable");
          scheduleError(msg, "submit");
          fm.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }

    const radios = document.querySelectorAll(".lead_radio");
    const section = document.getElementById("lead_activated");
    const applyLeadToggle = () => {
      try {
        const on = Array.from(radios).find(r => r.checked)?.value === "1";
        if (!section) return;
        if (on) {
          section.classList.remove("d-none");
        } else {
          section.classList.add("d-none");
        }
      } catch (err) {}
    };
    if (radios.length) {
      radios.forEach(r => r.addEventListener("change", applyLeadToggle));
      applyLeadToggle();
    }

    const empLink = document.getElementById("employee-index-link");
    if (empLink && empLink.getAttribute("data-listener-active") !== "true") {
      empLink.setAttribute("data-listener-active", "true");
      empLink.addEventListener("click", e => {
        try {
          const href = (empLink.getAttribute("href") ?? "#").trim();
          const url = (empLink.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg = empLink.getAttribute("data-guard-msg") || getMsg("employee_index_unavailable");
          scheduleError(msg, "click");
          empLink.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    }
  } catch (err) {}
})();
