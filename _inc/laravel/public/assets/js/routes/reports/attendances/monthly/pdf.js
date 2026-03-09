/** @requires ERPGuard */
(function () {
  const { guard } = window.ERPBootstrap.require("ERPGuard");
  if (!guard) return;
  const $ = window.jQuery;
  const qs = (s, r = document) => r.querySelector(s);

  const dataEvtBranch = "data-branch-guard";
  const dataEvtDept = "data-dept-guard";

  const validRoute = url =>
    typeof url === "string" && url.trim() !== "" && url.trim() !== "#";

  const saveAsPDF = () => {
    const area = document.getElementById("printableArea");
    if (!area) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
      return;
    }
    const name =
      (($ && $("#filename").val()) ?? "").toString().trim() || "export";
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
        } catch (_) {}
        guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
        return;
      }
      window.html2pdf().set(opt).from(area).save();
    } catch (_) {
      guard.scheduleInteractiveError(guard.getMsg("pdf_unavailable"));
    }
  };

  window.saveAsPDF = saveAsPDF;

  const deptUrl = '{{route(VW::RPT . ".attendance.getdepartment")}}';
  const empUrl = '{{route(VW::RPT . ".attendance.getemployee")}}';

  const renderDepartmentSelect = data => {
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
      Object.keys(data).forEach(function (k) {
        const o = document.createElement("option");
        o.value = k;
        o.textContent = data[k];
        select.appendChild(o);
      });
    }
  };

  const renderEmployeeSelect = data => {
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
      Object.keys(data).forEach(function (k) {
        const o = document.createElement("option");
        o.value = k;
        o.textContent = data[k];
        select.appendChild(o);
      });
    }
    if (window.Choices) {
      try {
        new window.Choices("#employee_id", { removeItemButton: true });
      } catch (_) {
        guard.scheduleInteractiveError(guard.getMsg("plugin_unavailable"));
      }
    } else {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("Choices unavailable");
      } catch (_) {}
    }
  };

  const getDepartment = branchId => {
    if (!validRoute(deptUrl)) {
      guard.scheduleInteractiveError(guard.getMsg("endpoint_unavailable"));
      return;
    }
    $.ajax({
      url: deptUrl,
      type: "POST",
      data: { branch_id: branchId, _token: "{{ csrf_token() }}" },
      success: function (data) {
        try {
          renderDepartmentSelect(data);
        } catch (_) {
          guard.scheduleInteractiveError(
            guard.getMsg("department_unavailable")
          );
        }
      },
      error: function () {
        guard.scheduleInteractiveError(
          guard.getMsg("department_unavailable")
        );
      },
    });
  };

  const getEmployee = deptId => {
    if (!validRoute(empUrl)) {
      guard.scheduleInteractiveError(guard.getMsg("endpoint_unavailable"));
      return;
    }
    $.ajax({
      url: empUrl,
      type: "POST",
      data: { department_id: deptId, _token: "{{ csrf_token() }}" },
      success: function (data) {
        try {
          renderEmployeeSelect(data);
        } catch (_) {
          guard.scheduleInteractiveError(
            guard.getMsg("employee_unavailable")
          );
        }
      },
      error: function () {
        guard.scheduleInteractiveError(guard.getMsg("employee_unavailable"));
      },
    });
  };

  const bindWithObserver = (el, evt, handler, flag) => {
    if (!el || el.getAttribute(flag) === "true") {
      return;
    }
    el.setAttribute(flag, "true");
    $(el).on(evt, handler);
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(el)) {
        $(el).off(evt, handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const init = () => {
    const branch = document.querySelector('select[name="branch_id"]');
    if (branch) {
      bindWithObserver(
        branch,
        "change",
        function () {
          const v = $(this).val();
          getDepartment(v);
        },
        dataEvtBranch
      );
    }
    const dept = document.getElementById("department_id");
    if (dept) {
      bindWithObserver(
        dept,
        "change",
        function () {
          const v = $(this).val();
          getEmployee(v);
        },
        dataEvtDept
      );
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
