(function () {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBound = "data-confirm-bound";
  const dataErrGuard = "data-error-guard";
  const dataFallbackBound = "data-confirm-fallback";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBootstrap = () =>
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    (qs('link[href*="bootstrap"]') &&
      window.bootstrap &&
      window.bootstrap.Toast);
  const ensureToastContainer = () => {
    let c = qs("#np-toast-container");
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = message => {
    if (hasBootstrap()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleInteractiveError = (target, message) => {
    if (!target || target.getAttribute(dataErrGuard) === "true") {
      return;
    }
    target.setAttribute(dataErrGuard, "true");
    const once = () => {
      try {
        showErrorNow(message);
      } finally {
        target.removeAttribute(dataErrGuard);
      }
    };
    target.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(target)) {
        target.removeEventListener("click", once);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const bindConfirmModals = () => {
    if (!$ || !$.fn) {
      try {
        console.error("jQuery unavailable");
      } catch (_) {}
      document
        .querySelectorAll("[data-confirm-delete]")
        .forEach(el =>
          scheduleInteractiveError(el, getMsg(el, "plugin_unavailable"))
        );
      return;
    }
    const hasFireModal = typeof $.fn.fireModal === "function";
    document.querySelectorAll("[data-confirm-delete]").forEach(el => {
      const me = $(el);
      if (el.getAttribute(dataBound) === "true") {
        return;
      }
      el.setAttribute(dataBound, "true");
      let meData = me.data("confirm-delete");
      meData = meData == null ? "" : String(meData);
      const parts = meData.split("|");
      const title = parts[0] ?? "";
      const body = parts.slice(1).join("|") ?? "";
      if (hasFireModal) {
        try {
          me.fireModal({
            title: title,
            body: body,
            buttons: [
              {
                text: me.data("confirm-text-yes") || "Yes",
                class: "btn btn-sm btn-danger rounded-pill",
                handler: function (modal) {
                  try {
                    const yesCode = me.data("confirm-yes");
                    if (yesCode) {
                      eval(yesCode);
                    }
                  } catch (_) {}
                  try {
                    if ($.destroyModal) {
                      $.destroyModal(modal);
                    } else {
                      $(modal).remove();
                    }
                  } catch (_) {}
                },
              },
              {
                text: me.data("confirm-text-cancel") || "Cancel",
                class: "btn btn-sm btn-secondary rounded-pill",
                handler: function (modal) {
                  try {
                    if ($.destroyModal) {
                      $.destroyModal(modal);
                    } else {
                      $(modal).remove();
                    }
                  } catch (_) {}
                  try {
                    const noCode = me.data("confirm-no");
                    if (noCode) {
                      eval(noCode);
                    }
                  } catch (_) {}
                },
              },
            ],
          });
        } catch (_) {
          scheduleInteractiveError(el, getMsg(el, "confirm_unavailable"));
        }
      } else {
        if (el.getAttribute(dataFallbackBound) !== "true") {
          el.setAttribute(dataFallbackBound, "true");
          const handler = function () {
            showErrorNow(getMsg(el, "confirm_unavailable"));
          };
          $(el).on("click.confirmFallback", handler);
          const mo = new MutationObserver((m, o) => {
            if (!document.body.contains(el)) {
              $(el).off("click.confirmFallback", handler);
              o.disconnect();
            }
          });
          mo.observe(document.body, { childList: true, subtree: true });
        }
      }
    });
  };
  const init = () => {
    bindConfirmModals();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
