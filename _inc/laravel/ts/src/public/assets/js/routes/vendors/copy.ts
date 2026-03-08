/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/copy.js
 * @generated from original JavaScript - manual review recommended
 * @module copy
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
  const dataBindGuard = "data-copy-billing-bound";
  const qs = <T extends Element = HTMLElement>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBootstrapUi = () =>
    !!(
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap.Toast;
  const ensureToastContainer = (): HTMLDivElement => {
    const existing = qs<HTMLDivElement>("#np-toast-container");
    if (existing) return existing;
    const c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string): void=> {
    if (hasBootstrapUi()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
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
      if (body) body.textContent = message ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
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
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    return msg;
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const copyValue = (from: unknown, to: unknown) => {
    if (!$) return false;
    // eslint-disable-next-line @typescript-eslint/restrict-template-expressions
    const $from = $(`[name='${from}']`);
    // eslint-disable-next-line @typescript-eslint/restrict-template-expressions
    const $to = $(`[name='${to}']`);
    if (!$from.length || !$to.length) return false;
    const v = String($from.val() ?? "");
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    $to.val(v);
    return true;
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const handler = function (this: HTMLElement) {
    try {
      if (!$?.fn) {
        try {
          console.error("jQuery unavailable");
        } catch (_) {
    console.error(`[copy] Error:`, _);
  }
        showErrorNow(getMsg(this, "plugin_unavailable"));
        return;
      }
      const ok = [
        ["billing_name", "shipping_name"],
        ["billing_country", "shipping_country"],
        ["billing_state", "shipping_state"],
        ["billing_city", "shipping_city"],
        ["billing_phone", "shipping_phone"],
        ["billing_zip", "shipping_zip"],
        ["billing_address", "shipping_address"],
      ]
        .map(function (p) {
          return copyValue(p[0], p[1]);
        })
        .every(function (x) {
          return x;
        });
      if (!ok) showErrorNow(getMsg(this, "copy_billing_unavailable"));
    } catch (_) {
      showErrorNow(getMsg(this, "copy_billing_unavailable"));
    }
  };
  const bind = (): void => {
    const host = document.body;
    if (host.getAttribute(dataBindGuard) === "true") return;
    host.setAttribute(dataBindGuard, "true");
    if ($?.fn) {
      $(document).on("click._npCopy", "#billing_data", handler);
    } else {
      document.addEventListener("click", function (e: Event) {
        const t = e.target as HTMLElement | null;
        if (t && (t.id === "billing_data" || t.closest("#billing_data")))
          handler.call(t);
      });
    }
    const mo = new MutationObserver(function (): void {
      if (!document.querySelector<HTMLElement>("#billing_data")) {
        if ($?.fn) {
          $(document).off("click._npCopy", "#billing_data");
        }
        host.removeAttribute(dataBindGuard);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  } else {
    bind();
  }
})();

export {};
