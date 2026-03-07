/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */


((): void => {
  const Q = <T extends Element = Element>(s: string): T | null =>
    document.querySelector<T>(s);
  const QA = <T extends Element = Element>(s: string): T[] =>
    Array.from(document.querySelectorAll<T>(s));
  const DEFAULT_ROUTE_MSG =
    "Requested route is unavailable. Please contact technical support or your domain administrator.";

  const toast = (message: string): void=> {
    const text = message || DEFAULT_ROUTE_MSG;
    const hasBs = !!(
      document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') &&
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

  const bindLinkGuard = (a: HTMLAnchorElement): void => {
    if (!a || a.getAttribute("data-listener-active") === "true") return;
    a.setAttribute("data-listener-active", "true");
    a.addEventListener(
      "click",
      function (this: HTMLAnchorElement, e: Event): void {
        const href = (this.getAttribute("href") ?? "#").trim();
        const url = (this.getAttribute("data-url") ?? href ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        toast(this.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
        this.setAttribute("data-failed-route", "true");
      },
    );
  };

  const bindFormGuard = (fm: HTMLFormElement): void => {
    if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    fm.addEventListener(
      "submit",
      function (this: HTMLFormElement, e: Event): void {
        const action = (this.getAttribute("action") ?? "#").trim();
        const url = (this.getAttribute("data-url") ?? action ?? "#").trim();
        if (url !== "#" && action !== "#") return;
        e.preventDefault();
        toast(this.getAttribute("data-guard-msg") || DEFAULT_ROUTE_MSG);
        this.setAttribute("data-failed-route", "true");
      },
    );
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

  const filenameFromInput = (inp: HTMLInputElement): string => {
    if (!inp.files) return "";
    if (inp.files.length === 0) return "";
    if (inp.files.length === 1) return inp.files[0].name ?? "";
    return Array.from(inp.files)
      .map((f: File) => f.name ?? "")
      .filter(Boolean)
      .join(", ");
  };

  const previewImage = (file: Blob, imgEl: HTMLImageElement | null): void => {
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
    QA<HTMLInputElement>('input[type="file"][data-filename]').forEach(
      (inp: HTMLInputElement): void => {
        if (inp.getAttribute("data-file-listener") === "true") return;
        inp.setAttribute("data-file-listener", "true");
        const outClass = inp.getAttribute("data-filename") ?? "";
        const out = outClass ? Q(`.${CSS.escape(outClass)}`) : null;
        inp.addEventListener("change", function (this: HTMLInputElement): void {
          const txt = filenameFromInput(this);
          if (out) out.textContent = txt ?? "";
          const id = this.id ?? "";
          if (this.files?.[0]) {
            if (id === "profile")
              previewImage(this.files[0], Q<HTMLImageElement>("#blah"));
            if (id === "resume")
              previewImage(this.files[0], Q<HTMLImageElement>("#blah1"));
          }
        });
      },
    );
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    QA<HTMLAnchorElement>("a[data-guard-msg], a[data-url]").forEach(
      bindLinkGuard,
    );
    QA<HTMLFormElement>("form[data-guard-msg], form[data-url]").forEach(
      bindFormGuard,
    );
    initTooltips();
    bindFileInputs();
  });
})();

export {};
