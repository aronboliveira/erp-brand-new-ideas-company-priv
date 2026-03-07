/**
 * @fileoverview TypeScript version of public/assets/js/routes/users/profiles/scroll.js
 * @generated from original JavaScript - manual review recommended
 * @module scroll
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBindGuard = "data-scrollspy-bound";
  const dataClickGuard = "data-listgroup-click-bound";
  const dataErrGuard = "data-scrollspy-error";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const qs = (s: string, r: ParentNode = document) => r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBS = () =>
    !!(
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap.Toast;
  const ensureToast = (): HTMLElement => {
    let c = qs("#np-toast-container") as HTMLElement | null;
    if (c) return c;
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
  const showErrorNow = (msg: string): void=> {
    if (hasBS()) {
      const container = ensureToast();
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
      const body = t.querySelector(".toast-body");
      if (body) body.textContent = msg ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(msg ?? errFb);
      }
    } else {
      alert(msg ?? errFb);
    }
  };
  const scheduleClickError = (msg: string): void=> {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(msg);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("click", once);
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
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
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
  const initScrollSpy = (): void => {
    const target =
      (qs("#useradd-sidenav") as HTMLElement | null) ?? document.body;
    if (target.getAttribute(dataBindGuard) === "true") return;
    target.setAttribute(dataBindGuard, "true");
    try {
      if (!window.bootstrap.ScrollSpy) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("bootstrap.ScrollSpy unavailable");
        } catch (_) {}
        scheduleClickError(getMsg(target, "scrollspy_unavailable"));
        return;
      }
      new window.bootstrap.ScrollSpy(document.body, {
        target: "#useradd-sidenav",
        offset: 300,
      });
    } catch (_) {
      scheduleClickError(getMsg(target, "scrollspy_unavailable"));
    }
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(target)) {
        o.disconnect();
      }
    });
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    mo.observe(document.body, { childList: true, subtree: true });
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const bindListClicks = () => {
    const root = document.body;
    if (root.getAttribute(dataClickGuard) === "true") return;
    root.setAttribute(dataClickGuard, "true");
    if (!$?.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      return;
    }
    const ns = ".lgitem";
    $(document).on("click" + ns, ".list-group-item", function () {
      try {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        // eslint-disable-next-line @typescript-eslint/no-unsafe-call
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-assignment
        const href = this.getAttribute("href") ?? "";
        const $all = $(".list-group-item");
        if ($all.length) {
          $all
            .filter(function (this: HTMLElement): boolean {
              return (this.getAttribute("href") ?? "") === href;
            })
            .parent()
            .removeClass("text-primary");
        }
      } catch (_) {}
    });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(root)) {
        $(document).off(ns);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const init = (): void => {
    initScrollSpy();
    bindListClicks();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
