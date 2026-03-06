/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const Q = s => document.querySelector(s);
  const QA = s => Array.from(document.querySelectorAll(s));
  const DEFAULT_ROUTE_MSG =
    "Requested route is unavailable. Please contact technical support or your domain administrator.";

  const toast = message => {
    const text = message || DEFAULT_ROUTE_MSG;
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      window.bootstrap
    );
    let box = document.getElementById("toast-container");
    if (!box) {
      box = document.createElement("div");
      box.id = "toast-container";
      document.body.appendChild(box);
    }
    if (hasBs) {
      const t = document.createElement("div");
      t.className = "toast";
      t.setAttribute("role", "alert");
      t.setAttribute("aria-live", "assertive");
      t.setAttribute("aria-atomic", "true");
      const b = document.createElement("div");
      b.className = "toast-body";
      b.textContent = text;
      t.appendChild(b);
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(text);
    }
  };

  const bindLinkGuard = a => {
    if (!a || a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    a.addEventListener("click", e => {
      const href = (a.getAttribute("href") ?? "#").trim();
      const url = (a.getAttribute("data-url") ?? href ?? "#").trim();
      if (url !== "#" && href !== "#") return;
      e.preventDefault();
      toast(a.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      a.setAttribute("data-failed-route", "true");
    });
  };

  const bindFormGuard = fm => {
    if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener("submit", e => {
      const action = (fm.getAttribute("action") ?? "#").trim();
      const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
      if (url !== "#" && action !== "#") return;
      e.preventDefault();
      toast(fm.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
      fm.setAttribute("data-failed-route", "true");
    });
  };

  const initTooltips = (): void => {
    try {
      QA('[data-bs-toggle="tooltip"]').forEach((el: Element): void => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch {}
      });
    } catch {}
  };

  const filenameFromInput = inp => {
    if (!inp?.files) return "";
    if (inp.files.length === 0) return "";
    if (inp.files.length === 1) return inp.files[0].name ?? "";
    return Array.from(inp.files)
      .map(f => f.name ?? "")
      .filter(Boolean)
      .join(", ");
  };

  const previewImage = (file, imgEl) => {
    if (!file || !imgEl) return;
    try {
      const url = URL.createObjectURL(file);
      imgEl.src = url;
      imgEl.onload = (): void => {
        try {
          URL.revokeObjectURL(url);
        } catch {}
      };
    } catch {}
  };

  const bindFileInputs = (): void => {
    QA('input[type="file"][data-filename]').forEach(inp => {
      if (inp.getAttribute("data-file-listener") === "true") return;
      inp.setAttribute("data-file-listener", "true");
      const outClass = inp.getAttribute("data-filename") ?? "";
      const out = outClass ? Q(`.${CSS.escape(outClass)}`) : null;
      inp.addEventListener("change", (): void => {
        const txt = filenameFromInput(inp);
        if (out) out.textContent = txt ?? "";
        const id = inp.id ?? "";
        if (id === "profile") previewImage(inp.files?.[0], Q("#blah"));
        if (id === "resume") previewImage(inp.files?.[0], Q("#blah1"));
      });
    });
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    QA("a[data-guard-msg], a[data-url]").forEach(bindLinkGuard);
    QA("form[data-guard-msg], form[data-url]").forEach(bindFormGuard);
    initTooltips();
    bindFileInputs();
  });
})();

export {};
