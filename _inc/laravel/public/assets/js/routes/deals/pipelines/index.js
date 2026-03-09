/**
 * @file Deals Pipelines Index Route Guard
 * @description Handles pipeline change select with i18n error messages
 * @note Complex file with custom form submission logic - partially converted
 */
(() => {
  try {
    const guard = window.ERPGuard;
    const ERR_FB = "# ERROR";
    const FL_CLIENT = "data-client-localized";
    const FL_GUARD = "data-guard-msg";
    const LANG_KEY = "erp-np-lang";
    let errorMessage = "";

    const getMsg = (key, el) => {
      let msg = ERR_FB;
      if (el.getAttribute(FL_CLIENT) === "true") {
        msg = el.getAttribute(FL_GUARD) || msg;
      } else {
        let lang = (
          sessionStorage.getItem(LANG_KEY) ||
          document.documentElement.lang ||
          "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          translations?.[lang]?.[key] ??
          el.getAttribute(FL_GUARD) ??
          translations?.["en"]?.[key] ??
          msg;
        if (msg !== ERR_FB) {
          el.setAttribute(FL_GUARD, msg);
          el.setAttribute(FL_CLIENT, "true");
        }
      }
      return msg;
    };

    const showError = message => {
      if (guard) {
        guard.showToast(message);
      } else {
        alert(message);
      }
    };

    const onUp = () => {
      if (errorMessage) {
        showError(errorMessage);
        errorMessage = "";
      }
    };
    document.addEventListener("pointerup", onUp);
    new MutationObserver((m, obs) => {
      m.forEach(mut =>
        Array.from(mut.removedNodes).forEach(n => {
          if (n === document.documentElement) {
            document.removeEventListener("pointerup", onUp);
            obs.disconnect();
          }
        }),
      );
    }).observe(document.body, { childList: true, subtree: true });

    document.addEventListener("DOMContentLoaded", () => {
      const sel = document.querySelector(
        ".change-pipeline select[name=default_pipeline_id]",
      );
      if (!sel) return;
      if (sel.dataset.listenerAttached === "true") return;
      sel.dataset.listenerAttached = "true";

      const handler = () => {
        try {
          const form = document.getElementById("change-pipeline");
          if (!form) throw new Error("pipeline_change_failed");
          form.submit();
        } catch (e) {
          errorMessage = getMsg("pipeline_change_failed", sel);
        }
      };

      sel.addEventListener("change", handler);
      new MutationObserver((m, obs) => {
        m.forEach(mut =>
          Array.from(mut.removedNodes).forEach(n => {
            if (n === sel) {
              sel.removeEventListener("change", handler);
              obs.disconnect();
            }
          }),
        );
      }).observe(document.body, { childList: true, subtree: true });
    });
  } catch (err) {
    console.error("Error initializing deals/pipelines index:", err);
  }
})();
