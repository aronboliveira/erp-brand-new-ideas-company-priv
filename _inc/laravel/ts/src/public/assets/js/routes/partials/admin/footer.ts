/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/footer.js
 * @generated from original JavaScript - manual review recommended
 * @module footer
 */


((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const langSessionKey = "erp-np-lang";

  const getLocalizedMessage = (msgKey: string, el: HTMLElement): string => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) ?? errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem(langSessionKey) ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ??
        el.getAttribute(dataGuardMsg) ??
        window.translations?.en?.[msgKey] ??
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const showError = (message: string): void => {
    try {
      let container = document.querySelector<HTMLElement>(
        "#bootstrap-toast-container",
      );
      if (!container) {
        const hasBs =
          Array.from(
            document.querySelectorAll<HTMLLinkElement>(
              'link[rel="stylesheet"]',
            ),
          ).some((l: HTMLLinkElement) => /bootstrap/i.test(l.href)) &&
          window.bootstrap.Toast;
        if (hasBs) {
          container = document.createElement("div");
          container.id = "bootstrap-toast-container";
          container.setAttribute("aria-live", "polite");
          container.setAttribute("aria-atomic", "true");
          document.body.appendChild(container);
        }
      }
      if (container && window.bootstrap.Toast) {
        let toast = container.querySelector(".toast");
        if (!toast) {
          toast = document.createElement("div");
          toast.className = "toast";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          const body = document.createElement("div");
          body.className = "toast-body";
          toast.appendChild(body);
          container.appendChild(toast);
          if (toast.getAttribute("data-click-listener") !== "true") {
            toast.addEventListener("click", () => (body.textContent = message));
            toast.setAttribute("data-click-listener", "true");
          }
        }
        const toastBody = toast.querySelector(".toast-body");
        if (toastBody) toastBody.textContent = message;
        new bootstrap.Toast(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };

  const removeClassByPrefix = (node: Element, prefix: string): void => {
    node.classList.forEach((cls: string) => {
      if (cls.startsWith(prefix)) node.classList.remove(cls);
    });
  };

  // feather replace
  try {
    if (window.feather) window.feather.replace();
  } catch {
    showError(getLocalizedMessage("feather_replace_failed", document.body));
  }

  // pctoggler
  const pctoggle = document.querySelector<HTMLElement>("#pct-toggler");
  if (pctoggle && pctoggle.dataset.listenerAttached !== "true") {
    pctoggle.dataset.listenerAttached = "true";
    const obs1 = new MutationObserver((ms, o) => {
      ms.forEach(m => {
        m.removedNodes.forEach(n => {
          if (n === pctoggle) {
            pctoggle.removeEventListener("click", onPctoggle);
            o.disconnect();
          }
        });
      });
    });
    obs1.observe(document.body, { childList: true, subtree: true });
    pctoggle.addEventListener("click", onPctoggle);
  }
  function onPctoggle(): void {
    try {
      const customizer = document.querySelector<HTMLElement>(".pct-customizer");
      if (!customizer) throw new Error();
      customizer.classList.toggle("active");
    } catch {
      if (pctoggle) showError(getLocalizedMessage("pctoggle_failed", pctoggle));
    }
  }

  // theme color switches
  document
    .querySelectorAll<HTMLElement>(".themes-color > a")
    .forEach((el: HTMLElement): void => {
      if (el.dataset.listenerAttached === "true") return;
      el.dataset.listenerAttached = "true";
      const obs2 = new MutationObserver((ms, o) => {
        ms.forEach(m => {
          m.removedNodes.forEach(n => {
            if (n === el) {
              el.removeEventListener("click", onThemeColor);
              o.disconnect();
            }
          });
        });
      });
      obs2.observe(document.body, { childList: true, subtree: true });
      el.addEventListener("click", onThemeColor);
    });
  function onThemeColor(e: Event): void {
    try {
      let tgt = e.target as HTMLElement | null;
      if (tgt && tgt.tagName === "SPAN")
        tgt = tgt.parentNode as HTMLElement | null;
      const val = tgt?.getAttribute("data-value") ?? "";
      removeClassByPrefix(document.body, "theme-");
      document.body.classList.add(val);
    } catch {
      const currentTarget = e.currentTarget as HTMLElement | null;
      if (currentTarget)
        showError(getLocalizedMessage("themecolor_failed", currentTarget));
    }
  }

  // custom theme background
  const custthemebg =
    document.querySelector<HTMLInputElement>("#cust-theme-bg");
  if (custthemebg && custthemebg.dataset.listenerAttached !== "true") {
    custthemebg.dataset.listenerAttached = "true";
    const obs3 = new MutationObserver((ms, o) => {
      ms.forEach(m => {
        m.removedNodes.forEach(n => {
          if (n === custthemebg) {
            custthemebg.removeEventListener("click", onCustThemeBg);
            o.disconnect();
          }
        });
      });
    });
    obs3.observe(document.body, { childList: true, subtree: true });
    custthemebg.addEventListener("click", onCustThemeBg);
  }
  function onCustThemeBg(): void {
    try {
      const sidebar = document.querySelector<HTMLElement>(".dash-sidebar");
      const header = document.querySelector<HTMLElement>(
        ".dash-header:not(.dash-mob-header)",
      );
      if (!sidebar || !header || !custthemebg) throw new Error();
      if (custthemebg.checked) {
        sidebar.classList.add("transprent-bg");
        header.classList.add("transprent-bg");
      } else {
        sidebar.classList.remove("transprent-bg");
        header.classList.remove("transprent-bg");
      }
    } catch {
      if (custthemebg)
        showError(getLocalizedMessage("custthemebg_failed", custthemebg));
    }
  }

  // custom dark layout toggle
  const custdarklayout =
    document.querySelector<HTMLInputElement>("#cust-darklayout");
  if (custdarklayout && custdarklayout.dataset.listenerAttached !== "true") {
    custdarklayout.dataset.listenerAttached = "true";
    const obs4 = new MutationObserver((ms, o) => {
      ms.forEach(m => {
        m.removedNodes.forEach(n => {
          if (n === custdarklayout) {
            custdarklayout.removeEventListener("click", onCustDark);
            o.disconnect();
          }
        });
      });
    });
    obs4.observe(document.body, { childList: true, subtree: true });
    custdarklayout.addEventListener("click", onCustDark);
  }
  function onCustDark(): void {
    try {
      const linkEl = document.querySelector<HTMLElement>("#main-style");
      const logoEl = document.querySelector<HTMLElement>(
        ".m-header > .b-brand > .logo-lg",
      );
      if (!linkEl || !logoEl || !custdarklayout) throw new Error();
      if (custdarklayout.checked) {
        linkEl.setAttribute("href", '{{ asset("assets/css/style-dark.css") }}');
        logoEl.setAttribute("src", '{{ asset("/storage/uploads/logo/{}") }}');
      } else {
        linkEl.setAttribute("href", '{{ asset("assets/css/style.css") }}');
        logoEl.setAttribute(
          "src",
          '{{ asset("/uploads/logo/2-logo-dark.png") }}',
        );
      }
    } catch {
      if (custdarklayout)
        showError(getLocalizedMessage("custdarklayout_failed", custdarklayout));
    }
  }
})();

export {};
