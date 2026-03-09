(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  const select = document.getElementById("vendor_select");
  if (!select || select.getAttribute("data-listener-active") === "true") return;
  select.setAttribute("data-listener-active", "true");

  const urlAttr = "data-url";
  const guardMsgAttr = "data-guard-msg";
  const failedAttr = "data-failed-route";

  select.addEventListener("change", async () => {
    try {
      const url = select.getAttribute(urlAttr);
      if (!url || url === "#") {
        const msg =
          select.getAttribute(guardMsgAttr) ||
          getMsg("bill_vendor_select_unavailable");
        scheduleError(msg, "change");
        select.setAttribute(failedAttr, "true");
        return;
      }

      const vendorId = select.value ?? "";
      const response = await fetch(
        `${url}?vendor_id=${encodeURIComponent(vendorId)}`,
        {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        },
      );

      if (!response.ok) {
        throw new Error(`Network error: ${response.status}`);
      }

      const data = await response.json();
      document.querySelectorAll("[data-vendor-field]").forEach(el => {
        const key = el.getAttribute("data-vendor-field");
        const val = data[key] ?? "";
        if (el.tagName === "INPUT" || el.tagName === "TEXTAREA") {
          el.value = val;
        } else {
          el.textContent = val;
        }
      });
    } catch (e) {}
  });
})();
