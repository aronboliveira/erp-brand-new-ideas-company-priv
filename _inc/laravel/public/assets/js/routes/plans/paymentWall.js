(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const dataBindGuard = "data-pw-bound";

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    return;
  }

  const verifyRoute = candidate => {
    const a = document.createElement("a");
    a.setAttribute("data-url", candidate ?? "");
    a.href = candidate ?? "";
    const url = a.getAttribute("data-url");
    const href = a.href;
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
  };

  const init = () => {
    try {
      const host = document.body;
      if (!host || host.getAttribute(dataBindGuard) === "true") return;
      host.setAttribute(dataBindGuard, "true");
      const containerId = "payment-form-container";
      const ctn = document.querySelector("#" + containerId);
      if (!ctn) {
        scheduleError(getMsg("payment_init_unavailable"), "pointerup");
        return;
      }
      const BrickCtor = window.Brick;
      if (typeof BrickCtor !== "function") {
        scheduleError(getMsg("plugin_unavailable"), "pointerup");
        return;
      }
      const action =
        '{{route(ViewsConstants::PLN.".pay.with.paymentwall",[$data["plan_id"],$data["coupon"]])}}';
      if (!verifyRoute(action)) {
        scheduleError(getMsg("route_unavailable"), "pointerup");
        return;
      }
      const brick = new BrickCtor({
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
      const go = target => {
        if (!verifyRoute(target)) {
          scheduleError(getMsg("payment_redirect_unavailable"), "pointerup");
          return;
        }
        window.location.href = target;
      };
      brick.showPaymentForm(
        function (data) {
          try {
            const f = Number((data && data.flag) ?? 0);
            go(f === 1 ? toErr : toOk);
          } catch (_) {
            scheduleError(getMsg("payment_redirect_unavailable"), "pointerup");
          }
        },
        function (errors) {
          try {
            const f = Number((errors && errors.flag) ?? 0);
            go(f === 1 ? toErr : toOk);
          } catch (_) {
            scheduleError(getMsg("payment_redirect_unavailable"), "pointerup");
          }
        },
      );
    } catch (_) {
      scheduleError(getMsg("payment_init_unavailable"), "pointerup");
    }
  };

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", init, { once: true });
  else init();
})();
