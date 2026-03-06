/**
 * @fileoverview TypeScript version of public/assets/js/routes/warehouses/transfers/quantity.js
 * @generated from original JavaScript - manual review recommended
 * @module quantity
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
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
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = (): void => {
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
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
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
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
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
  const csrf = (): void => {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m.getAttribute("content") ?? "";
  };
  const isBadUrl = u => !u || u === "#";
  const urls = {
    products: '{{ route(VW::WRH_TRF.".get.product") }}',
    quantity: '{{ route(VW::WRH_TRF.".get.quantity") }}',
  };

  const ensureJq = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!$.fn) {
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
  const buildProductControls = (): void => {
    const wrap = $("#product_div");
    if (wrap.length === 0) return;
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (!wrap.find('label[for="product"]').length) {
      wrap.append(
        '<label for="product" class="form-label">{{ __("Product") }}</label>'
      );
    }
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (!$("#product_id").length) {
      wrap.append(
        '<select class="form-control" id="product_id" name="product_id"></select>'
      );
    }
  };
  const populateSelect = ($sel, entries, placeholder) => {
    if (!$sel?.length) return;
    $sel.empty();
    if (placeholder) $sel.append(`<option value="">${placeholder}</option>`);
    $.each(entries, function (key, value) {
      $sel.append(`<option value="${key}">${value}</option>`);
    });
  };
  const getProduct = wid => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
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
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
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
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
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
  const bind = (): void => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!ensureJq()) return;
    const host = document.body;
    if (host.getAttribute(dataBindGuard) === "true") return;
    host.setAttribute(dataBindGuard, "true");
    $(document).on("change" + ns, 'select[name="from_warehouse"]', function (): void {
      const v = $(this).val();
      getProduct(v);
    });
    $(document).on("change" + ns, "#product_id", function (): void {
      const pid = $(this).val();
      const wid = $("#warehouse_id").val();
      getQuantity(pid, wid);
    });
    const startWid = $("#warehouse_id").val();
    if (startWid != null) getProduct(startWid);
    const mo = new MutationObserver(function (): void {
      if (
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        !$('select[name="from_warehouse"]').length &&
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
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

export {};
