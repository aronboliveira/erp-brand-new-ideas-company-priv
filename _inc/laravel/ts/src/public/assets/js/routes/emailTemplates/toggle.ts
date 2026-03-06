/**
 * @fileoverview TypeScript version of public/assets/js/routes/emailTemplates/toggle.js
 * @generated from original JavaScript - manual review recommended
 * @module toggle
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */

((): void => {
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? "";
  const lang = ((): void => {
    const l = (
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ?? "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    return l === "pt-br" ? l : l.slice(0, 2);
  })();
  const t = k =>
    window.translations?.[lang]?.[k] ||
    window.translations?.en?.[k] ||
    "# ERROR";
  const pop = (msg, type = "error") =>
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    { window.show_toastr ? window.show_toastr(type, msg, type) : alert(msg); };
  document.addEventListener("click", e => {
    const cb = e.target.closest(".email-template-checkbox");
    if (!cb) return;

    const url = cb.dataset.url;
    const val = cb.value ?? "";
    if (!url) {
      pop(t("email_template_toggle_failed"));
      return;
    }

    fetch(url, {
      method: "PUT",
      headers: {
        "X-CSRF-TOKEN": "" + CSRF,
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ status: val }),
    })
      .then(r => (r.ok ? r.json().catch(console.error) : Promise.reject()))
      .then(res => {
        if (!res?.is_success) return Promise.reject();
        pop(res.success ?? "OK", "success");

        cb.value = val === "1" ? "0" : "1";
      })
      .catch((): void => { pop(t("email_template_toggle_failed")); });
  });
})();

export {};
