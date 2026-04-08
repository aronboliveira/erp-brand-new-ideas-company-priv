/**
 * img-fallback-delegate.ts — CSP-safe replacement for inline onerror
 * image fallback handlers.
 * Any `<img>` with a `data-fallback-src` attribute will, on error,
 * swap its src to the fallback value (once).
 *
 * Mirror of public/assets/js/core/img-fallback-delegate.js
 * @module core/img-fallback-delegate
 */

(() => {
  "use strict";

  document.addEventListener(
    "error",
    (e: Event) => {
      const img = e.target as HTMLElement;
      if (img.tagName !== "IMG") return;

      const fallback = img.getAttribute("data-fallback-src");
      if (!fallback) return;

      img.removeAttribute("data-fallback-src");
      (img as HTMLImageElement).src = fallback;
    },
    true,
  );
})();

export {};
