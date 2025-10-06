
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $authUser = Auth::user();
    $lang = Utility::fetchUserLang(user:$authUser);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Purchase Create')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $purchaseIndexBase              = ViewsConstants::PRC.'.index';
        $purchaseIndexKebab             = Str::kebab($purchaseIndexBase);
        $purchaseIndexResolved          = Route::has($purchaseIndexBase) ? $purchaseIndexBase : (Route::has($purchaseIndexKebab) ? $purchaseIndexKebab : null);
        $purchaseIndexUrl               = $purchaseIndexResolved ? route($purchaseIndexResolved) : '#';
        $purchaseIndexGuardMsg          = Utility::fetchLinkMessage($lang, ViewsConstants::PRC, 'purchase_index_route_unavailable') ?? 'Purchase index route is unavailable. Please contact technical support or your domain administrator.';
        $purchaseIndexLinkId            = 'purchase-index-breadcrumb-link';
    @endphp
    <li class="breadcrumb-item">
        <a href="{{ $purchaseIndexUrl }}" id="{{ $purchaseIndexLinkId }}" class="{{ VC::BT_LNK ?? '' }}" data-ajax-popup="true" data-title="{{ __('Purchase') }}" data-url="{{ $purchaseIndexUrl }}" data-guard-msg="{{ $purchaseIndexGuardMsg }}" data-sv-localized="true">{{ __('Purchase') }}</a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/js/routes/purchases/index.js') }}" defer></script>
    @endpush
    <li class="breadcrumb-item">{{__('Purchase Create')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/purchases/lang/create.js') }}">
    </script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            if (!$) { try { 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) {
                    console.error("jQuery unavailable");
                }
             } catch (_) { } return; }
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataListenerGuard = "data-listener-guard";
            const dataErrGuard = "data-error-guard";
            const dataSvLocalized = "data-sv-localized";
            const scheduleInteractiveError = (message) => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
            host.setAttribute(dataErrGuard, "true");
            const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
            document.addEventListener("pointerup", once, { once: true });
            };
            const ensureToastContainer = () => {
            const id = "np-toast-container";
            let c = qs("#" + id);
            if (c) { return c; }
            c = document.createElement("div");
            c.id = id;
            c.setAttribute("aria-live", "polite");
            c.setAttribute("aria-atomic", "true");
            c.style.position = "fixed";
            c.style.top = "1rem";
            c.style.right = "1rem";
            document.body.appendChild(c);
            return c;
            };
            const showErrorNow = (message) => {
            const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') || qs('link[href*="bootstrap"]')) && window.bootstrap && window.bootstrap.Toast;
            if (hasBootstrap) {
                const container = ensureToastContainer();
                const toastId = "np-toast";
                let t = qs("#" + toastId, container);
                if (!t) {
                t = document.createElement("div");
                t.id = toastId;
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML = '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) { body.textContent = message ?? errFb; }
                try { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } catch (_) { alert(message ?? errFb); }
            } else { alert(message ?? errFb); }
            };
            const getMsgFor = (el, key) => {
            const errFbL = errFb;
            const dataClientLocalizedL = dataClientLocalized;
            const dataGuardMsgL = dataGuardMsg;
            let msg = errFbL;
            if (el && (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalizedL) === "true")) { msg = el.getAttribute(dataGuardMsgL) || errFbL; }
            else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = key;
                msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsgL) || window.translations?.["en"]?.[msgKey] || errFbL;
                if (el && msg !== errFbL) { el.setAttribute(dataGuardMsgL, msg); el.setAttribute(dataClientLocalizedL, "true"); }
            }
            return msg;
            };
            const routeOrFail = (el) => {
            const url = el?.getAttribute("data-url");
            const href = el?.tagName === "A" ? el.getAttribute("href") : el?.tagName === "FORM" ? el.getAttribute("action") : "";
            if ((!url || url === "#") && (!href || href === "#")) { scheduleInteractiveError(getMsgFor(el, "endpoint_unavailable")); return null; }
            return url || href || null;
            };
            const bindWithObserver = (el, evt, handler, flag) => {
            if (!el || el.getAttribute(flag) === "true") { return; }
            el.setAttribute(flag, "true");
            $(el).on(evt, handler);
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(el)) { $(el).off(evt, handler); o.disconnect(); } });
            mo.observe(document.body, { childList: true, subtree: true });
            };
            const safeFloat = (v) => { const n = parseFloat(v); return Number.isFinite(n) ? n : 0; };
            const recalcTotals = () => {
            let totalItemTaxPrice = 0;
            let totalItemPrice = 0;
            let totalDiscount = 0;
            const itemTaxPriceInput = $(".itemTaxPrice");
            for (let j = 0; j < itemTaxPriceInput.length; j++) { totalItemTaxPrice += safeFloat(itemTaxPriceInput[j].value); }
            const inputsQuantity = $(".quantity");
            const priceInput = $(".price");
            for (let j = 0; j < priceInput.length; j++) { totalItemPrice += safeFloat(priceInput[j].value) * safeFloat(inputsQuantity[j]?.value); }
            const inputs = $(".amount");
            let subTotal = 0;
            for (let i = 0; i < inputs.length; i++) { subTotal += safeFloat($(inputs[i]).html()); }
            const itemDiscountPriceInput = $(".discount");
            for (let k = 0; k < itemDiscountPriceInput.length; k++) { totalDiscount += safeFloat(itemDiscountPriceInput[k].value); }
            $(".subTotal").html(totalItemPrice.toFixed(2));
            $(".totalTax").html(totalItemTaxPrice.toFixed(2));
            $(".totalAmount").html(subTotal.toFixed(2));
            $(".totalDiscount").html(totalDiscount.toFixed(2));
            };
            const attachRepeater = () => {
            const selector = "body";
            if (!$(selector + " .repeater").length) { return; }
            if (!$.fn.sortable || !$.fn.repeater) { try { 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) {
                    console.error("jQuery Sortable or Repeater unavailable");
                }
            } catch (_) { } scheduleInteractiveError(getMsgFor(document.body, "plugin_unavailable")); }
            const $dragAndDrop = $("body .repeater tbody").sortable ? $("body .repeater tbody").sortable({ handle: ".sort-handler" }) : $("body .repeater tbody");
            const $repeater = $.fn.repeater ? $(selector + " .repeater").repeater({
                initEmpty: false,
                defaultValues: { status: 1 },
                show: function () {
                $(this).slideDown();
                const fileUploads = $(this).find("input.multi");
                if (fileUploads.length) {
                    if ($.fn.MultiFile) {
                    try { $(this).find("input.multi").MultiFile({ max: 3, accept: "png|jpg|jpeg", max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }} }); } catch (_) { scheduleInteractiveError(getMsgFor(this, "plugin_unavailable")); }
                    } else { try { 
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) {
                            console.error("MultiFile unavailable");
                        }
                     } catch (_) { } scheduleInteractiveError(getMsgFor(this, "plugin_unavailable")); }
                }
                if ($.fn.select2) { $(".select2").select2(); } else { try { 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) {
                        console.error("Select2 unavailable");
                    }
                } catch (_) { } }
                },
                hide: function (deleteElement) {
                if (window.confirm("Are you sure you want to delete this element?")) {
                    $(this).slideUp(deleteElement);
                    $(this).remove();
                    const inputs = $(".amount");
                    let subTotal = 0;
                    for (let i = 0; i < inputs.length; i++) { subTotal += safeFloat($(inputs[i]).html()); }
                    $(".subTotal").html(subTotal.toFixed(2));
                    $(".totalAmount").html(subTotal.toFixed(2));
                }
                },
                ready: function (setIndexes) { if ($dragAndDrop && $dragAndDrop.on) { $dragAndDrop.on("drop", setIndexes); } },
                isFirstItemUndeletable: true
            }) : null;
            let value = $(selector + " .repeater").attr("data-value");
            if (typeof value !== "undefined" && value && $repeater && $repeater.setList) { try { value = JSON.parse(value); $repeater.setList(value); } catch (_) { } }
            };
            const onVendorChange = function () {
            $("#vendor_detail").removeClass("d-none").addClass("d-block");
            $("#vendor-box").removeClass("d-block").addClass("d-none");
            const id = $(this).val();
            const url = routeOrFail(this);
            if (!url) { return; }
            try {
                $.ajax({
                url: url,
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("#token").val() ?? "" },
                data: { id: id },
                cache: false,
                success: function (data) {
                    if (data) { $("#vendor_detail").html(data); }
                    else { $("#vendor-box").removeClass("d-none").addClass("d-block"); $("#vendor_detail").removeClass("d-block").addClass("d-none"); }
                },
                error: function () { scheduleInteractiveError(getMsgFor(document.getElementById("vendor"), "vendor_unavailable")); }
                });
            } catch (_) { scheduleInteractiveError(getMsgFor(document.getElementById("vendor"), "vendor_unavailable")); }
            };
            const onRemoveClick = function () { $("#vendor-box").removeClass("d-none").addClass("d-block"); $("#vendor_detail").removeClass("d-block").addClass("d-none"); };
            const onItemChange = function () {
            const itemsId = $(this).val();
            const url = routeOrFail(this);
            if (!url) { return; }
            const el = $(this);
            try {
                $.ajax({
                url: url,
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("#token").val() ?? "" },
                data: { product_id: itemsId },
                cache: false,
                success: function (data) {
                    let item = null;
                    try { item = typeof data === "string" ? JSON.parse(data) : data; } catch (_) { item = null; }
                    if (!item || !item.product) { scheduleInteractiveError(getMsgFor(el.get(0), "item_unavailable")); return; }
                    $(el.closest("tr").find(".quantity")).val(1);
                    $(el.closest("tr").find(".price")).val(item.product.purchase_price);
                    $(el.closest("tr").find(".pro_description")).val(item.product.description);
                    let taxes = "";
                    const tax = [];
                    let totalItemTaxRate = 0;
                    if (item.taxes === 0 || !item.taxes || !item.taxes.length) { taxes += "-"; }
                    else {
                    for (let i = 0; i < item.taxes.length; i++) {
                        taxes += '<span class="badge bg-primary mt-1 mr-2">' + item.taxes[i].name + " (" + item.taxes[i].rate + "%)" + "</span>";
                        tax.push(item.taxes[i].id);
                        totalItemTaxRate += safeFloat(item.taxes[i].rate);
                    }
                    }
                    const itemTaxPrice = safeFloat((totalItemTaxRate / 100) * (safeFloat(item.product.purchase_price) * 1));
                    $(el.closest("tr").find(".itemTaxPrice")).val(itemTaxPrice.toFixed(2));
                    $(el.closest("tr").find(".itemTaxRate")).val(totalItemTaxRate.toFixed(2));
                    $(el.closest("tr").find(".taxes")).html(taxes);
                    $(el.closest("tr").find(".tax")).val(tax);
                    $(el.closest("tr").find(".unit")).html(item.unit ?? "");
                    $(el.closest("tr").find(".discount")).val(0);
                    $(el.closest("tr").find(".amount")).html(item.totalAmount ?? (safeFloat(item.product.purchase_price) + itemTaxPrice).toFixed(2));
                    const inputs = $(".amount");
                    let subTotal = 0;
                    for (let i = 0; i < inputs.length; i++) { subTotal += safeFloat($(inputs[i]).html()); }
                    $(".subTotal").html(subTotal.toFixed(2));
                    let totalItemPrice = 0;
                    const priceInput = $(".price");
                    for (let j = 0; j < priceInput.length; j++) { totalItemPrice += safeFloat(priceInput[j].value); }
                    let totalItemTaxPrice = 0;
                    const itemTaxPriceInput = $(".itemTaxPrice");
                    for (let j = 0; j < itemTaxPriceInput.length; j++) { totalItemTaxPrice += safeFloat(itemTaxPriceInput[j].value); }
                    $(".totalTax").html(totalItemTaxPrice.toFixed(2));
                    $(".totalAmount").html((safeFloat(subTotal) + safeFloat(totalItemTaxPrice)).toFixed(2));
                },
                error: function () { scheduleInteractiveError(getMsgFor(el.get(0), "item_unavailable")); }
                });
            } catch (_) { scheduleInteractiveError(getMsgFor(this, "item_unavailable")); }
            };
            const onQuantityKey = function () {
            const $row = $(this).closest("tr");
            const quantity = safeFloat($(this).val());
            const price = safeFloat($row.find(".price").val());
            let discount = $row.find(".discount").val();
            discount = (discount && discount.length > 0) ? safeFloat(discount) : 0;
            const totalItemPrice = (quantity * price) - discount;
            const totalItemTaxRate = safeFloat($row.find(".itemTaxRate").val());
            const itemTaxPrice = safeFloat((totalItemTaxRate / 100) * totalItemPrice);
            $row.find(".itemTaxPrice").val(itemTaxPrice.toFixed(2));
            $row.find(".amount").html((itemTaxPrice + totalItemPrice).toFixed(2));
            recalcTotals();
            };
            const onPriceOrDiscount = function () {
            const $row = $(this).closest("tr");
            const price = safeFloat($row.find(".price").val());
            const quantity = safeFloat($row.find(".quantity").val());
            let discount = $row.find(".discount").val();
            discount = (discount && discount.length > 0) ? safeFloat(discount) : 0;
            const totalItemPrice = (quantity * price) - discount;
            const totalItemTaxRate = safeFloat($row.find(".itemTaxRate").val());
            const itemTaxPrice = safeFloat((totalItemTaxRate / 100) * totalItemPrice);
            $row.find(".itemTaxPrice").val(itemTaxPrice.toFixed(2));
            $row.find(".amount").html((itemTaxPrice + totalItemPrice).toFixed(2));
            recalcTotals();
            };
            const onRepeaterDeleteClick = function () { $(".price").trigger("change"); $(".discount").trigger("change"); };
            const scanAndBind = () => {
            const v = document.getElementById("vendor");
            bindWithObserver(v, "change", onVendorChange, dataListenerGuard + "-vendor");
            const r = document.getElementById("remove");
            bindWithObserver(r, "click", onRemoveClick, dataListenerGuard + "-remove");
            const items = document.querySelectorAll(".item");
            items.forEach(el => bindWithObserver(el, "change", onItemChange, dataListenerGuard + "-item"));
            const qty = document.querySelectorAll(".quantity");
            qty.forEach(el => bindWithObserver(el, "keyup", onQuantityKey, dataListenerGuard + "-qty"));
            const price = document.querySelectorAll(".price");
            price.forEach(el => { bindWithObserver(el, "keyup", onPriceOrDiscount, dataListenerGuard + "-price-k"); bindWithObserver(el, "change", onPriceOrDiscount, dataListenerGuard + "-price-c"); });
            const discount = document.querySelectorAll(".discount");
            discount.forEach(el => { bindWithObserver(el, "keyup", onPriceOrDiscount, dataListenerGuard + "-disc-k"); bindWithObserver(el, "change", onPriceOrDiscount, dataListenerGuard + "-disc-c"); });
            const repDel = document.querySelectorAll("[data-repeater-delete]");
            repDel.forEach(el => bindWithObserver(el, "click", onRepeaterDeleteClick, dataListenerGuard + "-rep-del"));
            };
            const init = () => {
            attachRepeater();
            scanAndBind();
            const vendorId = "{{$vendorId}}";
            if (vendorId && safeFloat(vendorId) > 0) { $("#vendor").val(vendorId).trigger("change"); }
            };
            if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init, { once: true }); }
            else { init(); }
        })();
    </script>
