(function () {
  const $ = window.jQuery;
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("click", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";
  const routeGuard = url => !url || url === "#";
  const dataBindGuard = "data-warehouse-bound";
  const ns = "._npWarehouse";
  const urls = {
    products: '{{ route(VW::WRH_TRF.".get.product") }}',
    quantity: '{{ route(VW::WRH_TRF.".get.quantity") }}',
  };

  const ensureJq = () => {
    if (!$ || !$.fn) {
      scheduleError(getMsg("plugin_unavailable"));
      return false;
    }
    return true;
  };
  const buildProductControls = () => {
    const wrap = $("#product_div");
    if (!wrap.length) return;
    if (!wrap.find('label[for="product"]').length) {
      wrap.append(
        '<label for="product" class="form-label">{{ __("Product") }}</label>',
      );
    }
    if (!$("#product_id").length) {
      wrap.append(
        '<select class="form-control" id="product_id" name="product_id"></select>',
      );
    }
  };
  const populateSelect = ($sel, entries, placeholder) => {
    if (!$sel || !$sel.length) return;
    $sel.empty();
    if (placeholder) $sel.append(`<option value="">${placeholder}</option>`);
    $.each(entries, function (key, value) {
      $sel.append(`<option value="${key}">${value}</option>`);
    });
  };
  const getProduct = wid => {
    if (!ensureJq()) return;
    if (routeGuard(urls.products)) {
      scheduleError(getMsg("warehouse_products_unavailable"));
      return;
    }
    $.ajax({
      url: urls.products,
      type: "POST",
      data: { warehouse_id: wid ?? "", _token: utils.getCsrfToken() },
      success: function (data) {
        try {
          buildProductControls();
          const $product = $("#product_id");
          populateSelect($product, {}, '{{ __("Select Product") }}');
          if (data?.ware_products) {
            populateSelect($product, data.ware_products);
          }
          const $to = $('select[name="to_warehouse"]');
          if ($to.length && data?.to_warehouses) {
            $to.empty();
            $.each(data.to_warehouses, function (key, value) {
              $to.append(`<option value="${key}">${value}</option>`);
            });
          }
        } catch (_) {
          scheduleError(getMsg("warehouse_products_unavailable"));
        }
      },
      error: () => scheduleError(getMsg("ajax_unavailable")),
    });
  };
  const getQuantity = (pid, wid) => {
    if (!ensureJq()) return;
    if (routeGuard(urls.quantity)) {
      scheduleError(getMsg("warehouse_quantity_unavailable"));
      return;
    }
    $.ajax({
      url: urls.quantity,
      type: "POST",
      data: {
        product_id: pid ?? "",
        warehouse_id: wid ?? "",
        _token: utils.getCsrfToken(),
      },
      success: function (data) {
        try {
          $("#quantity").val((data ?? "").toString());
        } catch (_) {
          scheduleError(getMsg("warehouse_quantity_unavailable"));
        }
      },
      error: () => scheduleError(getMsg("ajax_unavailable")),
    });
  };
  $(() => {
    if (!ensureJq()) return;
    const host = document.body;
    if (host.getAttribute(dataBindGuard) === "true") return;
    host.setAttribute(dataBindGuard, "true");
    $(document).on("change" + ns, 'select[name="from_warehouse"]', function () {
      const v = $(this).val();
      getProduct(v);
    });
    $(document).on("change" + ns, "#product_id", function () {
      const pid = $(this).val();
      const wid = $("#warehouse_id").val();
      getQuantity(pid, wid);
    });
    const startWid = $("#warehouse_id").val();
    if (startWid != null) getProduct(startWid);
  });
})();
