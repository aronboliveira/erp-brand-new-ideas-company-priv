@php
$user ??= null;
	$currencyAvailable ??= false;
	$lang ??= '';
	$indexName ??= VW::INV . '.index';
	$indexRoute ??= '#';
	$indexGuardMsg ??= '';
	$storeName ??= VW::INV;
	$storeRoute ??= '#';
	$formId ??= 'invoiceCreateForm';
	$storeGuardMsg ??= '';
	try {
		$user = Auth::user();
		$currencyAvailable = !empty($user) && method_exists($user, 'currencySymbol');
		$lang = Utility::fetchUserLang(user: $user) ?? '';
		$indexRoute = Route::has($indexName)
			? (route($indexName) ?? '#')
			: '#';
		$indexGuardMsg = Utility::fetchLinkMessage(
			$lang,
			VW::INV,
			'invoice_index_route_unavailable'
		) ?? 'Invoice list route is unavailable. Please contact technical support or your domain administrator.';
		$storeRoute = Route::has($storeName)
			? (route($storeName) ?? '#')
			: '#';
		$storeGuardMsg = Utility::fetchLinkMessage(
			$lang,
			VW::INV,
			'invoice_store_route_unavailable'
		) ?? 'Invoice create route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in invoices/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in invoices/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in invoices/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(EL::ADM)
@section(YC::ADM_PG_TTL)
	{{__('Invoice Create')}}
@endsection
@section(YC::ADM_BDC)
	<li class="{{ VC::BCI }}">
		<a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
		{{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="{{ VC::BCI }}">
		<a
			id="breadcrumb-invoice-link"
			href="{{ $indexRoute }}"
			{{ $indexRoute === '#' ? 'aria-disabled="true"' : '' }}
			data-url="{{ $indexRoute }}"
			data-guard-msg="{{ base64_encode($indexGuardMsg) }}"
		>
			{{ __('Invoice') }}
		</a>
	</li>
	@push(ST::ADM_SCR_PG)
		<script defer src="{{ asset('assets/js/routes/invoices/createIndex.js') }}"></script>
	@endpush
	<li class="{{ VC::BCI }}">{{__('Invoice Create')}}</li>
@endsection
@push(ST::ADM_SCR_PG)
	<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
	<script async src="{{ asset('assets/js/routes/invoices/lang/create.js') }}"></script>
	<script defer src="{{ asset('js/jquery.repeater.min.js') }}"></script>
	<script defer>
        (() => {
        const dataListenerAdded = "data-listener-added";
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";

        const getLocalizedMessage = (el, msgKey) => {
            let msg = errFb;
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true"
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
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.["en"]?.[msgKey] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };

        const handleErrorDisplay = (el, msgKey) => {
            const message = el ? getLocalizedMessage(el, msgKey) : errFb;
            const hasBootstrap =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap?.Toast;
            if (hasBootstrap) {
            if (!document.querySelector("#error-toast")) {
                const toast = document.createElement("div");
                toast.id = "error-toast";
                toast.className = "toast align-items-center text-bg-danger border-0";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                toast.innerHTML = `
                                    <div class="{{ VC::DFL }}">
                                        <div class="toast-body">${message}</div>
                                        <button type="button"
                                                class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
                                                data-bs-dismiss="toast"
                                                aria-label="{{ __('Close') }}"></button>
                                    </div>`;
                document.body.appendChild(toast);
            }
            new bootstrap.Toast(document.querySelector("#error-toast")).show();
            } else {
            alert(message);
            }
        };

        try {
            if (!window.jQuery) {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
                return;
            }

            const selector = "body";

            if ($(`${selector} .repeater`).length) {
            const $dragAndDrop = $(`${selector} .repeater tbody`).sortable({
                handle: ".sort-handler",
            });

            const $repeater = $(`${selector} .repeater`).repeater({
                initEmpty: false,
                defaultValues: { status: 1 },
                show() {
                try {
                    $(this).slideDown();
                    const fileUploads = $(this).find("input.multi");
                    if (fileUploads.length) {
                    fileUploads.MultiFile({
                        max: 3,
                        accept: "png|jpg|jpeg",
                        max_size: "{{ SettingsConstants::MAX_U_SIZE_DEF }}",
                    });
                    }
                    if ($(".select2").length) {
                    $(".select2").select2();
                    }
                } catch {
                    const el = this;
                    if (el.getAttribute(dataListenerAdded) !== "true") {
                    $(el).on("click", () =>
                        handleErrorDisplay(el, "repeater_show_unavailable")
                    );
                    el.setAttribute(dataListenerAdded, "true");
                    const obs = new MutationObserver((_, o) => {
                        if (!document.body.contains(el)) {
                        $(el).off("click");
                        o.disconnect();
                        }
                    });
                    obs.observe(document.body, { childList: true, subtree: true });
                    }
                }
                },
                hide(deleteElement) {
                try {
                    if (confirm("{{ __('Are you sure you want to delete this element?') }}")) {
                    $(this).slideUp(deleteElement);
                    $(this).remove();
                    let subTotal = 0;
                    $(".amount").each((_i, amt) => {
                        subTotal += parseFloat($(amt).html()) || 0;
                    });
                    $(".subTotal, .totalAmount").html(subTotal.toFixed(2));
                    }
                } catch {
                    const el = this;
                    if (el.getAttribute(dataListenerAdded) !== "true") {
                    $(el).on("click", () =>
                        handleErrorDisplay(el, "repeater_hide_unavailable")
                    );
                    el.setAttribute(dataListenerAdded, "true");
                    const obs = new MutationObserver((_, o) => {
                        if (!document.body.contains(el)) {
                        $(el).off("click");
                        o.disconnect();
                        }
                    });
                    obs.observe(document.body, { childList: true, subtree: true });
                    }
                }
                },
                ready(setIndexes) {
                $dragAndDrop.on("drop", setIndexes);
                },
                isFirstItemUndeletable: true,
            });

            const value = $(`${selector} .repeater`).attr("data-value");
            if (value) {
                try {
                $repeater.setList(JSON.parse(value));
                } catch {
                // ignore initialization parse errors
                }
            }
            }

            $(document).on("change", "#customer", function () {
            try {
                $("#customer_detail").removeClass("d-none").addClass("d-block");
                $("#customer-box").removeClass("d-block").addClass("d-none");
                const id = $(this).val();
                const url = $(this).data("url");
                jQuery.ajax({
                url,
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("#token").val() },
                data: { id },
                cache: false,
                success(data) {
                    try {
                    if (data) {
                        $("#customer_detail").html(data);
                    } else {
                        $("#customer-box").removeClass("d-none").addClass("d-block");
                        $("#customer_detail").removeClass("d-block").addClass("d-none");
                    }
                    } catch {
                    const el = this;
                    if (el.getAttribute(dataListenerAdded) !== "true") {
                        $(el).on("click", () =>
                        handleErrorDisplay(el, "customer_change_unavailable")
                        );
                        el.setAttribute(dataListenerAdded, "true");
                        const obs = new MutationObserver((_, o) => {
                        if (!document.body.contains(el)) {
                            $(el).off("click");
                            o.disconnect();
                        }
                        });
                        obs.observe(document.body, { childList: true, subtree: true });
                    }
                    }
                },
                });
            } catch {
                const el = this;
                if (el.getAttribute(dataListenerAdded) !== "true") {
                $(el).on("click", () =>
                    handleErrorDisplay(el, "customer_change_unavailable")
                );
                el.setAttribute(dataListenerAdded, "true");
                const obs = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                    $(el).off("click");
                    o.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
                }
            }
            });

            $(document).on("click", "#remove", function () {
            try {
                $("#customer-box").removeClass("d-none").addClass("d-block");
                $("#customer_detail").removeClass("d-block").addClass("d-none");
            } catch {
                const el = this;
                if (el.getAttribute(dataListenerAdded) !== "true") {
                $(el).on("click", () =>
                    handleErrorDisplay(el, "customer_remove_unavailable")
                );
                el.setAttribute(dataListenerAdded, "true");
                const obs = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                    $(el).off("click");
                    o.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
                }
            }
            });

            $(document).on("change", ".item", function () {
            try {
                const $el = $(this);
                const itemId = $el.val();
                const url = $el.data("url");
                jQuery.ajax({
                url,
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("#token").val() },
                data: { product_id: itemId },
                cache: false,
                success(data) {
                    try {
                    const item = JSON.parse(data);
                    const $row = $el.closest("tr");
                    $row.find(".quantity").val(1);
                    $row.find(".price").val(item.product.sale_price);
                    $row.find(".pro_description").val(item.product.description);

                    let taxesHtml = "";
                    let totalRate = 0;
                    const taxIds = [];
                    if (item.taxes?.length) {
                        item.taxes.forEach(t => {
                        taxesHtml +=
                            `<span class="badge {{ VC::BG_P }} {{ VC::MT1 }} {{ VC::MR2 }}">` +
                            `${t.name} (${t.rate}%)</span>`;
                        taxIds.push(t.id);
                        totalRate += parseFloat(t.rate) || 0;
                        });
                    } else {
                        taxesHtml = "-";
                    }

                    const taxPrice = (totalRate / 100) * item.product.sale_price || 0;
                    $row.find(".itemTaxPrice").val(taxPrice.toFixed(2));
                    $row.find(".itemTaxRate").val(totalRate.toFixed(2));
                    $row.find(".taxes").html(taxesHtml);
                    $row.find(".tax").val(taxIds);
                    $row.find(".unit").html(item.unit);
                    $row.find(".discount").val(0);

                    let subTotal = 0;
                    let totalPrice = 0;
                    let totalTaxPrice = 0;
                    let totalDiscount = 0;

                    $(".amount").each((_i, amt) => {
                        subTotal += parseFloat($(amt).html()) || 0;
                    });
                    $(".price").each((_i, prc) => {
                        totalPrice += parseFloat(prc.value) || 0;
                    });
                    $(".itemTaxPrice").each((_i, tx) => {
                        totalTaxPrice += parseFloat(tx.value) || 0;
                    });
                    $(".discount").each((_i, dc) => {
                        totalDiscount += parseFloat(dc.value) || 0;
                    });

                    $(".subTotal").html(totalPrice.toFixed(2));
                    $(".totalTax").html(totalTaxPrice.toFixed(2));
                    $(".totalAmount").html(
                        (totalPrice - totalDiscount + totalTaxPrice).toFixed(2)
                    );
                    } catch {
                    const el = this;
                    if (el.getAttribute(dataListenerAdded) !== "true") {
                        $(el).on("click", () =>
                        handleErrorDisplay(el, "item_change_unavailable")
                        );
                        el.setAttribute(dataListenerAdded, "true");
                        const obs = new MutationObserver((_, o) => {
                        if (!document.body.contains(el)) {
                            $(el).off("click");
                            o.disconnect();
                        }
                        });
                        obs.observe(document.body, { childList: true, subtree: true });
                    }
                    }
                },
                });
            } catch {
                const el = this;
                if (el.getAttribute(dataListenerAdded) !== "true") {
                $(el).on("click", () =>
                    handleErrorDisplay(el, "item_change_unavailable")
                );
                el.setAttribute(dataListenerAdded, "true");
                const obs = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                    $(el).off("click");
                    o.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
                }
            }
            });

            const calculateTotals = $row => {
            const qty = parseFloat($row.find(".quantity").val()) || 0;
            const prc = parseFloat($row.find(".price").val()) || 0;
            const disc = parseFloat($row.find(".discount").val()) || 0;
            const net = qty * prc - disc;
            const rate = parseFloat($row.find(".itemTaxRate").val()) || 0;
            const taxPrice = (rate / 100) * net;
            $row.find(".itemTaxPrice").val(taxPrice.toFixed(2));
            $row.find(".amount").html((net + taxPrice).toFixed(2));

            let totalTax = 0;
            let totalPrice = 0;
            let subTotal = 0;

            $(".itemTaxPrice").each(
                (_i, tx) => (totalTax += parseFloat(tx.value) || 0)
            );
            $(".price").each((_i, pr) => {
                const q = parseFloat($(".quantity").eq(_i).val()) || 0;
                totalPrice += (parseFloat(pr.value) || 0) * q;
            });
            $(".amount").each(
                (_i, amt) => (subTotal += parseFloat($(amt).html()) || 0)
            );

            $(".subTotal").html(totalPrice.toFixed(2));
            $(".totalTax").html(totalTax.toFixed(2));
            $(".totalAmount").html(subTotal.toFixed(2));
            };

            $(document).on("keyup change", ".quantity, .price, .discount", function () {
            try {
                calculateTotals($(this).closest("tr"));
            } catch {
                const el = this;
                if (el.getAttribute(dataListenerAdded) !== "true") {
                el.addEventListener("pointerup", () =>
                    handleErrorDisplay(el, "calculation_unavailable")
                );
                el.setAttribute(dataListenerAdded, "true");
                const obs = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                    el.removeEventListener("pointerup", handleErrorDisplay);
                    o.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
                }
            }
            });

            const customerId = parseInt("{{$customer_id}}", 10) || 0;
            if (customerId > 0) {
            try {
                $("#customer").val(customerId).trigger("change");
            } catch {
                const el = document.querySelector("#customer");
                if (el && el.getAttribute(dataListenerAdded) !== "true") {
                el.addEventListener("click", () =>
                    handleErrorDisplay(el, "customer_initial_unavailable")
                );
                el.setAttribute(dataListenerAdded, "true");
                const obs = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                    el.removeEventListener("click", handleErrorDisplay);
                    o.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
                }
            }
            }
            $(document).on("click", "[data-repeater-delete]", () => {
            $(".price").trigger("change");
            $(".discount").trigger("change");
            });
        } catch (e) {
            if (
                window.location.hostname === "localhost" ||
                window.location.hostname === "127.0.0.1"
            ) {
                console.error("Initialization failed", e);
            }
        }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        @php
            try {
                $invoiceStoreBase        = VW::INV;
                $invoiceStoreKebab       = Str::kebab($invoiceStoreBase);
                $invoiceStoreResolved    = Route::has($invoiceStoreBase) ? $invoiceStoreBase : (Route::has($invoiceStoreKebab) ? $invoiceStoreKebab : null);
                $invoiceStoreUrl         = $invoiceStoreResolved ? route($invoiceStoreResolved) : '#';
                $invoiceStoreFormId      = 'invoice-store-form';
                $invoiceStoreGuardMsg    = Utility::fetchLinkMessage($lang, VW::INV, 'store_invoice_route_unavailable') ?? 'Store invoice route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('invoices/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        {{ Form::open([
            'url'               => $invoiceStoreUrl,
            'method'            => 'POST',
            'class'             => 'w-100',
            'id'                => $invoiceStoreFormId,
            'data-url'          => $invoiceStoreUrl,
            'data-guard-msg'    => $invoiceStoreGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            <div class="{{ VC::C12 }}">
                {{ Form::hidden('_token', csrf_token(), ['id' => 'token']) }}
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_MT }}">
                        <div class="{{ VC::RW }}">
                            <div class="col-12 col-sm-12 {{ VC::CM6 }} {{ VC::CL6 ?? 'col-lg-6' }}">
                                <div class="{{ VC::FM_G }}" id="customer-box">
                                    {{ Form::label('customer_id', __('Customer'), ['class' => VC::FM_LB]) }}
                                    @php
                                        try {
                                            $routeName      = VW::INV . '.customer';
                                            $customerRoute  = Route::has($routeName)
                                                ? route($routeName)
                                                : '#';
                                            $guardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                VW::INV,
                                                'invoice_customer_route_unavailable'
                                            ) ?? 'Invoice customer route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('invoices/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    {{ Form::select(
                                        'customer_id',
                                        Utility::isFilled($customers) ? $customers : [__('No customer available.' ?? [])],
                                        $customer_id,
                                        [
                                        'class'         => VC::FM_CT . ' select2',
                                        'id'            => 'customer',
                                        'data-url'      => $customerRoute,
                                        'data-guard-msg'=> $guardMsg,
                                        'required'      => 'required'
                                        ]
                                    )}}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/invoices/customers/store.js') }}"></script>
                                    @endpush
                                </div>
                                <div id="customer_detail" class="d-none"></div>
                            </div>
                            <div class="col-12 col-sm-12 {{ VC::CM6 }} {{ VC::CL6 ?? 'col-lg-6' }}">
                                <div class="{{ VC::RW }}">
                                    @php
                                        try {
                                            $fields = [
                                                ['name'=>'issue_date','type'=>'date','label'=>__('Issue Date'),'cols'=>6,'required'=>true],
                                                ['name'=>'due_date','type'=>'date','label'=>__('Due Date'),'cols'=>6,'required'=>true],
                                                ['name'=>'invoice_number','type'=>'readonly','label'=>__('Invoice Number'),'cols'=>6,'value'=> !empty($invoice_number) ? $invoice_number : __('ERROR')],
                                                ['name'=>'category_id','type'=>'select','label'=>__('Category'),'cols'=>6,'options'=> !empty($category) ? $category : [__('No category available.')],'attrs'=>['class'=>VC::FM_CT . ' select2','required'=>'required']],
                                                ['name'=>'ref_number','type'=>'text','label'=>__('Ref Number'),'cols'=>6,'icon'=>'<span><i class="ti ti-joint"></i></span>','attrs'=>['class'=>VC::FM_CT]],
                                            ];
                                        } catch (\Throwable $e) {
                                            \Log::error('invoices/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    @foreach($fields as $f)
                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                                                @php
                                                    try {
                                                        $attrs = $f['attrs'] ?? [];
                                                        if (!empty($f['required']))
                                                            $attrs['required'] = 'required';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('invoices/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp

                                                @if(!empty($f['icon']))
                                                    <div class="form-icon-user">{!! $f['icon'] !!}
                                                @endif

                                                @if($f['type'] === 'date')
                                                    {{ Form::date($f['name'], null, array_merge(['class'=>VC::FM_CT], $attrs)) }}
                                                @elseif($f['type'] === 'select')
                                                    {{ Form::select($f['name'], $f['options'], null, $attrs) }}
                                                @elseif($f['type'] === 'readonly')
                                                    <input type="text" class="{{ VC::FM_CT }}" value="{{ $f['value'] }}" readonly>
                                                @else
                                                    {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                                                @endif

                                                @if(!empty($f['icon']))
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(!$customFields->isEmpty())
                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                                                @include(VW::CST_FD . '.formBuilder')
                                            </div>
                                        </div>
                                    @else
                                        <div class="{{ VC::CM6 }} {{ VC::TXT_MT }}">{{ __('No custom fields available.') }}</div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::C12 }}">
                <h5 class="{{ VC::MB4 }}">{{ __('Product & Services') }}</h5>
                <div class="{{ VC::CD }} repeater">
                    <div class="item-section {{ VC::PY2 }}">
                        <div class="{{ VC::RW }} {{ VC::JCE }}">
                            <div class="all-button-box me-2">
                                <a href="#"
                                data-repeater-create
                                class="{{ VC::BT_SM_PM }}"
                                data-bs-toggle="modal"
                                data-target="#add-bank">
                                    <i class="{{ VC::TI_PLS }}"></i> {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="{{ VC::CD_MT }}">
                        <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB }} mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Items') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Discount') }}</th>
                                        <th>{{ __('Tax') }} (%)</th>
                                        <th class="{{ VC::TX_END }}">{{ __('Amount') }}<br><small class="{{ VC::TX_DNG }} fw-bold">{{ __('after tax & discount') }}</small></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="ui-sortable" data-repeater-item>
                                    <tr>
                                        <td class="{{ VC::FM_G }} pt-0" style="width:25%">
                                            @php
                                                try {
                                                    $routeName      = VW::INV . '.product';
                                                    $productRoute   = Route::has($routeName)
                                                        ? route($routeName)
                                                        : (Route::has(Str::kebab($routeName))
                                                            ? route(Str::kebab($routeName))
                                                            : '#');
                                                    $guardMsg       = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::INV,
                                                        'invoice_product_route_unavailable'
                                                    ) ?? 'Invoice product route is unavailable. Please contact technical support or your domain administrator.';
                                                } catch (\Throwable $e) {
                                                    \Log::error('invoices/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            {{ Form::select('item', Utility::isFilled($product_services) ? $product_services : [__('No product service available.' ?? [])], '', [
                                                'class'         => VC::FM_CT . ' select2 item',
                                                'data-url'      => $productRoute,
                                                'data-guard-msg'=> $guardMsg,
                                                'required'      => 'required',
                                            ]) }}
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/invoices/selectItem.js') }}"></script>
                                            @endpush
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                {{ Form::text('quantity', '', ['class'=>VC::FM_CT . ' quantity','required'=>'required','placeholder'=>__('Qty')]) }}
                                                <span class="unit {{ VC::TXTS_TRP }}"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                {{ Form::text('price', '', ['class'=>VC::FM_CT . ' price','required'=>'required','placeholder'=>__('Price')]) }}
                                                <span class="{{ VC::TXTS_TRP }}">{{ $currencyAvailable ? $user?->currencySymbol() : __('Failed to get currency') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                {{ Form::text('discount', '', ['class'=>VC::FM_CT . ' discount','required'=>'required','placeholder'=>__('Discount')]) }}
                                                <span class="{{ VC::TXTS_TRP }}">{{ $currencyAvailable ? $user?->currencySymbol() : __('Failed to get currency') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class'=>'tax']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class'=>'itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class'=>'itemTaxRate']) }}
                                            </div>
                                        </td>
                                        <td class="{{ VC::TX_END }} amount">0.00</td>
                                        <td>
                                            <a href="#" class="{{ VC::TRS_PARA }}" data-repeater-delete></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>2,'placeholder'=>__('Description')]) }}
                                            </div>
                                        </td>
                                        <td colspan="5"></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td><strong>{{ __('Sub Total') }} ({{ $currencyAvailable ? $user?->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                        <td class="{{ VC::TX_END }} subTotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td><strong>{{ __('Discount') }} ({{ $currencyAvailable ? $user?->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                        <td class="{{ VC::TX_END }} totalDiscount">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td><strong>{{ __('Tax') }} ({{ $currencyAvailable ? $user?->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                        <td class="{{ VC::TX_END }} totalTax">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td class="{{ VC::TX_PM }}"><strong>{{ __('Total Amount') }} ({{ $currencyAvailable ? $user?->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                        <td class="{{ VC::TX_END }} totalAmount {{ VC::TX_PM }}"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::C12 }} mt-3">
                <a href="{{ $indexRoute }}" data-guard-msg="{{ base64_encode($indexGuardMsg) }}" data-url="{{ $indexRoute }}" class="{{ VC::BT_LG }}" data-listener-alias="cancel-invoice">{{ __('Cancel') }}</a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer src="{{ asset('assets/js/routes/invoices/createIndexAnchor.js') }}"></script>
                @endpush
                <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
            </div>
        {{ Form::close() }}
        <script defer src="{{ asset('assets/js/routes/invoices/store.js') }}"></script>
    </div>
@endsection
