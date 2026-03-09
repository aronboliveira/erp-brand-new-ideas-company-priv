/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/tasks.js
 * @generated from original JavaScript - manual review recommended
 * @module tasks
 */

import "../../../../../declarations/routes/vendor-libs";

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const getMsg = (key: string) => {
    const lang = (
        sessionStorage.getItem("erp-np-lang") ??
        (document.documentElement.lang || "en")
      )
        .toLowerCase()
        .replace(/_/g, "-"),
      short = lang === "pt-br" ? lang : lang.slice(0, 2);
    return (
      window.translations?.[short]?.[key] ||
      window.translations?.en?.[key] ||
      "# ERROR"
    );
  };
  const toast = (msg: string): void => {
    window.show_toastr ? window.show_toastr("error", msg, "error") : alert(msg);
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    try {
      const dateInput = document.getElementById("date");
      if (dateInput && typeof $.fn.daterangepicker === "function") {
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
      if (timeInput && typeof $.fn.timepicker === "function") {
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
