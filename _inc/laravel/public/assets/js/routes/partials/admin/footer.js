(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const removeClassByPrefix = (node, prefix) => {
    node?.classList?.forEach(cls => {
      if (cls.startsWith(prefix)) node.classList.remove(cls);
    });
  };

  // feather replace
  try {
    window.feather?.replace?.();
  } catch {
    scheduleError(getMsg("feather_replace_failed"), "click");
  }

  // pctoggler
  const pctoggle = document.querySelector("#pct-toggler");
  if (pctoggle && pctoggle.dataset.listenerAttached !== "true") {
    pctoggle.dataset.listenerAttached = "true";
    const obs1 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        m.removedNodes.forEach(n => {
          if (n === pctoggle) {
            pctoggle.removeEventListener("click", onPctoggle);
            o.disconnect();
          }
        }),
      );
    });
    obs1.observe(document.body, { childList: true, subtree: true });
    pctoggle.addEventListener("click", onPctoggle);
  }
  function onPctoggle() {
    try {
      const customizer = document.querySelector(".pct-customizer");
      if (!customizer) throw new Error();
      customizer.classList.toggle("active");
    } catch {
      scheduleError(getMsg("pctoggle_failed"), "click");
    }
  }

  // theme color switches
  document.querySelectorAll(".themes-color > a").forEach(el => {
    if (el.dataset.listenerAttached === "true") return;
    el.dataset.listenerAttached = "true";
    const obs2 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        m.removedNodes.forEach(n => {
          if (n === el) {
            el.removeEventListener("click", onThemeColor);
            o.disconnect();
          }
        }),
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
      scheduleError(getMsg("themecolor_failed"), "click");
    }
  }

  // custom theme background
  const custthemebg = document.querySelector("#cust-theme-bg");
  if (custthemebg && custthemebg.dataset.listenerAttached !== "true") {
    custthemebg.dataset.listenerAttached = "true";
    const obs3 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        m.removedNodes.forEach(n => {
          if (n === custthemebg) {
            custthemebg.removeEventListener("click", onCustThemeBg);
            o.disconnect();
          }
        }),
      );
    });
    obs3.observe(document.body, { childList: true, subtree: true });
    custthemebg.addEventListener("click", onCustThemeBg);
  }
  function onCustThemeBg() {
    try {
      const sidebar = document.querySelector(".dash-sidebar");
      const header = document.querySelector(
        ".dash-header:not(.dash-mob-header)",
      );
      if (!sidebar || !header) throw new Error();
      if (custthemebg.checked) {
        sidebar.classList.add("transprent-bg");
        header.classList.add("transprent-bg");
      } else {
        sidebar.classList.remove("transprent-bg");
        header.classList.remove("transprent-bg");
      }
    } catch {
      scheduleError(getMsg("custthemebg_failed"), "click");
    }
  }

  // custom dark layout toggle
  const custdarklayout = document.querySelector("#cust-darklayout");
  if (custdarklayout && custdarklayout.dataset.listenerAttached !== "true") {
    custdarklayout.dataset.listenerAttached = "true";
    const obs4 = new MutationObserver((ms, o) => {
      ms.forEach(m =>
        m.removedNodes.forEach(n => {
          if (n === custdarklayout) {
            custdarklayout.removeEventListener("click", onCustDark);
            o.disconnect();
          }
        }),
      );
    });
    obs4.observe(document.body, { childList: true, subtree: true });
    custdarklayout.addEventListener("click", onCustDark);
  }
  function onCustDark() {
    try {
      const linkEl = document.querySelector("#main-style");
      const logoEl = document.querySelector(".m-header > .b-brand > .logo-lg");
      if (!linkEl || !logoEl) throw new Error();
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
      scheduleError(getMsg("custdarklayout_failed"), "click");
    }
  }
})();
