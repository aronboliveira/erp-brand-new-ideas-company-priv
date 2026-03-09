// assets/js/routes/holidays/calendar.js — Calendar-type switching for Holiday calendar
(function () {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-holcal-error";
  const dataCalGuard = "data-holcal-bound";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBootstrapUi = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = () => {
    let c = qs("#np-toast-container");
    if (c) return c;
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
  const showError = message => {
    if (hasBootstrapUi()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
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
      if (body) body.textContent = message ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const schedulePointerupError = msg => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = () => {
      try {
        showError(msg);
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
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const csrf = () => {
    const m = qs('meta[name="csrf-token"]');
    return m?.getAttribute("content") || "";
  };
  const getBase = () => {
    try {
      const v = $("#holiday_calendar").val();
      return (v ?? "").toString().trim();
    } catch (_) {
      const el = qs("#holiday_calendar");
      return (el?.value ?? "").toString().trim();
    }
  };
  const buildUrl = () => {
    const base = getBase();
    return base && base !== "#"
      ? `${base.replace(/\/$/, "")}/holidays/data`
      : "";
  };
  const ensureJq = () => {
    if ($ && $.ajax) return true;
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery unavailable");
    } catch (_) {}
    schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
    return false;
  };
  const ensureFC = () => {
    if (window.FullCalendar?.Calendar) return true;
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("FullCalendar unavailable");
    } catch (_) {}
    schedulePointerupError(
      getMsg(document.body, "calendar_initialization_failed")
    );
    return false;
  };
  const getCalendarType = () => {
    const sel = qs("#calendar_type");
    if (!sel) return "local_calendar";
    const opt = sel.querySelector(":scope option:checked");
    return (opt?.value ?? "local_calendar").toString().trim();
  };
  const applyCalendarClass = type => {
    const cal = qs("#calendar");
    if (!cal) return;
    cal.classList.remove("local_calendar", "google_calendar");
    cal.classList.add(type || "local_calendar");
  };
  const renderCalendar = events => {
    if (!ensureFC()) return;
    const el = qs("#calendar");
    if (!el) {
      schedulePointerupError(
        getMsg(document.body, "calendar_element_unavailable")
      );
      return;
    }
    if (el._fcInstance) {
      try {
        el._fcInstance.destroy();
      } catch (_) {}
      el._fcInstance = null;
    }
    try {
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
        editable: false,
        dayMaxEvents: true,
        handleWindowResize: true,
        events: events ?? [],
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
      schedulePointerupError(
        getMsg(el, "calendar_initialization_failed")
      );
    }
  };
  const getData = () => {
    if (!ensureJq()) return;
    const url = buildUrl();
    if (!url) {
      schedulePointerupError(
        getMsg(document.body, "holiday_fetch_unavailable")
      );
      return;
    }
    const type = getCalendarType();
    applyCalendarClass(type);
    $.ajax({
      url: url,
      method: "POST",
      data: { _token: csrf(), calendar_type: type },
      success: function (data) {
        try {
          renderCalendar(data);
        } catch (_) {
          schedulePointerupError(
            getMsg(document.body, "calendar_initialization_failed")
          );
        }
      },
      error: function () {
        schedulePointerupError(
          getMsg(document.body, "holiday_fetch_unavailable")
        );
      },
    });
  };
  const bind = () => {
    if (document.body.getAttribute(dataCalGuard) === "true") return;
    document.body.setAttribute(dataCalGuard, "true");
    getData();
    const mo = new MutationObserver((m, o) => {
      if (!qs("#calendar")) {
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  } else {
    bind();
  }
  window.get_data = getData;
  document.getElementById("calendar_type")?.addEventListener("change", getData);
})();
