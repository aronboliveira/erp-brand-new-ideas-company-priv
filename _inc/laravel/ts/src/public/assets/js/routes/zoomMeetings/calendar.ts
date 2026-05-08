/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/calendar.js
 * @generated from original JavaScript - manual review recommended
 * @module calendar
 */

import type {
  FullCalendarStatic,
  CalendarHTMLElement,
} from "../../../../../declarations/routes/fullcalendar.interfaces";

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const errFb = "# ERROR",
    dataClientLocalized = "data-client-localized",
    dataGuardMsg = "data-guard-msg",
    dataSvLocalized = "data-sv-localized",
    dataErrGuard = "data-zoomcal-error",
    dataBound = "data-zoomcal-bound";
  const qs = <T extends Element = Element>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector<T>(s);
  const hasBootstrapUi = (): boolean =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ??
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap.Toast;
  const ensureToastContainer = (): HTMLElement => {
    const c = qs<HTMLElement>("#np-toast-container");
    if (c) return c;
    const newC = document.createElement("div");
    newC.id = "np-toast-container";
    newC.setAttribute("aria-live", "polite");
    newC.setAttribute("aria-atomic", "true");
    Object.assign(newC.style, {
      position: "fixed",
      top: "1rem",
      right: "1rem",
    });
    document.body.appendChild(newC);
    return newC;
  };
  const showError = (message: string): void => {
    if (hasBootstrapUi()) {
      const container = ensureToastContainer();
      let t = qs<HTMLElement>("#np-toast", container);
      if (!t) {
        const newT = document.createElement("div");
        newT.id = "np-toast";
        newT.className = "toast";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          newT.setAttribute(k, v);
        newT.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(newT);
        t = newT;
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
  const schedulePointerupError = (msg: string): void => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showError(msg);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((_m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const csrf = (): string => {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m?.getAttribute("content") ?? "";
  };
  const getBase = (): string => {
    try {
      return String($("#zoom_calendar").val() ?? "").trim();
    } catch (_) {
      const el = qs<HTMLInputElement>("#zoom_calendar");
      return String(el?.value ?? "").trim();
    }
  };
  const buildUrl = (): string => {
    const base = getBase();
    return base && base !== "#"
      ? `${base}/zoom-meeting/get_zoom_meeting_data`
      : "";
  };
  const ensureJq = (): boolean => {
    if (!$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {
        console.error(`[calendar] Error:`, _);
      }
      schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
      return false;
    }
    return true;
  };
  const ensureFC = (): boolean => {
    if ((window.FullCalendar as FullCalendarStatic | undefined)?.Calendar)
      return true;
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("FullCalendar unavailable");
    } catch (_) {
      console.error(`[calendar] Error:`, _);
    }
    schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
    return false;
  };
  const getCalendarType = (): string => {
    const sel = qs<HTMLSelectElement>("#calendar_type");
    if (!sel) return "";
    const opt = sel.querySelector<HTMLOptionElement>(":scope option:checked");
    return String(opt?.value ?? "").trim();
  };
  const applyCalendarClass = (type: string): void => {
    const cal = qs("#calendar");
    if (!cal) return;
    cal.classList.remove("local_calendar");
    cal.classList.remove("google_calendar");
    if (!type) cal.classList.add("local_calendar");
    else cal.classList.add(type);
  };
  const renderCalendar = (events: unknown): void => {
    if (!ensureFC()) return;
    const el = qs<CalendarHTMLElement>("#calendar");
    if (!el) return;
    if (el._fcInstance) {
      try {
        el._fcInstance.destroy();
      } catch (_) {
        console.error(`[calendar] Error:`, _);
      }
      el._fcInstance = null;
    }
    try {
      const FC = window.FullCalendar as FullCalendarStatic,
        calendar = new FC.Calendar(el, {
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
          slotLabelFormat: {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
          },
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
      schedulePointerupError(getMsg(el, "calendar_unavailable"));
    }
  };
  const getData = (): void => {
    if (!ensureJq()) return;
    const url = buildUrl();
    if (!url) {
      schedulePointerupError(getMsg(document.body, "calendar_unavailable"));
      return;
    }
    const type = getCalendarType();
    applyCalendarClass(type);
    $.ajax({
      url: url,
      method: "POST",
      data: { _token: csrf(), calendar_type: type },
      success: function (data: unknown) {
        try {
          renderCalendar(data);
        } catch (_) {
          schedulePointerupError(getMsg(document.body, "calendar_unavailable"));
        }
      },
      error: function (): void {
        schedulePointerupError(getMsg(document.body, "calendar_unavailable"));
      },
    });
  };
  const bind = (): void => {
    if (document.body.getAttribute(dataBound) === "true") return;
    document.body.setAttribute(dataBound, "true");
    getData();
    const mo = new MutationObserver((_m, o) => {
      if (!qs("#calendar")) o.disconnect();
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", bind, { once: true })
    : bind();
  window.get_data = getData;
  document.getElementById("calendar_type")?.addEventListener("change", getData);
})();

export {};
