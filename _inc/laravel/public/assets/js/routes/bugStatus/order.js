(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  if (!guard || !utils) return;

  let errorMessage = "";

  function getLocalizedMessage(key, el) {
    return utils.getTranslation(key) || "# ERROR";
  }

  function showError(message) {
    guard.showToast(message);
  }

  const onErrorPointerUp = () => {
    if (errorMessage) {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onErrorPointerUp);
  new MutationObserver((muts, obs) => {
    muts.forEach(m =>
      m.removedNodes.forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      }),
    );
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".sortable").forEach(el => {
      if (el.dataset.listenerAttached === "true") return;
      el.dataset.listenerAttached = "true";
      try {
        $(el)
          .sortable()
          .disableSelection()
          .on("sortstop", function () {
            try {
              const order = [];
              this.querySelectorAll("li").forEach((li, idx) => {
                order[idx] = li.getAttribute("data-id");
              });
              const url = "{{route(ViewsConstants::BUG_STT.'.order')}}";
              if (!url) throw new Error("bugstatus_order_failed");
              $.ajax({
                url,
                type: "POST",
                data: {
                  order,
                  _token: $('meta[name="csrf-token"]').attr("content"),
                },
              }).fail(() => {
                throw new Error("bugstatus_order_failed");
              });
            } catch (e) {
              errorMessage = getLocalizedMessage(e.message, el);
            }
          });
      } catch {
        errorMessage = getLocalizedMessage("bugstatus_order_failed", el);
      }
      const obsEl = new MutationObserver((m, o) => {
        m.forEach(mut =>
          mut.removedNodes.forEach(node => {
            if (node === el) {
              $(el).sortable("destroy");
              obsEl.disconnect();
            }
          }),
        );
      });
      obsEl.observe(document.body, { childList: true, subtree: true });
    });
  });
})();
