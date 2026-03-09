/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;

  const dataBound = "data-zoomcal-bound";
  const qs = (s, r = document) => r.querySelector(s);
  const getBase = () => {
    try {
      const v = $("#zoom_calendar").val();
      return (v ?? "").toString().trim();
    } catch (_) {
      const el = qs("#zoom_calendar");
      return (el?.value ?? "").toString().trim();
    }
  };
  const buildUrl = () => {
    const base = getBase();
    return base && base !== "#"
      ? `${base}/zoom-meeting/get_zoom_meeting_data`
      : "";
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
    guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
    return false;
  };
  const getCalendarType = () => {
    const sel = qs("#calendar_type");
    if (!sel) return "";
    const opt = sel.querySelector(":scope option:checked");
    return (opt?.value ?? "").toString().trim();
  };
  const applyCalendarClass = type => {
    const cal = qs("#calendar");
    if (!cal) return;
    cal.classList.remove("local_calendar");
    cal.classList.remove("google_calendar");
    if (!type) cal.classList.add("local_calendar");
    cal.classList.add(type);
  };
  const renderCalendar = events => {
    if (!ensureFC()) return;
    const el = qs("#calendar");
    if (!el) return;
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
          right: "timeGridDay,timeGridWeek,dayGridMonth",
        },
        buttonText: {
          timeGridDay: "{{__('Day')}}",
          timeGridWeek: "{{__('Week')}}",
          dayGridMonth: "{{__('Month')}}",
        },
        slotLabelFormat: { hour: "2-digit", minute: "2-digit", hour12: false },
        themeSystem: "bootstrap",
        allDaySlot: false,
        navLinks: true,
        droppable: true,
        selectable: true,
        selectMirror: true,
        editable: true,
        dayMaxEvents: true,
        handleWindowResize: true,
        height: "auto",
        timeFormat: "H(:mm)",
        events: events ?? [],
      });
      calendar.render();
      el._fcInstance = calendar;
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("calendar_unavailable"));
    }
  };
  const getData = () => {
    if (!(window.jQuery && window.jQuery.fn)) return;
    const url = buildUrl();
    if (!url) {
      guard.scheduleInteractiveError(guard.getMsg("calendar_unavailable"));
      return;
    }
    const type = getCalendarType();
    applyCalendarClass(type);
    $.ajax({
      url: url,
      method: "POST",
      data: { _token: guard.getCsrfToken(), calendar_type: type },
      success: function (data) {
        try {
          renderCalendar(data);
        } catch (_) {
          guard.scheduleInteractiveError(guard.getMsg("calendar_unavailable"));
        }
      },
      error: function () {
        guard.scheduleInteractiveError(guard.getMsg("calendar_unavailable"));
      },
    });
  };
  const bind = () => {
    if (document.body.getAttribute(dataBound) === "true") return;
    document.body.setAttribute(dataBound, "true");
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
})();
