/**
 * @fileoverview TypeScript version of public/assets/js/routes/settings/invoices/preview.js
 * @generated from original JavaScript - manual review recommended
 * @module preview
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  try {
    const previewIframe = document.getElementById(
      "invoice-template-preview-frame"
    );
    if (!previewIframe) {
      return;
    }
    if (previewIframe.getAttribute("data-listener-active") === "true") {
      return;
    }
    previewIframe.setAttribute("data-listener-active", "true");
    const src = previewIframe.getAttribute("src") ?? "#";
    const url = previewIframe.getAttribute("data-url") ?? "#";
    if (url !== "#" && src !== "#") {
      return;
    }
    const msg =
      previewIframe.getAttribute("data-guard-msg") ??
      "Invoice preview route is unavailable. Please contact technical support or your domain administrator.";
    const hasBootstrap = !!(
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      document.querySelector('link[href*="bootstrap"]') && window.bootstrap
    );
    let container = document.getElementById("toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "toast-container";
      container.className = "toast-container position-fixed top-0 end-0 p-3";
      container.style.zIndex = "1080";
      document.body.appendChild(container);
    }
    if (hasBootstrap) {
      const toast = document.createElement("div");
      toast.className = "toast";
      toast.setAttribute("role", "alert");
      toast.setAttribute("aria-live", "assertive");
      toast.setAttribute("aria-atomic", "true");
      const body = document.createElement("div");
      body.className = "toast-body";
      body.textContent = msg;
      toast.appendChild(body);
      container.appendChild(toast);
      bootstrap.Toast.getOrCreateInstance(toast).show();
    } else {
      alert(msg);
    }
    previewIframe.setAttribute("data-failed-route", "true");
  } catch (err) {}
})();

export {};
