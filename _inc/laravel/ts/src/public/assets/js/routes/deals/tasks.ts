/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/tasks.js
 * @generated from original JavaScript - manual review recommended
 * @module tasks
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global $, jQuery */
((): void => {
  const getMsg = key => {
    const lang = (
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ?? "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    const short = lang === "pt-br" ? lang : lang.slice(0, 2);
    return (
      window.translations?.[short]?.[key] ||
      window.translations?.en?.[key] ||
      "# ERROR"
    );
  };
  const toast = msg =>
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    { window.show_toastr ? window.show_toastr("error", msg, "error") : alert(msg); };

  document.addEventListener("DOMContentLoaded", (): void => {
    try {
      const dateInput = document.getElementById("date");
      if (dateInput && $.fn.daterangepicker) {
        $("#date").daterangepicker({
          locale: { format: "YYYY-MM-DD" },
          singleDatePicker: true,
        });
      } else throw 0;
    } catch {
      toast(getMsg("datepicker_init_failed"));
    }

    try {
      const timeInput = document.getElementById("time");
      if (timeInput && $.fn.timepicker) {
        $("#time").timepicker({
          icons: { up: "ti ti-chevron-up", down: "ti ti-chevron-down" },
        });
      } else throw 0;
    } catch {
      toast(getMsg("timepicker_init_failed"));
    }
  });
})();

export {};
