(() => {
  const $ = window.jQuery;
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("pointerup", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";

  $(() => {
    const bindHandler = (selector, event, handler, errorKey) => {
      $(document).on(event, selector, function () {
        try {
          handler.call(this);
        } catch {
          scheduleError(getMsg(errorKey));
        }
      });
    };

    bindHandler(
      ".income_data",
      "keyup",
      function () {
        const $row = $(this).closest("tr");
        let cat = 0;
        $row
          .find(".income_data")
          .each((_, i) => (cat += parseFloat($(i).val()) || 0));
        $row.find(".totalIncome").text(cat);
        const m = $(this).data("month") || "";
        let mt = 0;
        $row
          .parent()
          .find(`.${m}_income`)
          .each((_, i) => (mt += parseFloat($(i).val()) || 0));
        $row.parent().find(`.${m}_total_income`).text(mt);
        let grand = 0;
        $row
          .parent()
          .find(".totalIncome")
          .each((_, i) => (grand += parseFloat($(i).text()) || 0));
        $row.parent().find(".income").text(grand);
      },
      "income_calculation_failed",
    );

    bindHandler(
      ".expense_data",
      "keyup",
      function () {
        const $row = $(this).closest("tr");
        let cat = 0;
        $row
          .find(".expense_data")
          .each((_, i) => (cat += parseFloat($(i).val()) || 0));
        $row.find(".totalExpense").text(cat);
        const m = $(this).data("month") || "";
        let mt = 0;
        $row
          .parent()
          .find(`.${m}_expense`)
          .each((_, i) => (mt += parseFloat($(i).val()) || 0));
        $row.parent().find(`.${m}_total_expense`).text(mt);
        let grand = 0;
        $row
          .parent()
          .find(".totalExpense")
          .each((_, i) => (grand += parseFloat($(i).text()) || 0));
        $row.parent().find(".expense").text(grand);
      },
      "expense_calculation_failed",
    );

    bindHandler(
      ".period",
      "change",
      function () {
        const v = $(this).val() || "";
        $(".budget_plan").addClass("d-none");
        $(`#${v}`).removeClass("d-none").addClass("d-block");
      },
      "period_toggle_failed",
    );

    $(".period").trigger("change");
  });
})();
