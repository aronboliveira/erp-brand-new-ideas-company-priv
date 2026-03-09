/**
 * @file Bank Transfers Index Route Guard
 * @description Guards transfer form, apply button, and reset link using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    guard.bindSubmitGuard("#transfer_form", {
      fallbackMsg:
        "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.",
    });

    const apply = document.getElementById("transfer-apply");
    if (apply) {
      guard.bindClickGuard("#transfer-apply", {
        fallbackMsg:
          "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.",
        onInvalid: e => {
          e.preventDefault();
          const fid = apply.getAttribute("data-form-id") ?? "";
          if (fid) {
            const form = document.getElementById(fid);
            if (form) form.setAttribute("data-failed-route", "true");
          }
        },
        onValid: e => {
          e.preventDefault();
          const fid = apply.getAttribute("data-form-id") ?? "";
          if (!fid) return;
          const form = document.getElementById(fid);
          if (!form) return;
          const action = form.getAttribute("action") ?? "#";
          const url = form.getAttribute("data-url") ?? action ?? "#";
          if (url === "#" || action === "#") {
            guard.showToast(
              apply.getAttribute("data-guard-msg") ??
                "Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.",
            );
            apply.setAttribute("data-failed-route", "true");
            form.setAttribute("data-failed-route", "true");
            return;
          }
          form.submit();
        },
      });
    }

    guard.bindClickGuard("#transfer-reset", {
      fallbackMsg:
        "Reset bank transfer route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (err) {
    console.error("Error initializing bank/transfers index guard:", err);
  }
})();
