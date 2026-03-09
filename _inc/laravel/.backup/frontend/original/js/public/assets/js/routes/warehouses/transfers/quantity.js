(function () {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-warehouse-error";
  const dataBindGuard = "data-warehouse-bound";
  const ns = "._npWarehouse";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBootstrap = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = () => {
    let c = qs("#np-toast-container");
    if (c) return c;
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = message => {
    if (hasBootstrap()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) body.textContent = message ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleClickError = message => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = () => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("click", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[key] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[key] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const csrf = () => {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m?.getAttribute("content") || "";
  };
  const isBadUrl = u => !u || u === "#";
  const urls = {
    products: '{{ route(VW::WRH_TRF.".get.product") }}',
    quantity: '{{ route(VW::WRH_TRF.".get.quantity") }}',
  };

  const ensureJq = () => {
    if (!$ || !$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      scheduleClickError(getMsg(document.body, "plugin_unavailable"));
      return false;
    }
    return true;
  };
  const buildProductControls = () => {
    const wrap = $("#product_div");
    if (!wrap.length) return;
    if (!wrap.find('label[for="product"]').length) {
      wrap.append(
        '<label for="product" class="form-label">{{ __("Product") }}</label>'
      );
    }
    if (!$("#product_id").length) {
      wrap.append(
        '<select class="form-control" id="product_id" name="product_id"></select>'
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
    if (isBadUrl(urls.products)) {
      scheduleClickError(
        getMsg(document.body, "warehouse_products_unavailable")
      );
      return;
    }
    $.ajax({
      url: urls.products,
      type: "POST",
      data: { warehouse_id: wid ?? "", _token: csrf() },
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
          scheduleClickError(
            getMsg(document.body, "warehouse_products_unavailable")
          );
        }
      },
    });
  };
  const getQuantity = (pid, wid) => {
    if (!ensureJq()) return;
    if (isBadUrl(urls.quantity)) {
      scheduleClickError(
        getMsg(document.body, "warehouse_quantity_unavailable")
      );
      return;
    }
    $.ajax({
      url: urls.quantity,
      type: "POST",
      data: { product_id: pid ?? "", warehouse_id: wid ?? "", _token: csrf() },
      success: function (data) {
        try {
          $("#quantity").val((data ?? "").toString());
        } catch (_) {
          scheduleClickError(
            getMsg(document.body, "warehouse_quantity_unavailable")
          );
        }
      },
    });
  };
  const bind = () => {
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
    const mo = new MutationObserver(function () {
      if (
        !$('select[name="from_warehouse"]').length &&
        !$("#product_id").length
      ) {
        $(document).off("change" + ns);
        host.removeAttribute(dataBindGuard);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  } else {
    bind();
  }
})();
