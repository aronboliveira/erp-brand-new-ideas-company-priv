(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  if (!guard || !utils) return;

  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const t = k => utils.getTranslation(k) || "# ERROR";
  const pop = (msg, type = "error") =>
    window.show_toastr ? window.show_toastr(type, msg, type) : alert(msg);

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
      .then(r => (r.ok ? r.json() : Promise.reject()))
      .then(res => {
        if (!res?.is_success) return Promise.reject();
        pop(res.success ?? "OK", "success");
        cb.value = val === "1" ? "0" : "1";
      })
      .catch(() => pop(t("email_template_toggle_failed")));
  });
})();
