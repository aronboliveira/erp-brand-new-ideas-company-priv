/**
 * @fileoverview TypeScript version of public/assets/js/routes/employees/templates/joining/downloads/doc.js
 * @generated from original JavaScript - manual review recommended
 * @module doc
 */

((): void => {
  try {
    const links = Array.from(
      document.querySelectorAll(
        'a[id^="joining-letter-download-doc-btn-"][data-url][data-guard-msg]'
      )
    );
    if (links.length === 0) {
      return;
    }
    links.forEach(l => {
      if (l.getAttribute("data-listener-active") === "true") {
        return;
      }
      l.setAttribute("data-listener-active", "true");
      l.addEventListener("click", (e: Event) => {
        try {
          const href = (l.getAttribute("href") ?? "#").trim();
          const url = (l.getAttribute("data-url") ?? "#").trim();
          if (url !== "#" && href !== "#") {
            return;
          }
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ??
            "Download joining letter (DOC) route is unavailable. Please contact technical support or your domain administrator.";
          const hasBootstrap = !!(
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap
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
          if (hasBootstrap) {
            const t = document.createElement("div");
            t.className = "toast";
            for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = msg;
            t.appendChild(b);
            container.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
          l.setAttribute("data-failed-route", "true");
        } catch (err) {
    console.error(`[doc] Error:`, err);
  }
      });
    });
  } catch (err) {
    console.error(`[doc] Error:`, err);
  }
})();

export {};
