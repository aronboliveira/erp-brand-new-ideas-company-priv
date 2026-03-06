/**
 * @fileoverview TypeScript version of public/assets/js/routes/bugStatus/order.js
 * @generated from original JavaScript - manual review recommended
 * @module order
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const guardMsgKey = "data-guard-msg";
  const clientFlag = "data-client-localized";
  const langKey = "erp-np-lang";
  let errorMessage = "";

  function getLocalizedMessage(key, el) {
    let msg = errFb;
    if (el.getAttribute(clientFlag) === "true") {
      msg = el.getAttribute(guardMsgKey) ?? msg;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        sessionStorage.getItem(langKey) ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        translations?.[lang]?.[key] ??
        el.getAttribute(guardMsgKey) ??
        translations?.en?.[key] ??
        msg;
      if (msg !== errFb) {
        el.setAttribute(guardMsgKey, msg);
        el.setAttribute(clientFlag, "true");
      }
    }
    return msg;
  }

  function showError(message) {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      const bs =
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
      if (bs) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = message;
        toast.appendChild(body);
        container.appendChild(toast);
        bootstrap.Toast.getOrCreateInstance(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  }

  const onErrorPointerUp = (): void => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onErrorPointerUp);
  new MutationObserver((muts, obs) => {
    muts.forEach(m =>
      { m.removedNodes.forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      }); }
    );
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", (): void => {
    document.querySelectorAll(".sortable").forEach((el: Element): void => {
      if (el.dataset.listenerAttached === "true") return;
      el.dataset.listenerAttached = "true";
      try {
        $(el)
          .sortable()
          .disableSelection()
          .on("sortstop", function (): void {
            try {
              const order = [];
              this.querySelectorAll("li").forEach((li, idx) => {
                order[idx] = li.getAttribute("data-id");
              });
              const url = "{{route(ViewsConstants::BUG_STT.'.order')}}";
              // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
              if (url === "") throw new Error("bugstatus_order_failed");
              $.ajax({
                url,
                type: "POST",
                data: {
                  order,
                  _token: $('meta[name="csrf-token"]').attr("content"),
                },
              }).fail((): void => {
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
          { mut.removedNodes.forEach(node => {
            if (node === el) {
              $(el).sortable("destroy");
              obsEl.disconnect();
            }
          }); }
        );
      });
      obsEl.observe(document.body, { childList: true, subtree: true });
    });
  });
})();

export {};
