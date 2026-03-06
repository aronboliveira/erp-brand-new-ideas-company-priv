/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/footer.js
 * @generated from original JavaScript - manual review recommended
 * @module footer
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global feather, bootstrap */
((): void => {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const langSessionKey = "erp-np-lang";

  const getLocalizedMessage = (msgKey, el) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) ?? errFb;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
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

  const showError = message => {
    try {
      let container = document.querySelector<HTMLElement>("#bootstrap-toast-container");
      if (!container) {
        const hasBs =
          Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(
            l => /bootstrap/i.test(l.href)
          ) && window.bootstrap.Toast;
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (hasBs) {
          container = document.createElement("div");
          container.id = "bootstrap-toast-container";
          container.setAttribute("aria-live", "polite");
          container.setAttribute("aria-atomic", "true");
          document.body.appendChild(container);
        }
      }
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
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
        toast.querySelector(".toast-body").textContent = message;
        new bootstrap.Toast(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  };

  const removeClassByPrefix = (node, prefix) => {
    node?.classList?.forEach(cls => {
      if (cls.startsWith(prefix)) node.classList.remove(cls);
    });
  };

  // feather replace
  try {
    window.feather.replace();
  } catch {
    showError(getLocalizedMessage("feather_replace_failed", document.body));
  }

  // pctoggler
  const pctoggle = document.querySelector<HTMLElement>("#pct-toggler");
  if (pctoggle?.dataset.listenerAttached !== "true") {
    pctoggle.dataset.listenerAttached = "true";
    const obs1 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        { m.removedNodes.forEach(n => {
          if (n === pctoggle) {
            pctoggle.removeEventListener("click", onPctoggle);
            o.disconnect();
          }
        }); }
      );
    });
    obs1.observe(document.body, { childList: true, subtree: true });
    pctoggle.addEventListener("click", onPctoggle);
  }
  function onPctoggle() {
    try {
      const customizer = document.querySelector<HTMLElement>(".pct-customizer");
      if (!customizer) throw new Error();
      customizer.classList.toggle("active");
    } catch {
      showError(getLocalizedMessage("pctoggle_failed", pctoggle));
    }
  }

  // theme color switches
  document.querySelectorAll(".themes-color > a").forEach((el: Element): void => {
    if (el.dataset.listenerAttached === "true") return;
    el.dataset.listenerAttached = "true";
    const obs2 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        { m.removedNodes.forEach(n => {
          if (n === el) {
            el.removeEventListener("click", onThemeColor);
            o.disconnect();
          }
        }); }
      );
    });
    obs2.observe(document.body, { childList: true, subtree: true });
    el.addEventListener("click", onThemeColor);
  });
  function onThemeColor(e) {
    try {
      let tgt = e.target;
      if (tgt.tagName === "SPAN") tgt = tgt.parentNode;
      const val = tgt.getAttribute("data-value") ?? "";
      removeClassByPrefix(document.body, "theme-");
      document.body.classList.add(val);
    } catch {
      showError(getLocalizedMessage("themecolor_failed", e.currentTarget));
    }
  }

  // custom theme background
  const custthemebg = document.querySelector<HTMLElement>("#cust-theme-bg");
  if (custthemebg?.dataset.listenerAttached !== "true") {
    custthemebg.dataset.listenerAttached = "true";
    const obs3 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        { m.removedNodes.forEach(n => {
          if (n === custthemebg) {
            custthemebg.removeEventListener("click", onCustThemeBg);
            o.disconnect();
          }
        }); }
      );
    });
    obs3.observe(document.body, { childList: true, subtree: true });
    custthemebg.addEventListener("click", onCustThemeBg);
  }
  function onCustThemeBg() {
    try {
      const sidebar = document.querySelector<HTMLElement>(".dash-sidebar");
      const header = document.querySelector<HTMLElement>(".dash-header:not(.dash-mob-header)");
      if (!sidebar || !header) throw new Error();
      if (custthemebg.checked) {
        sidebar.classList.add("transprent-bg");
        header.classList.add("transprent-bg");
      } else {
        sidebar.classList.remove("transprent-bg");
        header.classList.remove("transprent-bg");
      }
    } catch {
      showError(getLocalizedMessage("custthemebg_failed", custthemebg));
    }
  }

  // custom dark layout toggle
  const custdarklayout = document.querySelector<HTMLElement>("#cust-darklayout");
  if (custdarklayout?.dataset.listenerAttached !== "true") {
    custdarklayout.dataset.listenerAttached = "true";
    const obs4 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        { m.removedNodes.forEach(n => {
          if (n === custdarklayout) {
            custdarklayout.removeEventListener("click", onCustDark);
            o.disconnect();
          }
        }); }
      );
    });
    obs4.observe(document.body, { childList: true, subtree: true });
    custdarklayout.addEventListener("click", onCustDark);
  }
  function onCustDark() {
    try {
      const linkEl = document.querySelector<HTMLElement>("#main-style");
      const logoEl = document.querySelector<HTMLElement>(".m-header > .b-brand > .logo-lg");
      if (!linkEl || !logoEl) throw new Error();
      if (custdarklayout.checked) {
        linkEl.setAttribute("href", '{{ asset("assets/css/style-dark.css") }}');
        logoEl.setAttribute("src", '{{ asset("/storage/uploads/logo/{}") }}');
      } else {
        linkEl.setAttribute("href", '{{ asset("assets/css/style.css") }}');
        logoEl.setAttribute(
          "src",
          '{{ asset("/uploads/logo/2-logo-dark.png") }}'
        );
      }
    } catch {
      showError(getLocalizedMessage("custdarklayout_failed", custdarklayout));
    }
  }
})();

export {};
