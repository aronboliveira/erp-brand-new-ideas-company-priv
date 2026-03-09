(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const SUCCESS_KEY = "url_copy_success";
  const ERROR_KEY = "url_copy_failed";
  const ATTR_ACTIVE = "data-listener-active";
  const SELECTOR = ".copy_link";

  const els = document.querySelectorAll(SELECTOR);
  if (!els.length) return;

  els.forEach(el => {
    if (el.getAttribute(ATTR_ACTIVE) === "true") return;
    el.setAttribute(ATTR_ACTIVE, "true");

    el.addEventListener("click", async e => {
      e.preventDefault();
      try {
        const href = el.getAttribute("href");
        if (!href) throw new Error();
        await navigator.clipboard.writeText(href);
        const successMsg = getMsg(SUCCESS_KEY);
        show_toastr("success", successMsg, "success");
      } catch {
        const errorMsg = getMsg(ERROR_KEY);
        scheduleError(errorMsg, "click");
      }
    });
  });

  const mo = new MutationObserver((_, obs) => {
    if (![...els].some(el => document.body.contains(el))) {
      els.forEach(el => el.removeEventListener("click"));
      obs.disconnect();
    }
  });
  mo.observe(document.body, { childList: true, subtree: true });
})();
