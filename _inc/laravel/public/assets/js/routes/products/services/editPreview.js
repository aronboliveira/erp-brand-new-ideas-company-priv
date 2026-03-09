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

  const $imgInput = $("#pro_image");
  const $img = $("#image");
  if ($imgInput.length) {
    const onImgChange = function () {
      try {
        const file = this?.files?.[0];
        if (!file || !$img.length) {
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
    if ($imgInput.attr("data-np-bound") !== "true") {
      $imgInput.on("change", onImgChange);
      $imgInput.attr("data-np-bound", "true");
    }
  }

  if (document.body.getAttribute("data-np-qty-bound") !== "true") {
    $(document).on("click", ".type", function () {
      try {
        const isProduct =
          String($(this).val() ?? "").toLowerCase() === "product";
        const $qty = $(".quantity");
        if (!$qty.length) {
          scheduleError(getMsg("toggle_quantity_unavailable"));
          return;
        }
        $qty
          .toggleClass("d-none", !isProduct)
          .toggleClass("d-block", isProduct);
      } catch {
        scheduleError(getMsg("toggle_quantity_unavailable"));
      }
    });
    document.body.setAttribute("data-np-qty-bound", "true");
  }
})();
