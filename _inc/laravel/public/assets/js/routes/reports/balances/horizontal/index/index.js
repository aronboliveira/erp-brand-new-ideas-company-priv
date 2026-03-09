(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const f = document.getElementById("report_bill_summary");
    if (!f) return;
    const flag = "data-submit-listener";
    if (f.hasAttribute(flag) && f.getAttribute(flag) === "true") return;
    f.setAttribute(flag, "true");
    f.addEventListener(
      "submit",
      function (e) {
        try {
          const action = f.getAttribute("action") || "#";
          const url = f.getAttribute("data-url") || action || "#";
          if (action !== "#" && url !== "#") return;
          e.preventDefault();
          const msg = f.getAttribute("data-guard-msg") || getMsg("view_balance_sheet_unavailable");
          scheduleError(msg, "submit");
          f.setAttribute("data-failed-route", "true");
        } catch (err) {}
            inst.show();
          } else {
            alert(msg);
          }
          f.setAttribute("data-failed-route", "true");
        } catch (_) {}
      },
      { passive: false }
    );
  } catch (_) {}
})();
