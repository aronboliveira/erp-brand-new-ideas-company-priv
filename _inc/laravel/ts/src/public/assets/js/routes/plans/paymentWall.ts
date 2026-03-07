/**
 * @fileoverview TypeScript version of public/assets/js/routes/plans/paymentWall.js
 * @generated from original JavaScript - manual review recommended
 * @module paymentWall
 */

/* global bootstrap, $, jQuery */
(function (): void {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-pw-error";
  const dataBindGuard = "data-pw-bound";
  const qs = <T extends Element = HTMLElement>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector(s) as T | null;
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap?.Toast;
  const ensureToastContainer = (): HTMLElement => {
    let c = qs("#np-toast-container");
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
  const showErrorNow = (message: string) => {
    if (hasBS()) {
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
      const body = t.querySelector(".toast-body");
      if (body) body.textContent = message ?? errFb;
      try {
        bootstrap.Toast.getOrCreateInstance(t, {
          autohide: true,
          delay: 4000,
        }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const schedulePointerupError = (msg: string) => {
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
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const localize = (el: HTMLElement, key: string) => {
    const err = errFb;
    const dataClient = dataClientLocalized;
    const dataGuard = dataGuardMsg;
    if (
      el?.getAttribute?.("data-sv-localized") === "true" ||
      el?.getAttribute?.(dataClient) === "true"
    ) {
      return el.getAttribute(dataGuard) || err;
    }
    let lang = (
      window.sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ??
      "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    const msgKey = key;
    const msg =
      window.translations?.[lang]?.[msgKey] ||
      el?.getAttribute?.(dataGuard) ||
      window.translations?.en?.[msgKey] ||
      err;
    if (msg !== err && el) {
      el.setAttribute(dataGuard, msg);
      el.setAttribute(dataClient, "true");
    }
    return msg;
  };
  const verifyRoute = (candidate: string) => {
    const a = document.createElement("a");
    a.setAttribute("data-url", candidate ?? "");
    a.href = candidate ?? "";
    const url = a.getAttribute("data-url");
    const href = a.href;
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
  };
  const init = (): void => {
    try {
      const host = document.body;
      if (!host || host.getAttribute(dataBindGuard) === "true") return;
      host.setAttribute(dataBindGuard, "true");
      const containerId = "payment-form-container";
      const ctn = qs("#" + containerId);
      if (!ctn) {
        schedulePointerupError(
          localize(document.body, "payment_init_unavailable"),
        );
        return;
      }
      const BrickCtor = window.Brick;
      if (typeof BrickCtor !== "function") {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("Brick library unavailable");
        } catch (_) {}
        schedulePointerupError(localize(document.body, "plugin_unavailable"));
        return;
      }
      const action =
        '{{route(ViewsConstants::PLN.".pay.with.paymentwall",[$data["plan_id"],$data["coupon"]])}}';
      if (!verifyRoute(action)) {
        schedulePointerupError(localize(document.body, "route_unavailable"));
        return;
      }
      const Brick = BrickCtor as unknown as {
        new (
          o: Record<string, unknown>,
        ): Record<string, (...a: unknown[]) => void>;
      };
      const brick = new Brick({
        public_key: "{{ $admin_payment_setting[paymentwall_public_key'] }}",
        amount: "{{$plan->price }}",
        currency: "{{AppModelsUtility::getValByName('site_currency')}}",
        container: containerId,
        action: action,
        form: {
          merchant: "Paymentwall",
          product: "{{$plan->name}}",
          pay_button: "Pay",
          show_zip: true,
          show_cardholder: true,
        },
      });
      const toErr = '{{route("error.plan.show",1)}}';
      const toOk = '{{route("error.plan.show",2)}}';
      const go = (target: string) => {
        if (!verifyRoute(target)) {
          schedulePointerupError(
            localize(document.body, "payment_redirect_unavailable"),
          );
          return;
        }
        window.location.href = target;
      };
      brick.showPaymentForm(
        function (data: unknown) {
          try {
            const f = Number((data as { flag?: unknown })?.flag ?? 0);
            go(f === 1 ? toErr : toOk);
          } catch (_) {
            schedulePointerupError(
              localize(document.body, "payment_redirect_unavailable"),
            );
          }
        },
        function (errors: unknown) {
          try {
            const f = Number((errors as { flag?: unknown })?.flag ?? 0);
            go(f === 1 ? toErr : toOk);
          } catch (_) {
            schedulePointerupError(
              localize(document.body, "payment_redirect_unavailable"),
            );
          }
        },
      );
      const mo = new MutationObserver(function (): void {
        if (!document.body.contains(ctn)) {
          host.removeAttribute(dataBindGuard);
        }
      });
      mo.observe(document.documentElement, { childList: true, subtree: true });
    } catch (_) {
      schedulePointerupError(
        localize(document.body, "payment_init_unavailable"),
      );
    }
  };
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", init, { once: true });
  else init();
})();

export {};
