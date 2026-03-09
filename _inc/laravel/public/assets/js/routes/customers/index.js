(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  let errorMessage = "";

  const getLocalizedMessage = (key, el) => {
    return utils.getTranslation(key) || "# ERROR";
  };

  const showError = message => {
    guard.showToast(message);
  };

  const onPointerUp = () => {
    if (errorMessage) {
      showError(errorMessage);
      errorMessage = "";
    }
  };

  document.addEventListener("pointerup", onPointerUp);

  new MutationObserver((muts, obs) => {
    muts.forEach(m =>
      Array.from(m.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onPointerUp);
          obs.disconnect();
        }
      }),
    );
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("billing_data");
    if (!btn || btn.dataset.listenerAttached === "true") return;
    btn.dataset.listenerAttached = "true";

    const handler = () => {
      try {
        const fields = [
          "name",
          "country",
          "state",
          "city",
          "phone",
          "zip",
          "address",
        ];
        fields.forEach(key => {
          const bill = $(`[name='billing_${key}']`);
          const ship = $(`[name='shipping_${key}']`);
          if (!bill.length || !ship.length) {
            throw new Error("shipping_copy_failed");
          }
          ship.val(bill.val());
        });
      } catch (e) {
        errorMessage = getLocalizedMessage(e.message, btn);
      }
    };

    btn.addEventListener("click", handler);
    new MutationObserver((muts, obs) => {
      muts.forEach(m =>
        Array.from(m.removedNodes).forEach(n => {
          if (n === btn) {
            btn.removeEventListener("click", handler);
            obs.disconnect();
          }
        }),
      );
    }).observe(document.body, { childList: true, subtree: true });
  });
})();
