/**
 * @fileoverview TypeScript version of public/assets/js/routes/bugStatus/order.js
 * @generated from original JavaScript - manual review recommended
 * @module order
 */

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const errFb = "# ERROR";
  const guardMsgKey = "data-guard-msg";
  const clientFlag = "data-client-localized";
  const langKey = "erp-np-lang";
  let errorMessage = "";
  const translations = (window as unknown as Record<string, unknown>)
    .translations as Record<string, Record<string, string>> | undefined;
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  function getLocalizedMessage(key: string, el: HTMLElement) {
    let msg = errFb;
    if (el.getAttribute(clientFlag) === "true") {
      msg = el.getAttribute(guardMsgKey) ?? msg;
    } else {
      let lang = (
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

  function showError(message: string): void {
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
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
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
    muts.forEach(m => {
      m.removedNodes.forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", (): void => {
    document.querySelectorAll<HTMLElement>(".sortable").forEach((el): void => {
      if (el.dataset.listenerAttached === "true") return;
      el.dataset.listenerAttached = "true";
      try {
        $(el)
          .sortable()
          .disableSelection()
          .on("sortstop", function (): void {
            try {
              const order: (string | null)[] = [];
              // eslint-disable-next-line @typescript-eslint/no-unsafe-call
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
              this.querySelectorAll("li").forEach(
                (li: Element, idx: number) => {
                  order[idx] = li.getAttribute("data-id");
                },
              );
              const url =
                "{{route(ViewsConstants::BUG_STT.'.order')}}" as string;
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
              // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
              // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument
              errorMessage = getLocalizedMessage(e.message, el);
            }
          });
      } catch {
        errorMessage = getLocalizedMessage("bugstatus_order_failed", el);
      }
      const obsEl = new MutationObserver((m, _o) => {
        m.forEach(mut => {
          mut.removedNodes.forEach(node => {
            if (node === el) {
              $(el).sortable("destroy");
              obsEl.disconnect();
            }
          });
        });
      });
      obsEl.observe(document.body, { childList: true, subtree: true });
    });
  });
})();

export {};
