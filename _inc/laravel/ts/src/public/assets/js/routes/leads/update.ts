/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  /* assets/js/routes/leads/update.js */
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  (function () {
    const L = "data-guard-listener";
    const DCL = "data-client-localized";
    const DGM = "data-guard-msg";
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    const DSL = "data-sv-localized";
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
        // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
        alert(msg);
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
    function bindSubmitGuard(): void{
      try {
        const $ = window.jQuery;
        if (!$) {
          try {
            if (
              window.location.hostname === "localhost" ||
              window.location.hostname === "127.0.0.1"
            )
              console.error("jQuery not found for leads/update");
          } catch (_) {}
          return;
        }
        const form = document.getElementById(
          "lead-update-form",
        ) as HTMLFormElement | null;
        const btn = document.getElementById("lead-update-submit");
        if (!form || !btn) return;
        if (form.getAttribute(L) === "true") return;
        form.setAttribute(L, "true");
        $(btn)
          .off("click.leadsUpdateGuard")
          .on("click.leadsUpdateGuard", function (e: Event) {
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
        const obs = new MutationObserver(function (): void {
          if (!document.body.contains(form) || !document.body.contains(btn)) {
            try {
              $(btn).off("click.leadsUpdateGuard");
            } catch (_) {}
            obs.disconnect();
          }
        });
        obs.observe(document.body, { childList: true, subtree: true });
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
            console.error("Failed to initialize leads/update");
        } catch (_) {}
        return;
      }
      $(function (): void {
        bindSubmitGuard();
      });
    } catch (_) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("Failed to run leads/update");
      } catch (__) {}
    }
  })();
  const L = "data-guard-listener";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const DCL = "data-client-localized";
  const DGM = "data-guard-msg";
  const DSL = "data-sv-localized";
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
      // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
      }
    } catch (_) {
      alert(msg);
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
  function bindAiGuard(): void{
    try {
      const $ = window.jQuery;
      if (!$) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("jQuery not found for aiGenerateGuard");
        } catch (_) {}
        return;
      }
      const a = document.getElementById(
        "lead-ai-generate",
      ) as HTMLAnchorElement | null;
      if (!a || a.getAttribute(L) === "true") return;
      a.setAttribute(L, "true");
      $(a)
        .off("click.aiGuard")
        .on("click.aiGuard", function (e: Event) {
          try {
            const url = a.getAttribute("data-url");
            const href = a.href;
            if ((!url || url === "#") && (!href || href === "#")) {
              e.preventDefault();
              toast(getMsg(a as HTMLElement, "ai_generate_unavailable"));
            }
          } catch (_) {
            e.preventDefault();
            toast(getMsg(a as HTMLElement, "ai_generate_unavailable"));
          }
        });
      const obs2 = new MutationObserver(function (): void {
        if (!document.body.contains(a)) {
          try {
            $(a).off("click.aiGuard");
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
          console.error("Failed to initialize aiGenerateGuard");
      } catch (_) {}
      return;
    }
    $(function (): void {
      bindAiGuard();
    });
  } catch (_) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("Failed to run aiGenerateGuard");
    } catch (__) {}
  }
})();

export {};
