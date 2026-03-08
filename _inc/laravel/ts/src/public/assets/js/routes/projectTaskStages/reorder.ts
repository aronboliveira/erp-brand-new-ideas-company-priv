/**
 * @fileoverview TypeScript version of public/assets/js/routes/projectTaskStages/reorder.js
 * @generated from original JavaScript - manual review recommended
 * @module reorder
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const qs = <T extends Element = Element>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataSortGuard = "data-sort-guard";
  const ensureToastContainer = (): HTMLDivElement => {
    const id = "np-toast-container";
    let c = qs<HTMLDivElement>("#" + id);
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = id;
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string): void=> {
    const hasBootstrapLink =
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]');
    if (hasBootstrapLink) {
      const container = ensureToastContainer();
      let t = qs<HTMLDivElement>("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        const ToastClass =
          window.bootstrap.Toast ??
          class {
            constructor(
              private el: HTMLElement | null,
              _opts?: object,
            ) {}
            show(): void {
              alert(message ?? errFb);
            }
          };
        new ToastClass(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleInteractiveError = (message: string): void=> {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
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
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const initSortable = (): void => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {
    console.error(`[reorder] Error:`, _);
  }
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    if (!($.fn as JQuery & { sortable?: unknown }).sortable) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery UI sortable unavailable");
      } catch (_) {
    console.error(`[reorder] Error:`, _);
  }
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    const $lists = $(".sortable");
    if (!$lists.length) {
      return;
    }
    $lists.each(function (this: HTMLElement): void {
      const el = this;
      if (el.getAttribute(dataSortGuard) === "true") {
        return;
      }
      el.setAttribute(dataSortGuard, "true");
      try {
        const $el = $(el) as JQuery & {
          sortable: (opts?: object | string) => JQuery;
          disableSelection?: () => JQuery;
        };
        if (typeof $el.disableSelection === "function") {
          $el.disableSelection();
        }
        $el.sortable();
        $el.sortable({
          stop: function (this: HTMLElement): void {
            try {
              const order: string[] = [];
              $(this)
                .find("li")
                .each(function (this: HTMLElement, i: number): void {
                  order[i] = String(
                    $(this).attr("data-id") ?? $(this).data("id") ?? "",
                  );
                });
              const explicit = "{{route('project-task-stages.order')}}";
              const url = el.getAttribute("data-url");
              const href =
                el.tagName === "FORM"
                  ? (el.getAttribute("action") ?? "")
                  : (el.getAttribute("href") ?? "");
              if (
                (!url || url === "#") &&
                (!href || href === "#") &&
                (!explicit || (explicit as string) === "#")
              ) {
                scheduleInteractiveError(getMsg(el, "reorder_unavailable"));
                return;
              }
              const endpoint =
                url && url !== "#"
                  ? url
                  : href && href !== "#"
                    ? href
                    : explicit;
              const token = $('meta[name="csrf-token"]').attr("content") ?? "";
              $.ajax({
                url: endpoint,
                type: "POST",
                data: { order: order },
                headers: token ? { "X-CSRF-TOKEN": token } : undefined,
                cache: false,
                success: function (): void {},
                error: function (): void {
                  scheduleInteractiveError(getMsg(el, "ajax_unavailable"));
                },
              });
            } catch (_) {
              scheduleInteractiveError(getMsg(el, "reorder_unavailable"));
            }
          },
        });
        const mo = new MutationObserver((m, o) => {
          if (!document.body.contains(el)) {
            try {
              (
                $(el) as JQuery & { sortable: (cmd: string) => JQuery }
              ).sortable("destroy");
            } catch (_) {
    console.error(`[reorder] Error:`, _);
  }
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      } catch (_) {
        scheduleInteractiveError(getMsg(el, "plugin_unavailable"));
      }
    });
  };
  const init = (): void => {
    initSortable();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
