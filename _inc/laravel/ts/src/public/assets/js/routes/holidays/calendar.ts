/**
 * @fileoverview TypeScript version of public/assets/js/routes/holidays/calendar.js
 * @generated from original JavaScript - manual review recommended
 * @module calendar
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
// assets/js/routes/holidays/calendar.js — Calendar-type switching for Holiday calendar
(function (): void {
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
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = (): void => {
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
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
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
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
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
  const csrf = (): void => {
    const m = qs('meta[name="csrf-token"]');
    return m?.getAttribute("content") ?? "";
  };
  const getBase = (): void => {
    try {
      const v = $("#holiday_calendar").val();
      return (v ?? "").toString().trim();
    } catch (_) {
      const el = qs("#holiday_calendar");
      return (el?.value ?? "").toString().trim();
    }
  };
  const buildUrl = (): void => {
    const base = getBase();
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    return base && base !== "#"
      ? `${base.replace(/\/$/, "")}/holidays/data`
      : "";
  };
  const ensureJq = (): void => {
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
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
  const ensureFC = (): void => {
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
  const getCalendarType = (): void => {
    const sel = qs("#calendar_type");
    if (!sel) return "local_calendar";
    const opt = sel.querySelector(":scope option:checked");
    return (opt?.value ?? "local_calendar").toString().trim();
  };
  const applyCalendarClass = type => {
    const cal = qs("#calendar");
    if (!cal) return;
    cal.classList.remove("local_calendar", "google_calendar");
    cal.classList.add(type ?? "local_calendar");
  };
  const renderCalendar = events => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
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
  const getData = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!ensureJq()) return;
    const url = buildUrl();
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
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
      error: function (): void {
        schedulePointerupError(
          getMsg(document.body, "holiday_fetch_unavailable")
        );
      },
    });
  };
  const bind = (): void => {
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
