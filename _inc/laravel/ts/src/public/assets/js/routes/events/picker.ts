/**
 * @fileoverview TypeScript version of public/assets/js/routes/events/picker.js
 * @generated from original JavaScript - manual review recommended
 * @module picker
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const dataClient = "data-client-localized";
  const dataGuard = "data-guard-msg";
  const langKey = "erp-np-lang";
  const toastId = "toast-box";

  const getMsg = key => {
    let lang = (
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      sessionStorage.getItem(langKey) ??
      document.documentElement.lang ?? "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    return (
      window.translations?.[lang]?.[key] || window.translations.en[key] || errFb
    );
  };

  const showToast = msg => {
    const hasBs =
      Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l =>
        /bootstrap/i.test(l.href),
      ) && window.bootstrap.Toast;
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (hasBs) {
      let box = document.getElementById(toastId);
      if (!box) {
        box = document.createElement("div");
        box.id = toastId;
        box.setAttribute("aria-live", "polite");
        box.setAttribute("aria-atomic", "true");
        document.body.appendChild(box);
      }
      const t = document.createElement("div");
      t.className = "toast";
      {
        const _b = document.createElement("div");
        _b.className = "toast-body";
        _b.textContent = msg;
        t.replaceChildren(_b);
      }
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(msg);
    }
  };

  let queued = "";
  const flush = (): void => {
    if (queued !== "") {
      showToast(queued);
      queued = "";
    }
  };
  document.addEventListener("pointerup", flush);
  new MutationObserver((recs, obs) => {
    for (const r of recs) {
      for (const n of r.removedNodes) {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", flush);
          obs.disconnect();
        }
      }
    }
  }).observe(document.body, { childList: true, subtree: true });

  try {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!window.$ || !$.fn.daterangepicker) throw 0;
    const els = document.querySelectorAll(".datepicker");
    if (els.length === 0) return;
    const opts = {
      singleDatePicker: true,
      locale: window.date_picker_locale ?? { format: "YYYY-MM-DD" },
    };
    els.forEach((el: Element): void => $(el).daterangepicker(opts));
  } catch {
    queued = getMsg("date_picker_init_failed");
  }
})();

export {};
