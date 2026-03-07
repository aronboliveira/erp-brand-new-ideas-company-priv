/**
 * @fileoverview TypeScript version of public/assets/js/routes/bills/vendorEditSelect.js
 * @generated from original JavaScript - manual review recommended
 * @module vendorEditSelect
 */

/* global bootstrap, $, jQuery */
((): void => {
  const select = document.getElementById("vendor_select");
  if (!select || select.getAttribute("data-listener-active") === "true") return;
  select.setAttribute("data-listener-active", "true");

  const urlAttr = "data-url";
  const guardMsgAttr = "data-guard-msg";
  const failedAttr = "data-failed-route";

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
          document.body.appendChild(container);
        }
        if (bootstrapLink && window.bootstrap) {
          const toastEl = document.createElement("div");
          toastEl.className = "toast";
          toastEl.setAttribute("role", "alert");
          toastEl.setAttribute("aria-live", "assertive");
          toastEl.setAttribute("aria-atomic", "true");
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

      const data = await response.json();
      document
        .querySelectorAll("[data-vendor-field]")
        .forEach((el: Element): void => {
          const key = el.getAttribute("data-vendor-field") ?? "";
          const val = data[key] ?? "";
          if (el.tagName === "INPUT" || el.tagName === "TEXTAREA") {
            (el as HTMLInputElement).value = val;
          } else {
            el.textContent = val;
          }
        });
    } catch (e) {}
  });
})();

export {};
