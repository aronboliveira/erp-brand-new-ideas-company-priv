/**
 * @fileoverview TypeScript version of public/assets/js/routes/bills/select.js
 * @generated from original JavaScript - manual review recommended
 * @module select
 */

((): void => {
  const select = document.getElementById("vendor_select");
  if (!select || select.getAttribute("data-listener-active") === "true") return;
  select.setAttribute("data-listener-active", "true");

  const urlAttr = "data-url";
  const guardMsgAttr = "data-guard-msg";
  const failedAttr = "data-failed-route";

  // eslint-disable-next-line @typescript-eslint/no-misused-promises
  select.addEventListener("change", async (): Promise<void> => {
    try {
      const url = select.getAttribute(urlAttr);
      if (!url || url === "#") {
        const msg = select.getAttribute(guardMsgAttr);
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (bootstrapLink && window.bootstrap) {
          const toastEl = document.createElement("div");
          toastEl.className = "toast";
          for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toastEl.appendChild(body);
          container.appendChild(toastEl);
          bootstrap.Toast.getOrCreateInstance(toastEl).show();
        } else {
          alert(msg);
        }
        select.setAttribute(failedAttr, "true");
        return;
      }

      const vendorId = (select as HTMLSelectElement).value ?? "";
      const response = await fetch(
        `${url}?vendor_id=${encodeURIComponent(vendorId)}`,
        {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        },
      );

      if (!response.ok) {
        throw new Error(`Network error: ${response.status}`);
      }

      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
      const data = await response.json();
      document
        .querySelectorAll("[data-vendor-field]")
        .forEach((el: Element): void => {
          const key = el.getAttribute("data-vendor-field") ?? "";
          // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
          // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-assignment
          const val = data[key] ?? "";
          if (el.tagName === "INPUT" || el.tagName === "TEXTAREA") {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
            (el as HTMLInputElement).value = val;
          } else {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
            el.textContent = val;
          }
        });
    } catch (e) {
    console.error(`[select] Error:`, e);
  }
  });
})();

export {};
