/**
 * @fileoverview TypeScript version of public/assets/js/routes/customers/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

/* global bootstrap, $, jQuery */
((): void => {
  const ERR_FB = "# ERROR";
  const CLIENT_FLAG = "data-client-localized";
  const GUARD_MSG = "data-guard-msg";
  const LANG_KEY = "erp-np-lang";
  let errorMessage = "";

  function getLocalizedMessage(key: string, el: HTMLElement) {
    let msg = ERR_FB;
    if (el.getAttribute(CLIENT_FLAG) === "true") {
      msg = el.getAttribute(GUARD_MSG) || msg;
    } else {
      let lang = (
        sessionStorage.getItem(LANG_KEY) ??
        (document.documentElement.lang || "en")
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(GUARD_MSG) ||
        window.translations?.en?.[key] ||
        msg;
      if (msg !== ERR_FB) {
        el.setAttribute(GUARD_MSG, msg);
        el.setAttribute(CLIENT_FLAG, "true");
      }
    }
    return msg;
  }

  function showError(message: string) {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      const hasBs =
        !!document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (hasBs) {
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

  const onPointerUp = (): void => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onPointerUp);
  new MutationObserver((muts, obs) => {
    muts.forEach(m => {
      Array.from(m.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onPointerUp);
          obs.disconnect();
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", (): void => {
    const btn = document.getElementById("billing_data");
    if (!btn || btn.dataset.listenerAttached === "true") return;
    btn.dataset.listenerAttached = "true";

    const handler = (): void => {
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
          ship.val(bill.val() as string);
        });
      } catch (err) {
        errorMessage = getLocalizedMessage((err as Error).message, btn);
      }
    };

    btn.addEventListener("click", handler);
    new MutationObserver((muts, obs) => {
      muts.forEach(m => {
        Array.from(m.removedNodes).forEach(n => {
          if (n === btn) {
            btn.removeEventListener("click", handler);
            obs.disconnect();
          }
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  });
})();

export {};
