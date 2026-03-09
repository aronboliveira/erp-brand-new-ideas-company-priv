(() => {
  const $ = window.jQuery;
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("click", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";

  if (!$ || !$.fn) {
    scheduleError(getMsg("plugin_unavailable"));
    return;
  }

  const bindImagePreview = () => {
    const handler = function () {
      try {
        const file = this?.files?.[0];
        if (!file) return;
        const $img = $("#image");
        if (!$img.length) {
          scheduleError(getMsg("image_preview_unavailable"));
          return;
        }
        const prev = this.getAttribute("data-prev-url") || "";
        const url = URL.createObjectURL(file);
        $img.attr("src", url);
        if (prev) {
          try {
            URL.revokeObjectURL(prev);
          } catch {}
        }
        this.setAttribute("data-prev-url", url);
      } catch {
        scheduleError(getMsg("image_preview_unavailable"));
      }
    };

    const $input = $("#pro_image");
    if ($input.length && $input.attr("data-np-bound") !== "true") {
      $input.on("change", handler);
      $input.attr("data-np-bound", "true");
    }

    if (document.body.getAttribute("data-np-delegate-img") !== "true") {
      $(document).on("change", "#pro_image", function () {
        if ($(this).attr("data-np-bound") === "true") return;
        handler.call(this);
      });
      document.body.setAttribute("data-np-delegate-img", "true");
    }
  };

  const bindQuantityToggle = () => {
    if (document.body.getAttribute("data-np-qty-bound") === "true") return;
    $(document).on("click", ".type", function () {
      try {
        const type = String($(this).val() ?? "").toLowerCase();
        const $q = $(".quantity");
        if (!$q.length) return;
        if (type === "product") {
          $q.removeClass("d-none").addClass("d-block");
        } else {
          $q.addClass("d-none").removeClass("d-block");
        }
      } catch {
        scheduleError(getMsg("toggle_quantity_unavailable"));
      }
    });
    document.body.setAttribute("data-np-qty-bound", "true");
  };

  bindImagePreview();
  bindQuantityToggle();
})();
