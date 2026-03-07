/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/convert.js
 * @generated from original JavaScript - manual review recommended
 * @module convert
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const L1 = "data-client-toggle-listener";
  const L2 = "data-guard-listener";
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const ERR = "# ERROR";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  function hasBootstrapCss() {
    try {
      return !!document.querySelector(
        'link[rel~="stylesheet"][href*="bootstrap"]',
      );
    } catch (_) {
      return false;
    }
  }
  function toast(msg: string): void{
    try {
      if (hasBootstrapCss() && window.bootstrap.Toast) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        const b = document.createElement("div");
        b.className = "toast-body";
        b.textContent = msg;
        t.appendChild(b);
        c.appendChild(t);
        window.bootstrap.Toast.getOrCreateInstance(t).show();
      } else {
        alert(msg);
      }
    } catch (_) {
      alert(msg);
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    }
  }
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  function getMsg(el: HTMLElement, key: string) {
    try {
      let msg = ERR;
      if (el.getAttribute(DSL) === "true" || el.getAttribute(DCL) === "true")
        msg = el.getAttribute(DGM) || ERR;
      else {
        let lang = (
          window.sessionStorage.getItem("erp-np-lang") ??
          document.documentElement.lang ??
          "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        msg =
          window.translations?.[lang]?.[key] ||
          el.getAttribute(DGM) ||
          window.translations?.en?.[key] ||
          ERR;
        if (msg !== ERR) {
          el.setAttribute(DGM, msg);
          el.setAttribute(DCL, "true");
        }
      }
      return msg || ERR;
    } catch (_) {
      return ERR;
    }
  }
  function toggleBlocks(isExist: boolean): void{
    try {
      const $ = window.jQuery;
      if (!$) return;
      const $exist = $(".exist_client");
      const $new = $(".new_client");
      if (isExist) {
        $exist.removeClass("d-none");
        $new.addClass("d-none");
        $new.find("input").removeAttr("required");
      } else {
        $exist.addClass("d-none");
        $new.removeClass("d-none");
        $new.find("input").attr("required", "required");
      }
    } catch (_) {}
  }
  function bindToggle(): void{
    try {
      const $ = window.jQuery;
      if (!$) return;
      const $radios = $('input[name="client_check"]');
      if (!$radios.length) return;
      const el = $radios.get(0);
      if (el.getAttribute(L1) === "true") return;
      el.setAttribute(L1, "true");
      const initVal = $radios.filter(":checked").val();
      toggleBlocks(initVal === "exist");
      $radios
        .off("click.convertDeal")
        .on("click.convertDeal", function (): void {
          try {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            toggleBlocks(this.value === "exist");
          } catch (_) {}
        });
      const obs = new MutationObserver(function (): void {
        if (!document.body.contains(el)) {
          try {
            $radios.off("click.convertDeal");
          } catch (_) {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    } catch (_) {}
  }
  function bindSubmitGuard(): void{
    try {
      const $ = window.jQuery;
      if (!$) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("jQuery not found for convertDeal");
        } catch (_) {}
        return;
      }
      const form = document.getElementById(
        "lead-convert-form",
      ) as HTMLFormElement | null;
      const btn = document.getElementById("lead-convert-submit");
      if (!form || !btn) return;
      if (form.getAttribute(L2) === "true") return;
      form.setAttribute(L2, "true");
      $(btn)
        .off("click.convertDealGuard")
        .on("click.convertDealGuard", function (e: Event) {
          try {
            const url = form.getAttribute("data-url");
            const href = form.action;
            if ((!url || url === "#") && (!href || href === "#")) {
              e.preventDefault();
              toast(getMsg(form as HTMLElement, "action_unavailable"));
            }
          } catch (_) {
            e.preventDefault();
            toast(getMsg(form as HTMLElement, "action_unavailable"));
          }
        });
      const obs2 = new MutationObserver(function (): void {
        if (!document.body.contains(form) || !document.body.contains(btn)) {
          try {
            $(btn).off("click.convertDealGuard");
          } catch (_) {}
          obs2.disconnect();
        }
      });
      obs2.observe(document.body, { childList: true, subtree: true });
    } catch (_) {}
  }
  try {
    const $ = window.jQuery;
    if (!$) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Failed to initialize convertDeal: jQuery missing");
      } catch (_) {}
      return;
    }
    $(function (): void {
      bindToggle();
      bindSubmitGuard();
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to initialize convertDeal");
    } catch (__) {}
  }
})();

export {};
