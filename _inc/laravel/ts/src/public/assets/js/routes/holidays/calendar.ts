/**
 * @fileoverview TypeScript version of public/assets/js/routes/holidays/calendar.js
 * @generated from original JavaScript - manual review recommended
 * @module calendar
 */

import type {
  FullCalendarInstance,
  FullCalendarStatic,
  CalendarHTMLElement,
} from "../../../../../declarations/routes/fullcalendar.interfaces";

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// assets/js/routes/holidays/calendar.js — Calendar-type switching for Holiday calendar
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery!;
  const errFb = "# ERROR",
    dataClientLocalized = "data-client-localized",
    dataGuardMsg = "data-guard-msg",
    dataSvLocalized = "data-sv-localized",
    dataErrGuard = "data-holcal-error",
    dataCalGuard = "data-holcal-bound";
  const qs = <T extends Element = Element>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector<T>(s);
  const hasBootstrapUi = (): boolean =>
    !!(
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      (
        qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')
      )
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
      msg =
        window.translations?.[lang]?.[key] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const csrf = (): string => {
    const m = qs('meta[name="csrf-token"]');
    return m?.getAttribute("content") ?? "";
  };
  const getBase = (): string => {
    try {
      return String($("#holiday_calendar").val() ?? "").trim();
    } catch (_) {
      const el = qs<HTMLInputElement>("#holiday_calendar");
      return (el?.value ?? "").toString().trim();
    }
  };
  const buildUrl = (): string => {
    const base = getBase();
    return base && base !== "#"
      ? `${base.replace(/\/$/, "")}/holidays/data`
      : "";
  };
  const ensureJq = (): boolean => {
    if (typeof $.ajax === "function") return true;
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
    schedulePointerupError(
      getMsg(document.body, "calendar_initialization_failed"),
    );
    return false;
  };
  const getCalendarType = (): string => {
    const sel = qs<HTMLSelectElement>("#calendar_type");
    if (!sel) return "local_calendar";
    const opt = sel.querySelector<HTMLOptionElement>(":scope option:checked");
    return (opt?.value ?? "local_calendar").toString().trim();
  };
  const applyCalendarClass = (type: unknown): void => {
    const cal = qs("#calendar");
    if (!cal) return;
    cal.classList.remove("local_calendar", "google_calendar");
    cal.classList.add(String(type ?? "local_calendar"));
  };
  const renderCalendar = (events: unknown): void => {
    if (!ensureFC()) return;
    const el = qs<CalendarHTMLElement>("#calendar");
    if (!el) {
      schedulePointerupError(
        getMsg(document.body, "calendar_element_unavailable"),
      );
      return;
    }
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
        const mo = new MutationObserver((_m, o) => {
          if (!document.body.contains(el)) {
            try {
              calendar.destroy();
            } catch (_) {
              console.error(`[calendar] Error:`, _);
            }
            o.disconnect();
          }
        });
        mo.observe(document.body, { childList: true, subtree: true });
      }
    } catch (_) {
      schedulePointerupError(
        getMsg(el ?? document.body, "calendar_initialization_failed"),
      );
    }
  };
  const getData = (): void => {
    if (!ensureJq()) return;
    const url = buildUrl();
    if (!url) {
      schedulePointerupError(
        getMsg(document.body, "holiday_fetch_unavailable"),
      );
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
          schedulePointerupError(
            getMsg(document.body, "calendar_initialization_failed"),
          );
        }
      },
      error: function (): void {
        schedulePointerupError(
          getMsg(document.body, "holiday_fetch_unavailable"),
        );
      },
    });
  };
  const bind = (): void => {
    if (document.body.getAttribute(dataCalGuard) === "true") return;
    document.body.setAttribute(dataCalGuard, "true");
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