@endpush
@section('content')
    @php
        $billVendorBase        = ViewsConstants::BIL.'.vendor';
        $billVendorKebab       = Str::kebab($billVendorBase);
        $billVendorResolved    = Route::has($billVendorBase) ? $billVendorBase : (Route::has($billVendorKebab) ? $billVendorKebab : null);
        $billVendorUrl         = $billVendorResolved ? route($billVendorResolved) : '#';
        $billVendorGuardMsg    = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'vendor_route_unavailable') ?? 'Bill vendor route is unavailable. Please contact technical support or your domain administrator.';
        $mainField = [
            'name'    => 'vendor_id',
            'label'   => __('Vendor'),
            'options' => $vendors,
            'attrs'   => [
                'class'            => 'form-control select',
                'id'               => 'vendor',
                'data-url'         => $billVendorUrl,
                'data-guard-msg'   => $billVendorGuardMsg,
                'data-sv-localized'=> 'true',
                'required'         => 'required',
            ],
        ];
        $sideFields = [
            [
                'name'    => 'warehouse_id',
                'label'   => __('Warehouse'),
                'options' => $warehouse,
                'attrs'   => ['class'=>'form-control select','required'=>'required'],
            ],
            [
                'name'    => 'category_id',
                'label'   => __('Category'),
                'options' => $category,
                'attrs'   => ['class'=>'form-control select','required'=>'required'],
            ],
        ];
        $dateFields = [
            [
                'name'  => 'purchase_date',
                'label' => __('Purchase Date'),
                'type'  => 'date',
                'attrs' => ['class'=>'form-control','required'=>'required'],
            ],
            [
                'name'  => 'purchase_number',
                'label' => __('Purchase Number'),
                'type'  => 'text',
                'value' => $purchase_number,
                'attrs' => ['class'=>'form-control','readonly'=>true],
            ],
        ];
        $tableHeaders = [
            __('Items'),
            __('Quantity'),
            __('Price'),
            __('Discount'),
            __('Tax').' (%)',
            __('Amount').' <br><small class="text-danger font-weight-bold">'.__('after tax & discount').'</small>',
            '',
        ];
        $purchaseStoreBase      = ViewsConstants::PRC;
        $purchaseStoreKebab     = Str::kebab($purchaseStoreBase);
        $purchaseStoreResolved  = Route::has($purchaseStoreBase) ? $purchaseStoreBase : (Route::has($purchaseStoreKebab) ? $purchaseStoreKebab : null);
        $purchaseStoreUrl       = $purchaseStoreResolved ? route($purchaseStoreResolved) : '#';
        $purchaseStoreGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::PRC, 'store_purchase_route_unavailable') ?? 'Store purchase route is unavailable. Please contact technical support or your domain administrator.';
        $purchaseStoreFormId    = 'purchase-store-form';
    @endphp
    {{ Form::open([
        'method'         => 'POST',
        'url'            => $purchaseStoreUrl,
        'class'          => 'w-100',
        'id'             => $purchaseStoreFormId,
        'data-url'       => $purchaseStoreUrl,
        'data-guard-msg' => $purchaseStoreGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/bills/vendor.js') }}" defer></script>
            <script src="{{ asset('assets/js/routes/purchases/store.js') }}" defer></script>
        @endpush
        <input type="hidden" id="token" value="{{ csrf_token() }}">
        @php
            $mainSafe = (isset($mainField) && Utility::isFilled($mainField)) ? $mainField : [];
            $mainName = data_get($mainSafe,'name','vendor_id');
            $mainLabel = data_get($mainSafe,'label') ?? __('No vendor label available');
            $mainOptions = (array)(data_get($mainSafe,'options',[]));
            $mainAttrs = (array)(data_get($mainSafe,'attrs',[]));
            $vendorIdSafe = $vendorId ?? null;
            $sideFieldsSafe = (isset($sideFields) && is_iterable($sideFields)) ? $sideFields : [];
            $dateFieldsSafe = (isset($dateFields) && is_iterable($dateFields)) ? $dateFields : [];
            $headersSafe = (isset($tableHeaders) && is_iterable($tableHeaders)) ? $tableHeaders : [];
            $productServices = (isset($product_services) && (is_array($product_services) || $product_services instanceof \Illuminate\Support\Collection)) ? $product_services : [];
            $currencySymbol = ($authUser && method_exists($authUser,'currencySymbol')) ? $authUser->currencySymbol() : '¤';
        @endphp
        <div class="{{ VC::RW }} {{ VC::MB4 }}">
            <div class="{{ VC::CM6 }}">
                <div id="vendor-box" class="{{ VC::FM_G }}">
                    {{ Form::label($mainName, $mainLabel, ['class' => VC::FM_LB]) }}
                    {{ Form::select($mainName, $mainOptions, $vendorIdSafe, $mainAttrs) }}
                </div>
                <div id="vendor_detail" class="d-none"></div>
            </div>
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::RW }}">
                    @foreach($sideFieldsSafe as $f)
                        @php
                            $fname = data_get($f,'name','field');
                            $flabel = data_get($f,'label') ?? __('No field label available');
                            $foptions = (array)(data_get($f,'options',[]));
                            $fattrs = (array)(data_get($f,'attrs',[]));
                        @endphp
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::FM_G }}">
                                {{ Form::label($fname, $flabel, ['class' => VC::FM_LB]) }}
                                {{ Form::select($fname, $foptions, null, $fattrs) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="{{ VC::RW }}">
                    @foreach($dateFieldsSafe as $f)
                        @php
                            $dname = data_get($f,'name','date_field');
                            $dlabel = data_get($f,'label') ?? __('No date label available');
                            $dtype = data_get($f,'type','date');
                            $dattrs = (array)(data_get($f,'attrs',[]));
                            $dvalue = data_get($f,'value');
                        @endphp
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::FM_G }}">
                                {{ Form::label($dname, $dlabel, ['class' => VC::FM_LB]) }}
                                @if($dtype === 'date')
                                    {{ Form::date($dname, null, $dattrs) }}
                                @else
                                    <input type="text" name="{{ $dname }}" value="{{ $dvalue }}" @foreach($dattrs as $k=>$v) {{ $k }}="{{ $v }}" @endforeach>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <h5 class="{{ VC::MB3 }}">{{ __('Product & Services') }}</h5>
        <div class="{{ VC::CD }} repeater {{ VC::MB4 }}">
            <div class="item-section {{ VC::PY2 }}">
                <div class="{{ VC::DFL }} {{ VC::JCE }}">
                    <button type="button" data-repeater-create class="{{ VC::BT_PRM }}"><i class="{{ VC::TI_PLS }}"></i> {{ __('Add item') }}</button>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="{{ VC::TB }} {{ VC::MB0 }}" data-repeater-list="items" id="sortable-table">
                        <thead>
                            <tr>
                                @forelse($headersSafe as $th)
                                    <th>{!! $th !!}</th>
                                @empty
                                    <th>{{ __('No headers available') }}</th>
                                @endforelse
                            </tr>
                        </thead>
                        <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                @php
                                    $productBase             = ViewsConstants::PRC.'.product';
                                    $productKebab            = Str::kebab($productBase);
                                    $productResolved         = Route::has($productBase) ? $productBase : (Route::has($productKebab) ? $productKebab : null);
                                    $productUrl              = $productResolved ? route($productResolved) : '#';
                                    $productGuardMsg         = Utility::fetchLinkMessage($lang, ViewsConstants::PRC, 'product_purchase_route_unavailable') ?? 'Purchase product route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <td width="25%">
                                    {{ Form::select(
                                        'item',
                                        $productServices,
                                        '',
                                        [
                                            'class' => VC::FM_CT.' select2 item',
                                            'data-url' => $productUrl,
                                            'data-guard-msg' => $productGuardMsg,
                                            'data-sv-localized' => 'true',
                                            'required' => 'required'
                                        ]
                                    ) }}
                                </td>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script src="{{ asset('assets/js/routes/purchases/product.js') }}" defer></script>
                                @endpush
                                <td>
                                    {{ Form::text('quantity','', ['class' => VC::FM_CT.' quantity','placeholder' => __('Qty'),'required' => 'required']) }}
                                    <span class="unit {{ VC::TXTS_TRP }}"></span>
                                </td>
                                <td>
                                    {{ Form::text('price','', ['class' => VC::FM_CT.' price','placeholder' => __('Price'),'required' => 'required']) }}
                                    <span class="{{ VC::TXTS_TRP }}">{{ $currencySymbol }}</span>
                                </td>
                                <td>
                                    {{ Form::text('discount','', ['class' => VC::FM_CT.' discount','placeholder' => __('Discount')]) }}
                                    <span class="{{ VC::TXTS_TRP }}">{{ $currencySymbol }}</span>
                                </td>
                                <td>
                                    <div class="taxes"></div>
                                    {{ Form::hidden('tax','', ['class'=>'tax']) }}
                                    {{ Form::hidden('itemTaxPrice','', ['class'=>'itemTaxPrice']) }}
                                    {{ Form::hidden('itemTaxRate','', ['class'=>'itemTaxRate']) }}
                                </td>
                                <td class="text-end amount">0.00</td>
                                <td>
                                    <button type="button" class="{{ VC::TRS_DNG }}" data-repeater-delete></button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    {{ Form::textarea('description', null, ['class' => VC::FM_CT.' pro_description','rows' => 2,'placeholder' => __('Description')]) }}
                                </td>
                                <td colspan="5"></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"></td>
                                <td><strong>{{ __('Sub Total') }}</strong></td>
                                <td class="text-end subTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td><strong>{{ __('Discount') }}</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td><strong>{{ __('Tax') }}</strong></td>
                                <td class="text-end totalTax">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td class="blue-text"><strong>{{ __('Total Amount') }}</strong></td>
                                <td class="blue-text text-end totalAmount"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="{{ VC::DFL }} {{ VC::JCE }}">
            <button
                type="button"
                class="{{ VC::BT_LG }} me-2"
                id="purchase-cancel-btn"
                data-url="{{ $purchaseIndexUrl }}"
                data-guard-msg="{{ $purchaseIndexGuardMsg }}"
                data-sv-localized="true"
            >{{ __('Cancel') }}</button>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/purchases/cancel.js') }}" defer></script>
            @endpush
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
        </div>
    {{ Form::close() }}
@endsection
