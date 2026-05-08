/**
 * @fileoverview TypeScript version of public/assets/js/routes/plans/paymentWall.js
 * @generated from original JavaScript - manual review recommended
 * @module paymentWall
 */

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const errFb = "# ERROR",
    dataClientLocalized = "data-client-localized",
    dataGuardMsg = "data-guard-msg",
    _dataSvLocalized = "data-sv-localized",
    dataErrGuard = "data-pw-error",
    dataBindGuard = "data-pw-bound";
  const qs = <T extends Element = HTMLElement>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ??
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap.Toast;
  const ensureToastContainer = (): HTMLElement => {
    let c = qs("#np-toast-container");
    if (c) return c;
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string): void => {
    if (hasBS()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
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
  const schedulePointerupError = (msg: string): void => {
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
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const localize = (el: HTMLElement, key: string) => {
    const err = errFb,
      dataClient = dataClientLocalized,
      dataGuard = dataGuardMsg;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClient) === "true"
    )
      return el.getAttribute(dataGuard) || err;
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
      el.getAttribute(dataGuard) ||
      window.translations?.en?.[msgKey] ||
      err;
    if (msg !== err && el) {
      el.setAttribute(dataGuard, msg);
      el.setAttribute(dataClient, "true");
    }
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    return msg;
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const verifyRoute = (candidate: string) => {
    const a = document.createElement("a");
    a.setAttribute("data-url", candidate ?? "");
    a.href = candidate ?? "";
    const url = a.getAttribute("data-url"),
      href = a.href;
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
  };
  const init = (): void => {
    try {
      const host = document.body;
      if (!host || host.getAttribute(dataBindGuard) === "true") return;
      host.setAttribute(dataBindGuard, "true");
      const containerId = "payment-form-container",
        ctn = qs("#" + containerId);
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
        } catch (_) {
          console.error(`[paymentWall] Error:`, _);
        }
        schedulePointerupError(localize(document.body, "plugin_unavailable"));
        return;
      }
      const action =
        '{{route(ViewsConstants::PLN.".pay.with.paymentwall",[$data["plan_id"],$data["coupon"]])}}';
      if (!verifyRoute(action)) {
        schedulePointerupError(localize(document.body, "route_unavailable"));
        return;
      }
      const Brick = BrickCtor as unknown as new (
        o: Record<string, unknown>,
      ) => Record<string, (...a: unknown[]) => void>;
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
        }),
        toErr = '{{route("error.plan.show",1)}}',
        toOk = '{{route("error.plan.show",2)}}';
      const go = (target: string): void => {
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
            const f = Number((data as { flag?: unknown }).flag ?? 0);
            go(f === 1 ? toErr : toOk);
          } catch (_) {
            schedulePointerupError(
              localize(document.body, "payment_redirect_unavailable"),
            );
          }
        },
        function (errors: unknown) {
          try {
            const f = Number((errors as { flag?: unknown }).flag ?? 0);
            go(f === 1 ? toErr : toOk);
          } catch (_) {
            schedulePointerupError(
              localize(document.body, "payment_redirect_unavailable"),
            );
          }
        },
      );
      const mo = new MutationObserver(function (): void {
        if (!document.body.contains(ctn)) host.removeAttribute(dataBindGuard);
      });
      mo.observe(document.documentElement, { childList: true, subtree: true });
    } catch (_) {
      schedulePointerupError(
        localize(document.body, "payment_init_unavailable"),
      );
    }
  };
  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", init, { once: true })
    : init();
})();

export {};
