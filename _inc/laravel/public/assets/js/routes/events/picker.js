(() => {
  const errFb = "# ERROR";
  const dataClient = "data-client-localized";
  const dataGuard = "data-guard-msg";
  const langKey = "erp-np-lang";
  const toastId = "toast-box";

  const getMsg = key => {
    let lang = (
      sessionStorage.getItem(langKey) ||
      document.documentElement.lang ||
      "en"
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
        /bootstrap/i.test(l.href)
      ) && window.bootstrap?.Toast;
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
      t.innerHTML = `<div class="toast-body">${msg}</div>`;
      box.appendChild(t);
      bootstrap.Toast.getOrCreateInstance(t).show();
    } else {
      alert(msg);
    }
  };

  let queued = "";
  const flush = () => {
    if (queued) {
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
    if (!window.$ || !$.fn.daterangepicker) throw 0;
    const els = document.querySelectorAll(".datepicker");
    if (!els.length) return;
    const opts = {
      singleDatePicker: true,
      locale: window.date_picker_locale ?? { format: "YYYY-MM-DD" },
    };
    els.forEach(el => $(el).daterangepicker(opts));
  } catch {
    queued = getMsg("date_picker_init_failed");
  }
})();
