/**
 * @fileoverview TypeScript version of public/assets/js/routes/budgets/toggleCreate.js
 * @generated from original JavaScript - manual review recommended
 * @module toggleCreate
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-base-to-string, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars, @typescript-eslint/restrict-template-expressions */

/* global bootstrap, $, jQuery */
((): void => {
  const errFb = "# ERROR";
  const guardMsg = "data-guard-msg";
  const clientFlag = "data-client-localized";
  const langKey = "erp-np-lang";
  let errorMessage = "";

  function getLocalizedMessage(key, el) {
    let msg = errFb;
    if (el.getAttribute(clientFlag) === "true") {
      msg = el.getAttribute(guardMsg) || msg;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        sessionStorage.getItem(langKey) ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        translations?.[lang]?.[key] ||
        el.getAttribute(guardMsg) ||
        translations?.en?.[key] ||
        msg;
      if (msg !== errFb) {
        el.setAttribute(guardMsg, msg);
        el.setAttribute(clientFlag, "true");
      }
    }
    return msg;
  }

  function showError(message) {
    try {
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      const bs =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (bs) {
        const toast = document.createElement("div");
        toast.className = "toast";
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = message;
        toast.appendChild(body);
        container.appendChild(toast);
        bootstrap.Toast.getOrCreateInstance(toast).show();
      } else {
        alert(message);
      }
    } catch {
      alert(message);
    }
  }

  const onErrorPointerUp = (): void => {
    if (errorMessage !== "") {
      showError(errorMessage);
      errorMessage = "";
    }
  };
  document.addEventListener("pointerup", onErrorPointerUp);
  new MutationObserver((ms, obs) => {
    ms.forEach(m =>
      { Array.from(m.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      }); }
    );
  }).observe(document.body, { childList: true, subtree: true });

  $((): void => {
    const bindHandler = (selector, event, handler, errorKey) => {
      $(document).on(event, selector, function (): void {
        try {
          handler.call(this);
        } catch {
          errorMessage = getLocalizedMessage(errorKey, this);
        }
      });
    };

    bindHandler(
      ".income_data",
      "keyup",
      function (): void {
        const $row = $(this).closest("tr");
        let cat = 0;
        $row
          .find(".income_data")
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          .each((_, i) => (cat += parseFloat($(i).val()) || 0));
        $row.find(".totalIncome").text(cat);
        const m = $(this).data("month") ?? "";
        let mt = 0;
        $row
          .parent()
          .find(`.${m}_income`)
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          .each((_, i) => (mt += parseFloat($(i).val()) || 0));
        $row.parent().find(`.${m}_total_income`).text(mt);
        let grand = 0;
        $row
          .parent()
          .find(".totalIncome")
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          .each((_, i) => (grand += parseFloat($(i).text()) || 0));
        $row.parent().find(".income").text(grand);
      },
      "income_calculation_failed"
    );

    bindHandler(
      ".expense_data",
      "keyup",
      function (): void {
        const $row = $(this).closest("tr");
        let cat = 0;
        $row
          .find(".expense_data")
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          .each((_, i) => (cat += parseFloat($(i).val()) || 0));
        $row.find(".totalExpense").text(cat);
        const m = $(this).data("month") ?? "";
        let mt = 0;
        $row
          .parent()
          .find(`.${m}_expense`)
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          .each((_, i) => (mt += parseFloat($(i).val()) || 0));
        $row.parent().find(`.${m}_total_expense`).text(mt);
        let grand = 0;
        $row
          .parent()
          .find(".totalExpense")
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
          .each((_, i) => (grand += parseFloat($(i).text()) || 0));
        $row.parent().find(".expense").text(grand);
      },
      "expense_calculation_failed"
    );

    bindHandler(
      ".period",
      "change",
      function (): void {
        const v = $(this).val() ?? "";
        $(".budget_plan").addClass("d-none");
        $(`#${v}`).removeClass("d-none").addClass("d-block");
      },
      "period_toggle_failed"
    );

    $(".period").trigger("change");
  });
})();

export {};
