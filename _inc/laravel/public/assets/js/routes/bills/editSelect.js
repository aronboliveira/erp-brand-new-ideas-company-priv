(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const select = document.getElementById("product-select");
  if (!select || select.getAttribute("data-listener-active") === "true") return;
  select.setAttribute("data-listener-active", "true");

  select.addEventListener("change", async () => {
    try {
      const url = select.getAttribute("data-url");
      if (!url || url === "#") {
        const msg =
          select.getAttribute("data-guard-msg") ||
          getMsg("bill_product_select_unavailable");
        scheduleError(msg, "change");
        select.setAttribute("data-failed-route", "true");
        return;
      }

      const productId = select.value ?? "";
      const response = await fetch(
        `${url}?product_id=${encodeURIComponent(productId)}`,
        {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        },
      );
      if (!response.ok) throw new Error(`Network error: ${response.status}`);
      const data = await response.json();

      document.querySelectorAll("[data-product-field]").forEach(el => {
        const key = el.getAttribute("data-product-field");
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
