/**
 * @fileoverview TypeScript version of public/assets/js/routes/attendances/date.js
 * @generated from original JavaScript - manual review recommended
 * @module date
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

((): void => {
  const BS_LINK = 'link[href*="bootstrap"]';
  const toastContainer = ((): HTMLDivElement => {
    const c = document.createElement("div");
    c.className = "toast-container position-fixed bottom-0 end-0 p-3";
    document.body.append(c);
    return c;
  })();

  const showError = (key: string): void=> {
    const errFb = "# ERROR";
    let lang = (
      window.sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ??
      "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    const msg =
      window.translations?.[lang]?.[key] ||
      window.translations?.en?.[key] ||
      errFb;

    const existing = toastContainer.querySelector(
      `.toast[data-error-key="${key}"]`,
    );
    if (existing) return;

    if (document.querySelector(BS_LINK) && window.bootstrap.Toast) {
      const toast = document.createElement("div");
      toast.className = "toast align-items-center text-bg-danger border-0";
      toast.dataset.errorKey = key;
      for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
      toast.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
      toastContainer.append(toast);
      new window.bootstrap.Toast(toast).show();
    } else {
      alert(msg);
    }
  };

  try {
    const pickers = document.querySelectorAll<HTMLElement>(".daterangepicker");
    pickers.forEach((el): void => {
      if (el.dataset.dpListener) return;
      el.dataset.dpListener = "true";
      el.addEventListener("click", (): void => {
        try {
          if (typeof $ !== "function") {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error("jQuery not loaded");
            showError("date_picker_unavailable");
            return;
          }
          if (
            typeof ($.fn as unknown as Record<string, unknown>)
              .daterangepicker !== "function"
          ) {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error("daterangepicker plugin unavailable");
            showError("date_picker_unavailable");
            return;
          }
          (
            $(el) as unknown as JQuery & { daterangepicker: (...args: unknown[]) => unknown }
          ).daterangepicker({
            format: "yyyy-mm-dd",
            locale: { format: "YYYY-MM-DD" },
          });
        } catch (err) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("Error initializing date picker on click:", err);
          showError("date_picker_unavailable");
        }
      });
    });
  } catch (err) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Error binding datepicker listeners:", err);
  }
})();

export {};
