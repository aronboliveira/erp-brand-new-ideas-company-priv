(function() {
"use strict";
/**
 * @fileoverview TypeScript version of public/assets/js/routes/warehouses/transfers/quantity.js
 * @generated from original JavaScript - manual review recommended
 * @module quantity
 */



(function () {
    const $ = window.jQuery;
    const errFb = "# ERROR", dataClientLocalized = "data-client-localized", dataGuardMsg = "data-guard-msg", dataSvLocalized = "data-sv-localized", dataErrGuard = "data-warehouse-error", dataBindGuard = "data-warehouse-bound", ns = "._npWarehouse", qs = (s, r = document) => r.querySelector(s);

    const hasBootstrap = () => !!(

    (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]'))) && !!window.bootstrap.Toast;
    const ensureToastContainer = () => {
        let c = qs("#np-toast-container");
        if (c)
            return c;
        c = document.createElement("div");
        c.id = "np-toast-container";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        Object.assign(c.style, { position: "fixed", top: "1rem", right: "1rem" });
        document.body.appendChild(c);
        return c;
    };
    const showErrorNow = (message) => {
        if (hasBootstrap()) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                for (const [k, v] of Object.entries({
                    role: "alert",
                    "aria-live": "assertive",
                    "aria-atomic": "true",
                }))
                    t.setAttribute(k, v);
                t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = qs(".toast-body", t);
            if (body)
                body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            }
            catch (_) {
                alert(message ?? errFb);
            }
        }
        else {
            alert(message ?? errFb);
        }
    };
    const scheduleClickError = (message) => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true")
            return;
        host.setAttribute(dataErrGuard, "true");
        const once = () => {
            try {
                showErrorNow(message);
            }
            finally {
                host.removeAttribute(dataErrGuard);
            }
        };
        document.addEventListener("click", once, { once: true });
        const mo = new MutationObserver((_m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("click", once);
                o.disconnect();
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });

    };

    const getMsg = (el, key) => {
        let msg = errFb;
        if (el.getAttribute(dataSvLocalized) === "true" ||
            el.getAttribute(dataClientLocalized) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
        }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") ??
                document.documentElement.lang ??
                "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                    el.getAttribute(dataGuardMsg) ||
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
        return m?.getAttribute("content") ?? "";
    };
    const isBadUrl = (u) => !u || u === "#";
    const urls = {
        products: '{{ route(VW::WRH_TRF.".get.product") }}',
        quantity: '{{ route(VW::WRH_TRF.".get.quantity") }}',
    };
    const ensureJq = () => {
        if (!$.fn) {
            try {
                if (window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1")
                    console.error("jQuery unavailable");
            }
            catch (_) {
                console.error(`[quantity] Error:`, _);
            }
            scheduleClickError(getMsg(document.body, "plugin_unavailable"));
            return false;
        }
        return true;
    };
    const buildProductControls = () => {
        const wrap = $("#product_div");
        if (wrap.length === 0)
            return;
        if (!wrap.find('label[for="product"]').length)
            wrap.append('<label for="product" class="form-label">{{ __("Product") }}</label>');
        if (!$("#product_id").length)
            wrap.append('<select class="form-control" id="product_id" name="product_id"></select>');
    };
    const populateSelect = ($sel, entries, placeholder) => {
        if (!$sel?.length)
            return;
        $sel.empty();
        if (placeholder)
            $sel.append(`<option value="">${placeholder}</option>`);
        Object.entries(entries).forEach(([key, value]) => {

            $sel.append(`<option value="${key}">${value}</option>`);
        });
    };
    const getProduct = (wid) => {
        if (!ensureJq())
            return;
        if (isBadUrl(urls.products)) {
            scheduleClickError(getMsg(document.body, "warehouse_products_unavailable"));
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
                    if (data.ware_products)
                        populateSelect($product, data.ware_products);
                    const $to = $('select[name="to_warehouse"]');
                    if ($to.length && data.to_warehouses) {
                        $to.empty();
                        Object.entries(data.to_warehouses).forEach(([key, value]) => {

                            $to.append(`<option value="${key}">${value}</option>`);
                        });
                    }
                }
                catch (_) {
                    scheduleClickError(getMsg(document.body, "warehouse_products_unavailable"));
                }
            },
        });
    };
    const getQuantity = (pid, wid) => {
        if (!ensureJq())
            return;
        if (isBadUrl(urls.quantity)) {
            scheduleClickError(getMsg(document.body, "warehouse_quantity_unavailable"));
            return;
        }
        $.ajax({
            url: urls.quantity,
            type: "POST",
            data: { product_id: pid ?? "", warehouse_id: wid ?? "", _token: csrf() },
            success: function (data) {
                try {
                    $("#quantity").val(String(data ?? ""));
                }
                catch (_) {
                    scheduleClickError(getMsg(document.body, "warehouse_quantity_unavailable"));
                }
            },
        });
    };
    const bind = () => {
        if (!ensureJq())
            return;
        const host = document.body;
        if (host.getAttribute(dataBindGuard) === "true")
            return;
        host.setAttribute(dataBindGuard, "true");
        $(document).on("change" + ns, 'select[name="from_warehouse"]', function () {

            const v = String($(this).val() ?? "");
            getProduct(v);
        });
        $(document).on("change" + ns, "#product_id", function () {

            const pid = String($(this).val() ?? ""), wid = String($("#warehouse_id").val() ?? "");
            getQuantity(pid, wid);
        });
        const startWid = $("#warehouse_id").val();
        if (startWid != null)
            getProduct(String(startWid));
        const mo = new MutationObserver(function () {
            if (!$('select[name="from_warehouse"]').length &&
                !$("#product_id").length) {
                $(document).off("change" + ns);
                host.removeAttribute(dataBindGuard);
            }
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    document.readyState === "loading"
        ? document.addEventListener("DOMContentLoaded", bind, { once: true })
        : bind();
})();
})();