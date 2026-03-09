/**
 * @file Product & Services Index Route Guard
 * @description Guards product filter form, apply button, and reset link using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    const fm = document.getElementById("product-service-filter-form");
    if (!fm) return;

    guard.bindSubmitGuard("#product-service-filter-form", {
      fallbackMsg:
        "Product & Service index route is unavailable. Please contact technical support or your domain administrator.",
    });

    const applyBtn = document.getElementById("product-service-apply-btn");
    if (applyBtn) {
      guard.bindClickGuard("#product-service-apply-btn", {
        fallbackMsg:
          "Product & Service index route is unavailable. Please contact technical support or your domain administrator.",
        onInvalid: e => {
          e.preventDefault();
        },
        onValid: e => {
          e.preventDefault();
          if (fm && typeof fm.submit === "function") {
            fm.submit();
          }
        },
      });
    }

    guard.bindClickGuard("#product-service-reset-link", {
      fallbackMsg:
        "Product & Service index route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (err) {
    console.error("Error initializing products/services index guard:", err);
  }
})();
