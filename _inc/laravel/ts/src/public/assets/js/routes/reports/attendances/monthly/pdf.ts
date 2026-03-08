/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/attendances/monthly/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  const $ = window.jQuery;
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const qs = (s: string, r: Document | HTMLElement = document) =>
    r.querySelector(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataEvtBranch = "data-branch-guard";
  const dataEvtDept = "data-dept-guard";

  const ensureToastContainer = (): HTMLElement => {
    const id = "np-toast-container";
    const existing = qs("#" + id);
    if (existing instanceof HTMLElement) {
      return existing;
    }
    const c = document.createElement("div");
    c.id = id;
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };

  const showErrorNow = (message: string): void=> {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container) as HTMLElement | null;
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  t.setAttribute(k, v);
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
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

  const scheduleInteractiveError = (message: string): void=> {
    const host = document.body;
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
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
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
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const validRoute = (url: unknown): url is string =>
    typeof url === "string" && url.trim() !== "" && url.trim() !== "#";

  const saveAsPDF = (): void => {
    const area = document.getElementById("printableArea");
    if (!area) {
      scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
      return;
    }
    const name = String($?.("#filename").val() ?? "").trim();
    const opt = {
      margin: 0.3,
      filename: name,
      image: { type: "jpeg", quality: 1 },
      html2canvas: { scale: 4, dpi: 72, letterRendering: true },
      jsPDF: { unit: "in", format: "A2" },
    };
    try {
      if (typeof window.html2pdf !== "function") {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("html2pdf unavailable");
        } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
        scheduleInteractiveError(getMsg(area, "plugin_unavailable"));
        return;
      }
      // eslint-disable-next-line @typescript-eslint/no-unsafe-call
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-call
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      scheduleInteractiveError(getMsg(area, "pdf_unavailable"));
    }
  };

  window.saveAsPDF = saveAsPDF;

  const deptUrl = '{{route(VW::RPT . ".attendance.getdepartment")}}';
  const empUrl = '{{route(VW::RPT . ".attendance.getemployee")}}';

  const renderDepartmentSelect = (data: Record<string, unknown>): void=> {
    const wrap = document.getElementById("department_div");
    if (!wrap) {
      return;
    }
    const hasLabel = wrap.querySelector('label[for="department"]');
    const hasSelect = document.getElementById("department_id");
    if (!hasLabel) {
      const lab = document.createElement("label");
      lab.setAttribute("for", "department");
      lab.className = "form-label";
      lab.textContent = '{{__("Department")}}';
      wrap.appendChild(lab);
    }
    if (!hasSelect) {
      const sel = document.createElement("select");
      sel.className = "form-control";
      sel.id = "department_id";
      sel.name = "department_id[]";
      wrap.appendChild(sel);
    }
    const select = document.getElementById("department_id");
    if (!select) {
      return;
    }
    select.innerHTML = "";
    const opt0 = document.createElement("option");
    opt0.value = "";
    opt0.textContent = '{{__("Select Department")}}';
    select.appendChild(opt0);
    const optAll = document.createElement("option");
    optAll.value = "0";
    optAll.textContent = '{{__("All Department")}}';
    select.appendChild(optAll);
    if (data && typeof data === "object") {
      Object.keys(data).forEach(function (k: string) {
        const o = document.createElement("option");
        o.value = k;
        o.textContent = String(data[k] ?? "");
        select.appendChild(o);
      });
    }
  };

  const renderEmployeeSelect = (data: Record<string, unknown>): void=> {
    const wrap = document.getElementById("employee_div");
    if (!wrap) {
      return;
    }
    const hasLabel = wrap.querySelector('label[for="employee"]');
    const hasSelect = document.getElementById("employee_id");
    if (!hasLabel) {
      const lab = document.createElement("label");
      lab.setAttribute("for", "employee");
      lab.className = "form-label";
      lab.textContent = '{{__("Employee")}}';
      wrap.appendChild(lab);
    }
    if (!hasSelect) {
      const sel = document.createElement("select");
      sel.className = "form-control";
      sel.id = "employee_id";
      sel.name = "employee_id[]";
      sel.multiple = true;
      wrap.appendChild(sel);
    }
    const select = document.getElementById("employee_id");
    if (!select) {
      return;
    }
    select.innerHTML = "";
    const opt0 = document.createElement("option");
    opt0.value = "";
    opt0.textContent = '{{__("Select Employee")}}';
    select.appendChild(opt0);
    const optAll = document.createElement("option");
    optAll.value = "0";
    optAll.textContent = '{{__("All Employee")}}';
    select.appendChild(optAll);
    if (data && typeof data === "object") {
      Object.keys(data).forEach(function (k: string) {
        const o = document.createElement("option");
        o.value = k;
        o.textContent = String(data[k] ?? "");
        select.appendChild(o);
      });
    }
    if (window.Choices) {
      try {
        new window.Choices("#employee_id", { removeItemButton: true });
      } catch (_) {
        scheduleInteractiveError(getMsg(select, "plugin_unavailable"));
      }
    } else {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Choices unavailable");
      } catch (_) {
    console.error(`[pdf] Error:`, _);
  }
    }
  };

  const getDepartment = (branchId: string): void=> {
    if (!validRoute(deptUrl)) {
      scheduleInteractiveError(getMsg(document.body, "endpoint_unavailable"));
      return;
    }
    $?.ajax({
      url: deptUrl,
      type: "POST",
      data: { branch_id: branchId, _token: "{{ csrf_token() }}" },
      success: function (data: unknown) {
        try {
          renderDepartmentSelect(data as Record<string, unknown>);
        } catch (_) {
          scheduleInteractiveError(
            getMsg(document.body, "department_unavailable"),
          );
        }
      },
      error: function (): void {
        scheduleInteractiveError(
          getMsg(document.body, "department_unavailable"),
        );
      },
    });
  };

  const getEmployee = (deptId: string): void=> {
    if (!validRoute(empUrl)) {
      scheduleInteractiveError(getMsg(document.body, "endpoint_unavailable"));
      return;
    }
    $?.ajax({
      url: empUrl,
      type: "POST",
      data: { department_id: deptId, _token: "{{ csrf_token() }}" },
      success: function (data: unknown) {
        try {
          renderEmployeeSelect(data as Record<string, unknown>);
        } catch (_) {
          scheduleInteractiveError(
            getMsg(document.body, "employee_unavailable"),
          );
        }
      },
      error: function (): void {
        scheduleInteractiveError(getMsg(document.body, "employee_unavailable"));
      },
    });
  };

  const bindWithObserver = (
    el: HTMLElement,
    evt: string,
    handler: (this: HTMLElement, e: Event) => void,
    flag: string,
  ): void=> {
    if (!el || el.getAttribute(flag) === "true") {
      return;
    }
    el.setAttribute(flag, "true");
    if ($) $(el).on(evt, handler as unknown as (e: Event) => void);
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(el)) {
        if ($) $(el).off(evt, handler as unknown as (e: Event) => void);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const init = (): void => {
    const branch = document.querySelector('select[name="branch_id"]');
    if (branch instanceof HTMLElement) {
      bindWithObserver(
        branch,
        "change",
        function (this: HTMLElement): void {
          const v = $ ? String($(this).val() ?? "") : "";
          getDepartment(v);
        },
        dataEvtBranch,
      );
    }
    const dept = document.getElementById("department_id");
    if (dept) {
      bindWithObserver(
        dept,
        "change",
        function (this: HTMLElement): void {
          const v = $ ? String($(this).val() ?? "") : "";
          getEmployee(v);
        },
        dataEvtDept,
      );
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
