/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/guard.js
 * @generated from original JavaScript - manual review recommended
 * @module guard
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  const toast = msg => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): void => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        el.appendChild(body);
        c.appendChild(el);
        window.bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  const bindGuard = el => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    el.addEventListener("click", e => {
      const url = el.getAttribute("href") || el.getAttribute("data-url") ?? "#";
      if (!url || url === "#") {
        e.preventDefault();
        const msg = el.getAttribute("data-guard-msg") ?? "Action unavailable.";
        toast(msg);
      }
    });
  };

  bindGuard(document.getElementById("pos-print"));
  bindGuard(document.getElementById("pos-setting"));
})();

(function (): void {
  function toast(msg) {
    try {
      let c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
      if (window.bootstrap && window.bootstrap.Toast) {
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = msg;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(msg);
      }
    } catch (_) {
      alert(msg);
    }
  }

  try {
    const links = document.querySelectorAll(
      'a[data-guard-msg]:not([data-listener-active="true"])'
    );
    links.forEach(function (a) {
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", function (e) {
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        const url = a.getAttribute("href") ?? a.getAttribute("data-url") ?? "#";
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (!url || url === "#") {
          e.preventDefault();
          const msg = a.getAttribute("data-guard-msg") ?? "Action unavailable.";
          toast(msg);
        }
      });
    });
  } catch (_) {}
})();

export {};
