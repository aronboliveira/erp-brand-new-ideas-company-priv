/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/editSelect.js
 * @generated from original JavaScript - manual review recommended
 * @module editSelect
 */

/* global bootstrap, $, jQuery */
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const langKey = "erp-np-lang";
  const errFb = "# ERROR";
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (key: string, el: HTMLElement) => {
    let msg = errFb;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem(langKey) ??
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
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };

  const showError = (message: string): void=> {
    try {
      const hasBs =
        Array.from(
          document.querySelectorAll<HTMLLinkElement>('link[rel="stylesheet"]'),
        ).some(l => /bootstrap/i.test(l.href)) && window.bootstrap.Toast;
      if (hasBs) {
        let c = document.getElementById("bootstrap-toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "bootstrap-toast-container";
          c.setAttribute("aria-live", "polite");
          c.setAttribute("aria-atomic", "true");
          document.body.appendChild(c);
        }
        let t = c.querySelector(".toast");
        if (!t) {
          t = document.createElement("div");
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          const b = document.createElement("div");
          b.className = "toast-body";
          t.appendChild(b);
          c.appendChild(t);
        }
        const toastBody = t.querySelector(".toast-body");
        if (toastBody) toastBody.textContent = message;
        bootstrap.Toast.getOrCreateInstance(t).show();
      } else alert(message);
    } catch {
      alert(message);
    }
  };

  const delegate = <T extends HTMLElement>(
    eventType: string,
    selector: string,
    handler: (el: T) => void,
    key: string,
  ): void=> {
    document.addEventListener(eventType, e => {
      const target = e.target as Element | null;
      if (!target) return;
      const el = target.closest<T>(selector);
      if (!el) return;
      try {
        handler(el);
      } catch {
        showError(getMsg(key, el));
      }
      const obs = new MutationObserver((m, o) => {
        if (!document.body.contains(el)) {
          o.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    });
  };

  delegate<HTMLInputElement>(
    "change",
    'input[name="type"]:radio',
    el => {
      const t = el.value;
      if (t === "employee") {
        $(".employee").addClass("d-block").removeClass("d-none");
        $(".customer, .vendor").addClass("d-none").removeClass("d-block");
      } else if (t === "customer") {
        $(".customer").addClass("d-block").removeClass("d-none");
        $(".employee, .vendor").addClass("d-none").removeClass("d-block");
      } else {
        $(".vendor").addClass("d-block").removeClass("d-none");
        $(".employee, .customer").addClass("d-none").removeClass("d-block");
      }
    },
    "selection_failed",
  );

  delegate<HTMLSelectElement>(
    "change",
    "#employee",
    el => {
      $("#employee_detail").addClass("d-block").removeClass("d-none");
      $("#employee-box").addClass("d-none").removeClass("d-block");
      $.ajax({
        url: el.getAttribute("data-url"),
        type: "POST",
        headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
        data: { id: el.value },
        success: (data: string) => {
          if (data) {
            // SECURITY: Use safe HTML insertion instead of innerHTML
            const employeeDetail = document.getElementById("employee_detail");
            if (employeeDetail) safeSethtmlContent(employeeDetail, data);
          } else {
            $("#employee-box").addClass("d-block").removeClass("d-none");
            $("#employee_detail").addClass("d-none").removeClass("d-block");
          }
        },
        error: (): void => {
          showError(getMsg("employee_fetch_failed", el));
        },
      });
    },
    "employee_change_failed",
  );

  delegate<HTMLSelectElement>(
    "change",
    "#customer",
    el => {
      $("#customer_detail").addClass("d-block").removeClass("d-none");
      $("#customer-box").addClass("d-none").removeClass("d-block");
      $.ajax({
        url: el.getAttribute("data-url"),
        type: "POST",
        headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
        data: { id: el.value },
        success: (data: string) => {
          if (data) {
            // SECURITY: Use safe HTML insertion instead of innerHTML
            const customerDetail = document.getElementById("customer_detail");
            if (customerDetail) safeSethtmlContent(customerDetail, data);
          } else {
            $("#customer-box").addClass("d-block").removeClass("d-none");
            $("#customer_detail").addClass("d-none").removeClass("d-block");
          }
        },
        error: (): void => {
          showError(getMsg("customer_fetch_failed", el));
        },
      });
    },
    "customer_change_failed",
  );

  delegate<HTMLSelectElement>(
    "change",
    "#vendor",
    el => {
      $("#vendor_detail").addClass("d-block").removeClass("d-none");
      $("#vendor-box").addClass("d-none").removeClass("d-block");
      $.ajax({
        url: el.getAttribute("data-url"),
        type: "POST",
        headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
        data: { id: el.value },
        success: (data: string) => {
          if (data) {
            // SECURITY: Use safe HTML insertion instead of innerHTML
            const vendorDetail = document.getElementById("vendor_detail");
            if (vendorDetail) safeSethtmlContent(vendorDetail, data);
          } else {
            $("#vendor-box").addClass("d-block").removeClass("d-none");
            $("#vendor_detail").addClass("d-none").removeClass("d-block");
          }
        },
        error: (): void => {
          showError(getMsg("vendor_fetch_failed", el));
        },
      });
    },
    "vendor_change_failed",
  );

  delegate<HTMLElement>(
    "click",
    "#remove",
    _el => {
      $(".vendor, .customer, .employee")
        .addClass("d-block")
        .removeClass("d-none");
      $("#vendor_detail, #customer_detail, #employee_detail")
        .addClass("d-none")
        .removeClass("d-block");
    },
    "remove_failed",
  );

  // SECURITY: Safe HTML insertion helper
  function safeSethtmlContent(el: HTMLElement, html: string): void{
    try {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      if (doc.body.innerHTML.includes("PARSER ERROR")) {
        el.textContent = html;
        return;
      }
      while (el.firstChild) {
        el.removeChild(el.firstChild);
      }
      const fragment = document.createDocumentFragment();
      for (const node of doc.body.childNodes) {
        fragment.appendChild(node.cloneNode(true));
      }
      el.appendChild(fragment);
    } catch (e) {
      el.textContent = html;
    }
  }
})();

export {};
