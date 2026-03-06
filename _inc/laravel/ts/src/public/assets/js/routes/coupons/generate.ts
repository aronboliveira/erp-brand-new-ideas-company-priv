/**
 * @fileoverview TypeScript version of public/assets/js/routes/coupons/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const aiBtn = document.getElementById("coupon-generate-ai-btn");
  if (aiBtn?.getAttribute("data-listener-active") !== "true") {
    aiBtn.setAttribute("data-listener-active", "true");
    aiBtn.addEventListener("click", event => {
      try {
        const url = aiBtn.getAttribute("data-url");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (!url || url === "#") {
          event.preventDefault();
          const msg = aiBtn.getAttribute("data-guard-msg") ?? "# ERROR";
          const bootstrapLink = document.querySelector(
            'link[href*="bootstrap"]'
          );
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
          aiBtn.setAttribute("data-failed-route", "true");
          return;
        }
      } catch (e) {}
    });
  }

  const manualRad = document.getElementById("manual_code");
  const autoRad = document.getElementById("auto_code");
  const manualDiv = document.getElementById("manual");
  const autoDiv = document.getElementById("auto");
  const generateBtn = document.getElementById("code-generate");

  if (manualRad && autoRad && manualDiv && autoDiv) {
    const toggle = (): void => {
      if (manualRad.checked) {
        manualDiv.classList.remove("d-none");
        autoDiv.classList.add("d-none");
      } else {
        autoDiv.classList.remove("d-none");
        manualDiv.classList.add("d-none");
      }
    };
    manualRad.addEventListener("change", toggle);
    autoRad.addEventListener("change", toggle);
    toggle();
  }

  if (
    generateBtn?.getAttribute("data-listener-active") !== "true"
  ) {
    generateBtn.setAttribute("data-listener-active", "true");
    generateBtn.addEventListener("click", event => {
      try {
        event.preventDefault();
        const input = document.getElementById("auto-code");
        if (!input) return;
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        let code = "";
        for (let i = 0; i < 8; i++) {
          code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        input.value = code;
      } catch (e) {}
    });
  }
})();

export {};
