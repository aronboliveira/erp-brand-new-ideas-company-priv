/**
 * @file Bills Vendor Select Route Guard
 * @description Guards vendor select change events and populates fields with ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindChangeGuard("#vendor_select", {
      fallbackMsg:
        "Vendor select route is unavailable. Please contact technical support or your domain administrator.",
      async handler(event, element) {
        const vendorId = element.value ?? "";
        const url = element.getAttribute("data-url");
        const response = await fetch(
          `${url}?vendor_id=${encodeURIComponent(vendorId)}`,
          { headers: { "X-Requested-With": "XMLHttpRequest" } },
        );
        if (!response.ok) throw new Error(`Network error: ${response.status}`);
        const data = await response.json();
        document.querySelectorAll("[data-vendor-field]").forEach(el => {
          const key = el.getAttribute("data-vendor-field");
          const val = data[key] ?? "";
          if (el.tagName === "INPUT" || el.tagName === "TEXTAREA")
            el.value = val;
          else el.textContent = val;
        });
      },
    });
  } catch (_) {}
})();
