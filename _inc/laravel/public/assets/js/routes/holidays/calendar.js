// assets/js/routes/holidays/index.js
(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    return;
  }

  try {
    document.querySelectorAll("a[data-guard-msg], a[data-url]").forEach(el => {
      if (!el || el.getAttribute("data-listener-active") === "true") return;
      el.setAttribute("data-listener-active", "true");
      el.addEventListener("click", e => {
        try {
          const href = (el.getAttribute("href") ?? "#").trim();
          const url = (el.getAttribute("data-url") ?? href ?? "#").trim();
          if (url !== "#" && href !== "#") return;
          e.preventDefault();
          const msg = el.getAttribute("data-guard-msg") || getMsg("route_unavailable");
          scheduleError(msg, "click");
          el.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    });

    document.querySelectorAll("form[data-guard-msg], form[data-url]").forEach(fm => {
      if (!fm || fm.getAttribute("data-submit-guarded") === "true") return;
      fm.setAttribute("data-submit-guarded", "true");
      fm.addEventListener("submit", e => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim();
          const url = (fm.getAttribute("data-url") ?? action ?? "#").trim();
          if (url !== "#" && action !== "#") return;
          e.preventDefault();
          const msg = fm.getAttribute("data-guard-msg") || getMsg("route_unavailable");
          scheduleError(msg, "click");
          fm.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    });

    const renderCalendar = events => {
      try {
        if (!window.FullCalendar?.Calendar) throw 0;
        const el = document.getElementById("calendar");
        if (!el) throw 0;
        const calendar = new FullCalendar.Calendar(el, {
          headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay",
          },
          buttonText: {
            timeGridDay: "Day",
            timeGridWeek: "Week",
            dayGridMonth: "Month",
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
          events: Array.isArray(events) ? events : [],
        });
        calendar.render();
      } catch (_) {
        scheduleError(getMsg("calendar_data_unavailable"), "click");
      }
    };

    const getData = () => {
      try {
        const base = document.getElementById("holiday_calendar")?.value || "";
        if (!base) throw 0;
        const sel = document.getElementById("calendar_type");
        const ct = sel ? sel.value : "local_calendar";
        const cal = document.getElementById("calendar");
        if (cal) {
          cal.classList.remove("local_calendar", "google_calendar");
          cal.classList.add(ct);
        }
        const xhr = new XMLHttpRequest();
        xhr.open("POST", `${base}/holiday/get_holiday_data`);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
        xhr.setRequestHeader("X-CSRF-TOKEN", token);
        xhr.onreadystatechange = () => {
          try {
            if (xhr.readyState !== 4) return;
            if (xhr.status >= 200 && xhr.status < 300) {
              let data;
              try {
                data = JSON.parse(xhr.responseText);
              } catch (_) {
                data = [];
              }
              renderCalendar(data);
            } else {
              scheduleError(getMsg("calendar_data_unavailable"), "click");
            }
          } catch (_) {}
        };
        xhr.send(JSON.stringify({ calendar_type: ct }));
      } catch (_) {
        scheduleError(getMsg("calendar_data_unavailable"), "click");
      }
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", getData, { once: true });
    } else {
      getData();
    }

    window.get_data = getData;

    // CSP-safe replacement for inline onchange="get_data()"
    const calTypeSelect = document.getElementById("calendar_type");
    if (calTypeSelect) {
      calTypeSelect.addEventListener("change", getData);
    }

    try {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
          bootstrap.Tooltip.getOrCreateInstance(el);
        } catch (_) {}
      });
    } catch (_) {}
  } catch (_) {}
})();
