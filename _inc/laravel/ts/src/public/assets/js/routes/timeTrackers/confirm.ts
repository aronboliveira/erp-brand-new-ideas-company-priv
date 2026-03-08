/**
 * @fileoverview TypeScript version of public/assets/js/routes/timeTrackers/confirm.js
 * @generated from original JavaScript - manual review recommended
 * @module confirm
 */

interface JQueryStaticExtended extends JQueryStatic {
  destroyModal?: (modal: JQuery<HTMLElement> | HTMLElement) => void;
}

interface JQueryExtended extends JQuery<HTMLElement> {
  fireModal?: (options: {
    title: string;
    body: string;
    buttons: {
      text: string;
      class: string;
      handler: (modal: JQuery<HTMLElement>) => void;
    }[];
  }) => void;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery as JQueryStaticExtended;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBound = "data-confirm-bound";
  const dataErrGuard = "data-error-guard";
  const dataFallbackBound = "data-confirm-fallback";
  const qs = (
    s: string,
    r: Document | HTMLElement = document,
  ): HTMLElement | null => r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBootstrap = () =>
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    (qs('link[href*="bootstrap"]') && window.bootstrap.Toast);
  const ensureToastContainer = (): HTMLElement => {
    const c = qs("#np-toast-container");
    if (c) {
      return c;
    }
    const el = document.createElement("div");
    el.id = "np-toast-container";
    el.setAttribute("aria-live", "polite");
    el.setAttribute("aria-atomic", "true");
    el.style.position = "fixed";
    el.style.top = "1rem";
    el.style.right = "1rem";
    document.body.appendChild(el);
    return el;
  };
  const showErrorNow = (message: string): void=> {
    if (hasBootstrap()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        const toastEl = document.createElement("div");
        toastEl.id = "np-toast";
        toastEl.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);
        toastEl.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(toastEl);
        t = toastEl;
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
  const scheduleInteractiveError = (target: HTMLElement, message: string): void=> {
    if (!target || target.getAttribute(dataErrGuard) === "true") {
      return;
    }
    target.setAttribute(dataErrGuard, "true");
    const once = (): void => {
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
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const bindConfirmModals = (): void => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {
    console.error(`[confirm] Error:`, _);
  }
      document
        .querySelectorAll("[data-confirm-delete]")
        .forEach((el: Element): void => {
          scheduleInteractiveError(
            el as HTMLElement,
            getMsg(el as HTMLElement, "plugin_unavailable"),
          );
        });
      return;
    }
    const hasFireModal =
      typeof ($.fn as unknown as { fireModal?: unknown }).fireModal ===
      "function";
    document
      .querySelectorAll("[data-confirm-delete]")
      .forEach((el: Element): void => {
        const htmlEl = el as HTMLElement;
        const me = $(htmlEl) as JQueryExtended;
        if (htmlEl.getAttribute(dataBound) === "true") {
          return;
        }
        htmlEl.setAttribute(dataBound, "true");
        let meData: unknown = me.data("confirm-delete");
        meData = meData == null ? "" : String(meData);
        const parts = (meData as string).split("|");
        const title = parts[0] ?? "";
        const body = parts.slice(1).join("|") ?? "";
        if (hasFireModal) {
          try {
            me.fireModal?.({
              title: title,
              body: body,
              buttons: [
                {
                  text: String(me.data("confirm-text-yes") ?? "Yes"),
                  class: "btn btn-sm btn-danger rounded-pill",
                  handler: function (modal: JQuery<HTMLElement>): void{
                    try {
                      const yesCode = String(me.data("confirm-yes") ?? "");
                      if (yesCode) {
                        // SECURITY: Replace eval with safe handler dispatch
                        const confirmHandler = window.__confirmHandlers?.[
                          yesCode
                        ] as (() => void) | undefined;
                        if (typeof confirmHandler === "function") {
                          confirmHandler();
                        } else {
                          safeFormAction(yesCode, me.get(0));
                        }
                      }
                    } catch (_) {
    console.error(`[confirm] Error:`, _);
  }
                    try {
                      const destroyFn = $.destroyModal;
                      if (destroyFn) {
                        destroyFn(modal);
                      } else {
                        modal.remove();
                      }
                    } catch (_) {
    console.error(`[confirm] Error:`, _);
  }
                  },
                },
                {
                  text: String(me.data("confirm-text-cancel") ?? "Cancel"),
                  class: "btn btn-sm btn-secondary rounded-pill",
                  handler: function (modal: JQuery<HTMLElement>): void{
                    try {
                      const destroyFn = $.destroyModal;
                      if (destroyFn) {
                        destroyFn(modal);
                      } else {
                        modal.remove();
                      }
                    } catch (_) {
    console.error(`[confirm] Error:`, _);
  }
                    try {
                      const noCode = String(me.data("confirm-no") ?? "");
                      if (noCode) {
                        // SECURITY: Replace eval with safe handler dispatch
                        const confirmHandler = window.__confirmHandlers?.[
                          noCode
                        ] as (() => void) | undefined;
                        if (typeof confirmHandler === "function") {
                          confirmHandler();
                        } else {
                          safeFormAction(noCode, me.get(0));
                        }
                      }
                    } catch (_) {
    console.error(`[confirm] Error:`, _);
  }
                  },
                },
              ],
            });
          } catch (_) {
            scheduleInteractiveError(
              htmlEl,
              getMsg(htmlEl, "confirm_unavailable"),
            );
          }
        } else {
          if (htmlEl.getAttribute(dataFallbackBound) !== "true") {
            htmlEl.setAttribute(dataFallbackBound, "true");
            const handler = function (): void {
              showErrorNow(getMsg(htmlEl, "confirm_unavailable"));
            };
            $(htmlEl).on("click.confirmFallback", handler);
            const mo = new MutationObserver((m, o) => {
              if (!document.body.contains(htmlEl)) {
                $(htmlEl).off("click.confirmFallback", handler);
                o.disconnect();
              }
            });
            mo.observe(document.body, { childList: true, subtree: true });
          }
        }
      });
  };

  // SECURITY: Safe fallback for confirm handlers instead of eval()
  const safeFormAction = (actionStr: string, _element: HTMLElement): void=> {
    if (!actionStr) return;
    // If it looks like a form selector, submit that form
    if (actionStr.startsWith("#") || actionStr.startsWith(".")) {
      const form = qs(actionStr);
      if (form?.tagName === "FORM") {
        (form as HTMLFormElement).submit();
      }
      return;
    }
    // If it starts with a safe URL protocol, navigate to it
    if (
      /^(https?:\/\/|\/)/.test(actionStr) &&
      !/^javascript:/i.test(actionStr)
    ) {
      window.location.href = actionStr;
      return;
    }
  };

  const init = (): void => {
    bindConfirmModals();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
