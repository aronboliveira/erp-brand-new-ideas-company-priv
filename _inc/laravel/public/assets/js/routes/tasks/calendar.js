(function () {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataCalGuard = "data-cal-guard";
  const ensureToastContainer = () => {
    let c = document.getElementById("np-toast-container");
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = message => {
    const hasBootstrap =
      (document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') ||
        document.querySelector('link[href*="bootstrap"]')) &&
      window.bootstrap &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      let t = document.getElementById("np-toast");
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = t.querySelector(".toast-body");
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleInteractiveError = message => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = () => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute(dataSvLocalized) === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const initCalendar = events => {
    const el = document.getElementById("calendar");
    if (!el) {
      scheduleInteractiveError(getMsg(document.body, "calendar_unavailable"));
      return;
    }
    try {
      if (!window.FullCalendar || !window.FullCalendar.Calendar) {
        try {
          console.error("FullCalendar unavailable");
        } catch (_) {}
        scheduleInteractiveError(getMsg(el, "plugin_unavailable"));
        return;
      }
      if (el._fcInstance) {
        try {
          el._fcInstance.destroy();
        } catch (_) {}
        el._fcInstance = null;
      }
      const calendar = new window.FullCalendar.Calendar(el, {
        headerToolbar: {
          left: "prev,next today",
          center: "title",
          right: "dayGridMonth,timeGridWeek,timeGridDay",
        },
        buttonText: {
          timeGridDay: "{{ __('Day') }}",
          timeGridWeek: "{{ __('Week') }}",
          dayGridMonth: "{{ __('Month') }}",
        },
        themeSystem: "bootstrap",
        slotDuration: "00:10:00",
        navLinks: true,
        droppable: true,
        selectable: true,
        selectMirror: true,
        editable: true,
        dayMaxEvents: true,
        handleWindowResize: true,
        events: events || [],
      });
      calendar.render();
      el._fcInstance = calendar;
      if (el.getAttribute(dataCalGuard) !== "true") {
        el.setAttribute(dataCalGuard, "true");
        const mo = new MutationObserver((m, o) => {
          if (!document.body.contains(el)) {
            try {
              calendar.destroy();
            } catch (_) {}
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      }
    } catch (_) {
      scheduleInteractiveError(getMsg(el, "calendar_unavailable"));
    }
  };
  const getData = () => {
    if (!window.jQuery || !$.ajax) {
      try {
        console.error("jQuery unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    const $calendar = $("#calendar");
    const $sel = $("#calendar_type");
    let calendar_type = $sel.find(":selected").val();
    $calendar.removeClass("local_calendar");
    $calendar.removeClass("google_calendar");
    if (calendar_type === undefined) {
      $calendar.addClass("local_calendar");
    }
    $calendar.addClass(calendar_type || "");
    const base = ($("#task_calendar").val() || "").toString().trim();
    const endpoint =
      base && base !== "#"
        ? base.replace(/\/$/, "") + "/calendar/get_task_data"
        : "";
    const urlAttr = $calendar.attr("data-url");
    const hrefAttr = $calendar.is("form")
      ? $calendar.attr("action") || ""
      : $calendar.attr("href") || "";
    if (
      (!endpoint || endpoint === "#") &&
      (!urlAttr || urlAttr === "#") &&
      (!hrefAttr || hrefAttr === "#")
    ) {
      scheduleInteractiveError(getMsg($calendar.get(0), "fetch_unavailable"));
      return;
    }
    const url = endpoint || urlAttr || hrefAttr;
    $.ajax({
      url: url,
      method: "POST",
      data: { _token: "{{ csrf_token() }}", calendar_type: calendar_type },
      cache: false,
      success: function (data) {
        initCalendar(data);
      },
      error: function () {
        scheduleInteractiveError(getMsg($calendar.get(0), "fetch_unavailable"));
      },
    });
  };
  const init = () => {
    if (document.readyState === "loading") {
      $(getData);
    } else {
      getData();
    }
  };
  init();
})();
