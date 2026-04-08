/**
 * @file img-fallback-delegate.js
 * @description CSP-safe replacement for inline onerror image fallback handlers.
 *   Any <img> with a `data-fallback-src` attribute will, on error, swap its
 *   src to the fallback value (once).
 */
(() => {
  "use strict";
  document.addEventListener(
    "error",
    e => {
      const img = e.target;
      if (img.tagName !== "IMG") return;
      const fallback = img.getAttribute("data-fallback-src");
      if (!fallback) return;
      img.removeAttribute("data-fallback-src");
      img.src = fallback;
    },
    true,
  );
})();
