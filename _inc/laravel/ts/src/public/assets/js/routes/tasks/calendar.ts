/**
 * @fileoverview TypeScript version of public/assets/js/routes/tasks/calendar.js
 * @generated from original JavaScript - manual review recommended
 * @module calendar
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataCalGuard = "data-cal-guard";
  const ensureToastContainer = (): void => {
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
      (document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') ??
        document.querySelector('link[href*="bootstrap"]')) &&
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain, @typescript-eslint/strict-boolean-expressions
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
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
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
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
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
      if (!window.FullCalendar?.Calendar) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
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
  const getData = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!window.jQuery || !$.ajax) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
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
    $calendar.addClass(calendar_type ?? "");
    const base = ($("#task_calendar").val() ?? "").toString().trim();
    const endpoint =
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      base && base !== "#"
        ? base.replace(/\/$/, "") + "/calendar/get_task_data"
        : "";
    const urlAttr = $calendar.attr("data-url");
    const hrefAttr = $calendar.is("form")
      ? $calendar.attr("action") ?? ""
      : $calendar.attr("href") ?? "";
    if (
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      (!endpoint || endpoint === "#") &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      (!urlAttr || urlAttr === "#") &&
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      (!hrefAttr || hrefAttr === "#")
    ) {
      scheduleInteractiveError(getMsg($calendar.get(0), "fetch_unavailable"));
      return;
    }
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    const url = endpoint || urlAttr ?? hrefAttr;
    $.ajax({
      url: url,
      method: "POST",
      data: { _token: "{{ csrf_token() }}", calendar_type: calendar_type },
      cache: false,
      success: function (data) {
        initCalendar(data);
      },
      error: function (): void {
        scheduleInteractiveError(getMsg($calendar.get(0), "fetch_unavailable"));
      },
    });
  };
  const init = (): void => {
    if (document.readyState === "loading") {
      $(getData);
    } else {
      getData();
    }
  };
  init();
  window.get_data = getData;
  document.getElementById("calendar_type")?.addEventListener("change", getData);
})();

export {};
