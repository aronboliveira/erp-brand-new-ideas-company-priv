/**
 * @fileoverview TypeScript version of public/assets/js/routes/bills/copy.js
 * @generated from original JavaScript - manual review recommended
 * @module copy
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const BS_LINK = 'link[href*="bootstrap"]';
  const toastContainer = ((): void => {
    const c = document.createElement("div");
    c.className = "toast-container position-fixed bottom-0 end-0 p-3";
    document.body.append(c);
    return c;
  })();

  const showError = key => {
    const errFb = "# ERROR";
    let lang = (
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      window.sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ?? "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    const msg =
      window.translations?.[lang]?.[key] ||
      window.translations?.en?.[key] ||
      errFb;
    if (toastContainer.querySelector(`.toast[data-error-key="${key}"]`)) return;
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    if (document.querySelector(BS_LINK) && window.bootstrap.Toast) {
      const toast = document.createElement("div");
      toast.className = "toast align-items-center text-bg-danger border-0";
      toast.dataset.errorKey = key;
      toast.setAttribute("role", "alert");
      toast.setAttribute("aria-live", "assertive");
      toast.setAttribute("aria-atomic", "true");
      toast.innerHTML = `
                <div class="d-flex">
                <div class="toast-body">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;
      toastContainer.append(toast);
      new window.bootstrap.Toast(toast).show();
    } else {
      alert(msg);
    }
  };

  try {
    document.querySelectorAll(".copy_link").forEach((el: Element): void => {
      if (el.dataset.copyListener) return;
      el.dataset.copyListener = "true";
      el.addEventListener("click", e => {
        e.preventDefault();
        const href = el.getAttribute("href") ?? "";
        if (href === "") {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("No href to copy");
          showError("copy_link_unavailable");
          return;
        }
        try {
          const onCopy = evt => {
            evt.clipboardData.setData("text/plain", href);
            evt.preventDefault();
          };
          document.addEventListener("copy", onCopy, true);
          const success = document.execCommand("copy");
          document.removeEventListener("copy", onCopy, true);
          if (!success) throw new Error("execCommand returned false");
          show_toastr(
            "success",
            window.translations?.en?.copy_link_success ?? "Link copied",
            "success"
          );
        } catch (err) {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("Copy command failed:", err);
          showError("copy_link_unavailable");
        }
      });
    });
  } catch (err) {
    if (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
    )
      console.error("Failed to bind copy_link handlers:", err);
  }
})();

export {};
