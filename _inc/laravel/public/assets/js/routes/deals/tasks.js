(() => {
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof getMsg !== "function") {
    void(0);
    return;
  }

  const toast = msg =>
    window.show_toastr ? window.show_toastr("error", msg, "error") : alert(msg);

  document.addEventListener("DOMContentLoaded", () => {
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
