/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;

  const dataCalGuard = "data-cal-guard";
  const initCalendar = events => {
    const el = document.getElementById("calendar");
    if (!el) {
      guard.scheduleInteractiveError(guard.getMsg("calendar_unavailable"));
      return;
    }
    try {
      if (!window.FullCalendar || !window.FullCalendar.Calendar) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("FullCalendar unavailable");
        } catch (_) {}
        guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
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
      guard.scheduleInteractiveError(guard.getMsg("calendar_unavailable"));
    }
  };
  const getData = () => {
    if (!window.jQuery || !$.ajax) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
      return;
    }
    const $calendar = $("#calendar");
    const $sel = $("#calendar_type");
    const calendar_type = $sel.find(":selected").val();
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
      guard.scheduleInteractiveError(guard.getMsg($calendar.get(0), "fetch_unavailable"));
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
        guard.scheduleInteractiveError(guard.getMsg($calendar.get(0), "fetch_unavailable"));
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
