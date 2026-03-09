/**
 * @fileoverview TypeScript version of public/assets/js/routes/bills/copy.js
 * @generated from original JavaScript - manual review recommended
 * @module copy
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

  const showError = (key: string): void => {
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
    if (toastContainer.querySelector(`.toast[data-error-key="${key}"]`)) return;
    if (document.querySelector(BS_LINK) && window.bootstrap.Toast) {
      const toast = document.createElement("div");
      toast.className = "toast align-items-center text-bg-danger border-0";
      toast.dataset.errorKey = key;
      for (const [k, v] of Object.entries({
        role: "alert",
        "aria-live": "assertive",
        "aria-atomic": "true",
      }))
        toast.setAttribute(k, v);
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
    document.querySelectorAll<HTMLElement>(".copy_link").forEach((el): void => {
      if (el.dataset.copyListener) return;
      el.dataset.copyListener = "true";
      el.addEventListener("click", (e: Event) => {
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
          const onCopy = (evt: ClipboardEvent): void => {
            evt.clipboardData?.setData("text/plain", href);
            evt.preventDefault();
          };
          document.addEventListener("copy", onCopy, true);
          const success = document.execCommand("copy");
          document.removeEventListener("copy", onCopy, true);
          if (!success) throw new Error("execCommand returned false");
          (
            window as unknown as { show_toastr: (a: string, b: string) => void }
          ).show_toastr(
            "success",
            window.translations?.en?.copy_link_success ?? "Link copied",
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
