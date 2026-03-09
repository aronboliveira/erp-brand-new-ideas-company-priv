/**
 * @fileoverview TypeScript version of public/assets/js/routes/budgets/toggleCreate.js
 * @generated from original JavaScript - manual review recommended
 * @module toggleCreate
 */

const $ = window.jQuery!;
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  const errFb = "# ERROR",
    guardMsg = "data-guard-msg",
    clientFlag = "data-client-localized",
    langKey = "erp-np-lang";
  let errorMessage = "";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  function getLocalizedMessage(key: string, el: HTMLElement) {
    let msg = errFb;
    if (el.getAttribute(clientFlag) === "true") {
      msg = el.getAttribute(guardMsg) || msg;
    } else {
      let lang = (
        sessionStorage.getItem(langKey) ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const translations = window.translations!;
      msg =
        translations[lang][key] ||
        el.getAttribute(guardMsg) ||
        translations.en[key] ||
        msg;
      if (msg !== errFb) {
        el.setAttribute(guardMsg, msg);
        el.setAttribute(clientFlag, "true");
      }
    }
    return msg;
  }

  function showError(message: string): void {
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
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          toast.setAttribute(k, v);
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
    ms.forEach(m => {
      Array.from(m.removedNodes).forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onErrorPointerUp);
          obs.disconnect();
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });

  $((): void => {
    const bindHandler = (
      selector: string,
      event: string,
      handler: (this: HTMLElement) => void,
      errorKey: string,
    ): void => {
      $(document).on(event, selector, function (this: HTMLElement): void {
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
      function (this: HTMLElement): void {
        const $row = $(this).closest("tr");
        let cat = 0;
        $row.find(".income_data").each((_: number, el: HTMLElement) => {
          cat += parseFloat($(el).val() as string) || 0;
        });
        $row.find(".totalIncome").text(String(cat));
        const m = String($(this).data("month") ?? "");
        let mt = 0;
        $row
          .parent()
          .find(`.${m}_income`)
          .each((_: number, el: HTMLElement) => {
            mt += parseFloat($(el).val() as string) || 0;
          });
        $row.parent().find(`.${m}_total_income`).text(String(mt));
        let grand = 0;
        $row
          .parent()
          .find(".totalIncome")
          .each((_: number, el: HTMLElement) => {
            grand += parseFloat($(el).text()) || 0;
          });
        $row.parent().find(".income").text(String(grand));
      },
      "income_calculation_failed",
    );

    bindHandler(
      ".expense_data",
      "keyup",
      function (this: HTMLElement): void {
        const $row = $(this).closest("tr");
        let cat = 0;
        $row.find(".expense_data").each((_: number, el: HTMLElement) => {
          cat += parseFloat($(el).val() as string) || 0;
        });
        $row.find(".totalExpense").text(String(cat));
        const m = String($(this).data("month") ?? "");
        let mt = 0;
        $row
          .parent()
          .find(`.${m}_expense`)
          .each((_: number, el: HTMLElement) => {
            mt += parseFloat($(el).val() as string) || 0;
          });
        $row.parent().find(`.${m}_total_expense`).text(String(mt));
        let grand = 0;
        $row
          .parent()
          .find(".totalExpense")
          .each((_: number, el: HTMLElement) => {
            grand += parseFloat($(el).text()) || 0;
          });
        $row.parent().find(".expense").text(String(grand));
      },
      "expense_calculation_failed",
    );

    bindHandler(
      ".period",
      "change",
      function (this: HTMLElement): void {
        const v = String($(this).val() ?? "");
        $(".budget_plan").addClass("d-none");
        $(`#${v}`).removeClass("d-none").addClass("d-block");
      },
      "period_toggle_failed",
    );

    $(".period").trigger("change");
  });
})();

export {};
