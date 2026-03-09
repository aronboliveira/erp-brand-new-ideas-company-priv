(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;
  const TYPE_RADIO = 'input[name="type"][type="radio"]';
  const MONTH_CLASS = "month";
  const DATE_CLASS = "date";
  const TOGGLER_ATTR = "data-toggler-initialized";

  const handleToggle = target => {
    try {
      if (typeof $ !== "function") {
        scheduleError(getMsg("toggle_failed"));
        return;
      }
      const type = target.value ?? "";
      const showMonth = type === "monthly";
      document.querySelectorAll(`.${MONTH_CLASS}`).forEach(el => {
        el.classList.toggle("d-block", showMonth);
        el.classList.toggle("d-none", !showMonth);
      });
      document.querySelectorAll(`.${DATE_CLASS}`).forEach(el => {
        el.classList.toggle("d-block", !showMonth);
        el.classList.toggle("d-none", showMonth);
      });
    } catch (_) {
      scheduleError(getMsg("toggle_failed"));
    }
  };

  try {
    const radios = document.querySelectorAll(TYPE_RADIO);
    if (!radios.length) return;

    radios.forEach(radio => {
      if (radio.getAttribute(TOGGLER_ATTR) === "true") return;
      radio.setAttribute(TOGGLER_ATTR, "true");
      radio.addEventListener("change", ({ target }) => handleToggle(target));
    });

    const checked = document.querySelector(`${TYPE_RADIO}:checked`);
    if (checked) checked.dispatchEvent(new Event("change"));
  } catch (_) {
    scheduleError(getMsg("toggler_unavailable"));
  }
})();
