(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};
  const $ = window.jQuery;

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  $(() => {
    const bindIncome = () => {
      $(".income_data").each((_, el) => {
        const $el = $(el);
        if ($el.data("listener-income") === true) return;
        $el.data("listener-income", true);
        const handler = e => {
          try {
            const $row = $el.closest("tr");
            let catTotal = 0;
            $row.find(".income_data").each((i, inp) => {
              const v = parseFloat($(inp).val()) || 0;
              catTotal += v;
            });
            $row.find(".totalIncome").text(catTotal);
            const month = $el.data("month") || "";
            let mTotal = 0;
            $row
              .parent()
              .find(`.${month}_income`)
              .each((i, inp) => {
                mTotal += parseFloat($(inp).val()) || 0;
              });
            $row.parent().find(`.${month}_total_income`).text(mTotal);
            let grand = 0;
            $row
              .parent()
              .find(".totalIncome")
              .each((i, td) => {
                grand += parseFloat($(td).text()) || 0;
              });
            $row.parent().find(".income").text(grand);
          } catch {
            scheduleError(getMsg("income_calculation_failed"), "pointerup");
          }
        };
        $el.on("keyup", handler);
        handler();
      });
    };

    const bindExpense = () => {
      $(".expense_data").each((_, el) => {
        const $el = $(el);
        if ($el.data("listener-expense") === true) return;
        $el.data("listener-expense", true);
        const handler = e => {
          try {
            const $row = $el.closest("tr");
            let catTotal = 0;
            $row.find(".expense_data").each((i, inp) => {
              catTotal += parseFloat($(inp).val()) || 0;
            });
            $row.find(".totalExpense").text(catTotal);
            const month = $el.data("month") || "";
            let mTotal = 0;
            $row
              .parent()
              .find(`.${month}_expense`)
              .each((i, inp) => {
                mTotal += parseFloat($(inp).val()) || 0;
              });
            $row.parent().find(`.${month}_total_expense`).text(mTotal);
            let grand = 0;
            $row
              .parent()
              .find(".totalExpense")
              .each((i, td) => {
                grand += parseFloat($(td).text()) || 0;
              });
            $row.parent().find(".expense").text(grand);
          } catch {
            scheduleError(getMsg("expense_calculation_failed"), "pointerup");
          }
        };
        $el.on("keyup", handler);
        handler();
      });
    };

    const bindPeriod = () => {
      $(".period").each((_, el) => {
        const $el = $(el);
        if ($el.data("listener-period") === true) return;
        $el.data("listener-period", true);
        const handler = e => {
          try {
            const val = $el.val() || "";
            $(".budget_plan").addClass("d-none");
            $(`#${val}`).removeClass("d-none").addClass("d-block");
          } catch {
            scheduleError(getMsg("period_toggle_failed"), "pointerup");
          }
        };
        $el.on("change", handler);
        handler();
      });
    };

    bindIncome();
    bindExpense();
    bindPeriod();
  });
})();
