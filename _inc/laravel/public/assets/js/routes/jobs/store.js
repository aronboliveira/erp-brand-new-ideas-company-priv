/** @requires ERPGuard */
(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  if (typeof scheduleError !== "function") return;

  const safeUrl = el =>
    (
      el?.getAttribute("action") ||
      el?.getAttribute("data-url") ||
      el?.getAttribute("href") ||
      ""
    ).trim();

  const guard = el =>
    el?.getAttribute("data-guard-msg") ||
    "Route is unavailable. Please contact technical support or your domain administrator.";

  const form = document.getElementById("job-create-form");
  if (form) {
    form.addEventListener(
      "submit",
      e => {
        const url = safeUrl(form);
        if (!url || url === "#") {
          e.preventDefault();
          scheduleError(guard(form), "submit");
        }
      },
      { passive: false },
    );
  }

  const guardLinks = Array.from(
    document.querySelectorAll('a[data-ajax-popup-over="true"]'),
  );
  guardLinks.forEach(a => {
    a.addEventListener("click", e => {
      const url = safeUrl(a);
      if (!url || url === "#") {
        e.preventDefault();
        scheduleError(guard(a), "click");
      }
    });
  });

  if (window.jQuery) {
    const $ = window.jQuery;
    $(".summernote-simple").each(function () {
      if (!$(this).data("summernote")) $(this).summernote({ height: 200 });
    });
    $(".summernote-simple-2").each(function () {
      if (!$(this).data("summernote")) $(this).summernote({ height: 300 });
    });
    $('input[data-toggle="tags"]').each(function () {
      if (typeof $(this).tagsinput === "function") $(this).tagsinput("items");
    });
  }
})();
