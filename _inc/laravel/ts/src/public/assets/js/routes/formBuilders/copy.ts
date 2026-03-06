/**
 * @fileoverview TypeScript version of public/assets/js/routes/formBuilders/copy.js
 * @generated from original JavaScript - manual review recommended
 * @module copy
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */

((): void => {
  const SUCCESS_KEY = "link_copy_success";
  const FAILURE_KEY = "link_copy_failed";
  const LISTENER_ATTR = "data-copy-listener";
  const SELECTOR = [".cp_link", ".iframe_link"];
  const showMsg = (key, isError = false) => {
    const msg = ((): void => {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      return (
        window.translations?.[lang]?.[key] ||
        window.translations?.en?.[key] ||
        "# ERROR"
      );
    })();
    show_toastr(isError ? "error" : "success", msg);
  };

  const copyText = async text => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(text);
      } else {
        const tmp = document.createElement("input");
        document.body.append(tmp);
        tmp.value = text;
        tmp.select();
        document.execCommand("copy");
        tmp.remove();
      }
      showMsg(SUCCESS_KEY);
    } catch {
      showMsg(FAILURE_KEY, true);
    }
  };

  const attach = el => {
    if (el.getAttribute(LISTENER_ATTR) === "true") return;
    el.setAttribute(LISTENER_ATTR, "true");
    el.addEventListener("click", e => {
      e.preventDefault();
      const link = el.getAttribute("data-link");
      if (!link) {
        showMsg(FAILURE_KEY, true);
        return;
      }
      void copyText(link);
    });
  };

  const init = (): void => {
    SELECTOR.forEach(sel => {
      document.querySelectorAll(sel).forEach(attach);
    });

    const mo = new MutationObserver((): void => {
      SELECTOR.forEach(sel => {
        document.querySelectorAll(sel).forEach(attach);
      });
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

export {};
