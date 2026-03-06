/**
 * @fileoverview TypeScript version of public/assets/js/routes/bills/editSelect.js
 * @generated from original JavaScript - manual review recommended
 * @module editSelect
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const select = document.getElementById("product-select");
  if (!select || select.getAttribute("data-listener-active") === "true") return;
  select.setAttribute("data-listener-active", "true");

  select.addEventListener("change", async (): void => {
    try {
      const url = select.getAttribute("data-url");
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!url || url === "#") {
        const msg = select.getAttribute("data-guard-msg");
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
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
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
        select.setAttribute("data-failed-route", "true");
        return;
      }

      const productId = select.value ?? "";
      const response = await fetch(
        `${url}?product_id=${encodeURIComponent(productId)}`,
        {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        }
      );
      if (!response.ok) throw new Error(`Network error: ${response.status}`);
      const data = await response.json();

      document.querySelectorAll("[data-product-field]").forEach((el: Element): void => {
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

export {};
