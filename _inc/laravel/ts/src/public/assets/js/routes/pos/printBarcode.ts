/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/printBarcode.js
 * @generated from original JavaScript - manual review recommended
 * @module printBarcode
 */

((): void => {
  const toast = (msg: string): void => {
    try {
      if (window.bootstrap.Toast) {
        const c =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          el.setAttribute(k, v);
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

  const bindGuard = (el: HTMLElement | null): void => {
    if (!el || el.getAttribute("data-listener-active") === "true") return;
    el.setAttribute("data-listener-active", "true");
    if (!el.getAttribute("data-listener-bound-click")) {
      el.setAttribute("data-listener-bound-click", "1");
      el.addEventListener("click", (e: Event) => {
        const url =
          (el.getAttribute("href") || el.getAttribute("data-url")) ?? "#";
        if (!url || url === "#") {
          e.preventDefault();
          const msg =
            el.getAttribute("data-guard-msg") ?? "Action unavailable.";
          toast(msg);
        }
      });
    }
  };

  bindGuard(document.getElementById("pos-print"));
  bindGuard(document.getElementById("pos-setting"));
})();

(function (): void {
  function toast(msg: string): void {
    try {
      let c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      if (window.bootstrap.Toast) {
        const t = document.createElement("div");
        t.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
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
      'a[data-guard-msg]:not([data-listener-active="true"])',
    );
    links.forEach(function (a) {
      a.setAttribute("data-listener-active", "true");
      a.addEventListener("click", function (e: Event) {
        const url = a.getAttribute("href") ?? a.getAttribute("data-url") ?? "#";
        if (!url || url === "#") {
          e.preventDefault();
          const msg = a.getAttribute("data-guard-msg") ?? "Action unavailable.";
          toast(msg);
        }
      });
    });
  } catch (_) {
    console.error(`[printBarcode] Error:`, _);
  }
})();

export {};
