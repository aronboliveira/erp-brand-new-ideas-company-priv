(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);
  const scheduleError = msg => {
    let errorMessage = msg;
    const handler = () => {
      if (errorMessage) {
        showError(errorMessage);
        errorMessage = null;
      }
    };
    document.body.addEventListener("click", handler, { once: true });
  };

  const initGuard = "data-scroll-init";
  const initScrollSpy = () => {
    const target = document.querySelector("#useradd-sidenav") || document.body;
    if (target.getAttribute(initGuard) === "true") return;
    target.setAttribute(initGuard, "true");
    try {
      if (!window.bootstrap?.ScrollSpy) {
        scheduleError(getMsg("scrollspy_unavailable"));
        return;
      }
      new window.bootstrap.ScrollSpy(document.body, {
        target: "#useradd-sidenav",
        offset: 300,
      });
    } catch {
      scheduleError(getMsg("scrollspy_unavailable"));
    }
  };

  const bindGuard = "data-listgroup-click-bound";
  const bindListClicks = () => {
    const root = document.body;
    if (root.getAttribute(bindGuard) === "true") return;
    root.setAttribute(bindGuard, "true");
    $(document).on("click.lgitem", ".list-group-item", function () {
      try {
        const href = this.getAttribute("href") || "";
        const $all = $(".list-group-item");
        if ($all?.length) {
          $all
            .filter(function () {
              return (this.getAttribute("href") || "") === href;
            })
            .parent()
            .removeClass("text-primary");
        }
      } catch {}
    });
    const obs = new MutationObserver(() => {
      if (!document.body.contains(root)) {
        try {
          $(document).off("click.lgitem");
        } catch {}
        obs.disconnect();
      }
    });
    obs.observe(document.body, { childList: true, subtree: true });
  };

  const init = () => {
    initScrollSpy();
    bindListClicks();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
