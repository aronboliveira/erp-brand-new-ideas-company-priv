/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/pipelines/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const ERR_FB = "# ERROR",
    FL_CLIENT = "data-client-localized",
    FL_GUARD = "data-guard-msg",
    LANG_KEY = "erp-np-lang";
  let errorMessage = "";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (key: string, el: HTMLElement) => {
    let msg = ERR_FB;
    if (el.getAttribute(FL_CLIENT) === "true") {
      msg = el.getAttribute(FL_GUARD) || msg;
    } else {
      let lang = (
        sessionStorage.getItem(LANG_KEY) ??
        (document.documentElement.lang || "en")
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ??
        el.getAttribute(FL_GUARD) ??
        window.translations?.en?.[key] ??
        msg;
      if (msg !== ERR_FB) {
        el.setAttribute(FL_GUARD, msg);
        el.setAttribute(FL_CLIENT, "true");
      }
    }
    return msg;
  };

  const showError = (message: string): void => {
    try {
      let c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      const bs =
        !!document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (bs) {
        const t = document.createElement("div");
        t.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = message;
        t.appendChild(b);
        c.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };

  const onUp = (): void => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onUp);
  new MutationObserver((m, obs) => {
    m.forEach(mut => {
      Array.from(mut.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onUp);
          obs.disconnect();
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });

  document.addEventListener("DOMContentLoaded", (): void => {
    const sel = document.querySelector<HTMLElement>(
      ".change-pipeline select[name=default_pipeline_id]",
    );
    if (!sel) return;
    if (sel.dataset.listenerAttached === "true") return;
    sel.dataset.listenerAttached = "true";

    const handler = (): void => {
      try {
        const form = document.getElementById("change-pipeline");
        if (!form) throw new Error("pipeline_change_failed");
        (form as HTMLFormElement).submit();
      } catch (_e) {
        errorMessage = getMsg("pipeline_change_failed", sel);
      }
    };

    if (!sel.getAttribute("data-listener-bound-change")) {
      sel.setAttribute("data-listener-bound-change", "1");
      sel.addEventListener("change", handler);
    }
    new MutationObserver((m, obs) => {
      m.forEach(mut => {
        Array.from(mut.removedNodes).forEach(n => {
          if (n === sel) {
            sel.removeEventListener("change", handler);
            obs.disconnect();
          }
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  });
})();

export {};
