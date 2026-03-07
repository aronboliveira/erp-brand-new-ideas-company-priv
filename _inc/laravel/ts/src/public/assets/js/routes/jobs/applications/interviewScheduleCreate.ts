/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/interviewScheduleCreate.js
 * @generated from original JavaScript - manual review recommended
 * @module interviewScheduleCreate
 */

/* global bootstrap */
((): void => {
  try {
    const links = Array.from(
      document.querySelectorAll(
        'a[id^="interview-schedule-create-btn-"][data-url][data-guard-msg]'
      )
    );
    if (links.length === 0) {
      return;
    }
    links.forEach(l => {
      try {
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
              "Create interview schedule route is unavailable. Please contact technical support or your domain administrator.";
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
              t.setAttribute("role", "alert");
              t.setAttribute("aria-live", "assertive");
              t.setAttribute("aria-atomic", "true");
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
          } catch (err) {}
        });
      } catch (err) {}
    });
  } catch (err) {}
})();

export {};
