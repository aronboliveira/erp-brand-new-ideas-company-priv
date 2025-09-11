
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        SettingsConstants,
        StacksConstants as ST,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route, URL, Request};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(EL::ADM)
@section(YW::ADM_PG_TTL)
    {{__('Expense Create')}}
@endsection
@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(VW::EXP.'.index')}}">{{__('Expense')}}</a></li>
    <li class="breadcrumb-item">{{__('Expense Create')}}</li>
@endsection
@push(ST::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/expenses/lang/create.js') }}"></script>
    <script defer src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/expenses/createRepeater.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/expenses/createSelect.js') }}"></script>
    <script defer>
        (() => {
            const errFb = '# ERROR';
            const dataClientLocalized = 'data-client-localized';
            const dataGuardMsg = 'data-guard-msg';
            const langKey = 'erp-np-lang';
        
            const getMsg = (key, el) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true'
                || el.getAttribute(dataClientLocalized) === 'true') {
                msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
                let lang = (sessionStorage.getItem(langKey)
                        ?? document.documentElement.lang
                        ?? 'en').toLowerCase().replace(/_/g,'-');
                lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                msg = translations?.[lang]?.[key]
                    ?? el.getAttribute(dataGuardMsg)
                    ?? translations?.['en']?.[key]
                    ?? errFb;
                if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
                }
            }
            return msg;
            };
        
            const showError = message => {
            try {
                let container = document.getElementById('toast-container');
                if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
                }
                const bsLink = document.querySelector('link[href*="bootstrap"]');
                if (bsLink && window.bootstrap) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role','alert');
                toast.setAttribute('aria-live','assertive');
                toast.setAttribute('aria-atomic','true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                body.textContent = message;
                toast.appendChild(body);
                container.appendChild(toast);
                bootstrap.Toast.getOrCreateInstance(toast).show();
                } else {
                alert(message);
                }
            } catch {
                alert(message);
            }
            };
        
            let errorMsg = '';
            const onPointerUp = () => {
            if (errorMsg) {
                showError(errorMsg);
                errorMsg = '';
            }
            };
            document.addEventListener('pointerup', onPointerUp);
            new MutationObserver((m, obs) => {
            m.forEach(mut => mut.removedNodes.forEach(n => {
                if (n === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
                }
            }));
            }).observe(document.body, { childList:true, subtree:true });
        
            document.addEventListener('DOMContentLoaded', () => {
            const sel = 'body .repeater';
            const container = document.querySelector(sel);
            if (!container) return;
        
            let drag = null;
            try {
                drag = $(sel + ' tbody').sortable({ handle: '.sort-handler' });
            } catch {
                errorMsg = getMsg('calculation_error', container);
            }
        
            let rep = null;
            try {
                rep = $(sel).repeater({
                initEmpty: true,
                defaultValues: { status:1 },
                show() {
                    $(this).slideDown();
                    try {
                    const multi = $(this).find('input.multi');
                    if (multi.length) {
                        multi.MultiFile({
                        max: 3,
                        accept: 'png|jpg|jpeg',
                        max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }}
                        });
                    }
                    JsSearchBox();
                    $('.select2').length && $('.select2').select2();
                    } catch {
                    errorMsg = getMsg('calculation_error', this);
                    }
                },
                hide(delEl) {
                    if (!confirm(getMsg('repeater_delete_confirm', this))) return;
                    $(this).slideUp(delEl);
                    $(this).remove();
                    try {
                    let sub=0;
                    $('.amount').each((i,el)=> sub+=parseFloat($(el).text())||0);
                    $('.subTotal, .totalAmount').text(sub.toFixed(2));
                    } catch {
                    errorMsg = getMsg('calculation_error', this);
                    }
                },
                ready(setIdx) { drag?.on('drop', setIdx); },
                isFirstItemUndeletable:true
                });
                const dataVal = container.getAttribute('data-value');
                if (dataVal) {
                const list = JSON.parse(dataVal);
                rep.setList(list);
                list.forEach(item => {
                    const tr = $(`#sortable-table .id[value="${item.id}"]`).parent();
                    tr.find('.item').val(item.product_id);
                    changeItem(tr.find('.item'));
                });
                }
            } catch {
                errorMsg = getMsg('calculation_error', container);
            }
        
            const onVendorChange = async () => {
                try {
                $('#vendor_detail').removeClass('d-none').addClass('d-block');
                $('#vendor-box').removeClass('d-block').addClass('d-none');
                const id = $('#vendor').val(), url = $('#vendor').data('url');
                const resp = await $.ajax({
                    url, type:'POST',
                    headers:{ 'X-CSRF-TOKEN':$('#token').val() },
                    data:{ id }
                });
                if (resp) $('#vendor_detail').html(resp);
                else throw 0;
                } catch {
                $('#vendor-detail').toggleClass('d-none d-block', false);
                $('#vendor-box').toggleClass('d-block d-none', false);
                errorMsg = getMsg('vendor_detail_fetch_failed', document.body);
                }
            };
            $('#vendor').on('change', onVendorChange);
            $('#remove').on('click', () => {
                $('#vendor-box').removeClass('d-none').addClass('d-block');
                $('#vendor_detail').removeClass('d-block').addClass('d-none');
            });
        
            const billId = '{{ $bill->id }}';
            async function changeItem(el) {
                try {
                const pid = el.val(), url = el.data('url');
                const itemData = JSON.parse(await $.ajax({
                    url, type:'POST',
                    headers:{ 'X-CSRF-TOKEN':$('#token').val() },
                    data:{ product_id: pid }
                }));
                const billItems = JSON.parse(await $.ajax({
                    url: '{{route(VW::BIL.'.items')}}',
                    type:'GET',
                    headers:{ 'X-CSRF-TOKEN':$('#token').val() },
                    data:{ bill_id: billId, product_id: pid }
                })) || null;
        
                const row = el.closest('tr');
                const qty = billItems?.quantity ?? 1;
                const price = billItems?.price ?? itemData.product.purchase_price;
                const discount = billItems?.discount ?? 0;
                row.find('.quantity').val(qty);
                row.find('.price').val(price);
                row.find('.discount').val(discount);
                row.find('.pro_description').val(billItems?.description ?? itemData.product.description);
        
                const totalRate = (itemData.taxes || []).reduce((s,t)=>{
                    return s + parseFloat(t.rate);
                },0);
                const taxHtml = (itemData.taxes || []).map(t=>
                    `<span class="badge bg-primary p-2 px-3 rounded mt-1 mr-1">${t.name} (${t.rate}%)</span>`
                ).join('') || '-';
                const taxPrice = ((totalRate/100)*( (price*qty) - discount ));
                row.find('.itemTaxPrice').val(taxPrice.toFixed(2));
                row.find('.itemTaxRate').val(totalRate.toFixed(2));
                row.find('.taxes').html(taxHtml);
                row.find('.tax').val((itemData.taxes||[]).map(t=>t.id));
                row.find('.unit').html(itemData.unit);
        
                $('.quantity, .price, .discount, .accountAmount').trigger('keyup change');
                } catch {
                errorMsg = getMsg('item_data_fetch_failed', document.body);
                }
            }
            $(document).on('change', '.item', function(){ changeItem($(this)); });
        
            const recalc = () => {
                try {
                let totalPrice=0, totalTax=0, totalAccount=0;
                $('.quantity').each((i,el)=> {
                    const q=+$(el).val()||0;
                    const p=+$('.price').eq(i).val()||0;
                    const d=+$('.discount').eq(i).val()||0;
                    const sub=(q*p)-d;
                    const r=+$('.itemTaxRate').eq(i).val()||0;
                    const t=(r/100)*sub;
                    $('.itemTaxPrice').eq(i).val(t.toFixed(2));
                    $('.amount').eq(i).text((sub+t).toFixed(2));
                    totalPrice+=sub;
                    totalTax+=t;
                });
                $('.accountAmount').each((i,el)=> {
                    const v=+$(el).val()||0;
                    $('.accountamount').eq(i).text(v.toFixed(2));
                    totalAccount+=v;
                });
                $('.subTotal').text((totalPrice+totalAccount).toFixed(2));
                $('.totalTax').text(totalTax.toFixed(2));
                $('.totalAmount').text((totalPrice - $('.discount').toArray().reduce((s,e)=>s+(+e.value||0),0) + totalTax + totalAccount).toFixed(2));
                } catch {
                errorMsg = getMsg('calculation_error', document.body);
                }
            };
            $(document).on('keyup change', '.quantity, .price, .discount, .accountAmount', recalc);
        
            $(document).on('click','[data-repeater-delete]', async function(){
                if (!confirm(getMsg('repeater_delete_confirm', this))) return;
                const row = $(this).closest('tr');
                const id = +row.find('.id').val()||0;
                const amt = +row.find('.amount').text()||0;
                try {
                await $.ajax({
                    url: '{{route(VW::BIL.".product.destroy")}}',
                    type: 'POST',
                    headers:{ 'X-CSRF-TOKEN':$('#token').val() },
                    data: { id, amount: amt }
                });
                } catch {
                errorMsg = getMsg('product_destroy_failed', this);
                }
                recalc();
            });
        
            $('.accountAmount').trigger('keyup');
            });
        })();
    </script>
@endpush
@php
    $formId           = 'expense-create-form';
    $storeBase        = VW::EXP;
    $storeKebab       = Str::kebab($storeBase);
    $storeResolved    = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl         = $storeResolved ? route($storeResolved) : '#';
    $storeGuardMsg    = Utility::fetchLinkMessage($lang, VW::EXP, 'store_expense_route_unavailable') ?? 'Store expense route is unavailable. Please contact technical support or your domain administrator.';

    $typeParam        = (string) Request::get('type', 'employee');
    $isEmployeeType   = $typeParam === 'employee';
    $isCustomerType   = $typeParam === 'customer';
    $isVendorType     = $typeParam === 'vendor';

    $employeesIsList  = (is_array($employees ?? null) && count($employees ?? []) > 0) || (($employees ?? null) instanceof Collection && $employees->isNotEmpty());
    $customersIsList  = (is_array($customers ?? null) && count($customers ?? []) > 0) || (($customers ?? null) instanceof Collection && $customers->isNotEmpty());
    $vendorsIsList    = (is_array($vendors   ?? null) && count($vendors   ?? []) > 0) || (($vendors   ?? null) instanceof Collection && $vendors->isNotEmpty());
    $categoryIsList   = (is_array($category  ?? null) && count($category  ?? []) > 0) || (($category  ?? null) instanceof Collection && $category->isNotEmpty());
    $accountsIsList   = (is_array($accounts  ?? null) && count($accounts  ?? []) > 0) || (($accounts  ?? null) instanceof Collection && $accounts->isNotEmpty());
    $prodSvcIsList    = (is_array($product_services ?? null) && count($product_services ?? []) > 0) || (($product_services ?? null) instanceof Collection && $product_services->isNotEmpty());
    $chartAccIsList   = (is_array($chartAccounts ?? null) && count($chartAccounts ?? []) > 0) || (($chartAccounts ?? null) instanceof Collection && $chartAccounts->isNotEmpty());

    $employeeOptions  = $employeesIsList ? (is_array($employees) ? $employees : $employees->toArray()) : [__('No employees available')];
    $customerOptions  = $customersIsList ? (is_array($customers) ? $customers : $customers->toArray()) : [__('No customers available')];
    $vendorOptions    = $vendorsIsList   ? (is_array($vendors)   ? $vendors   : $vendors->toArray())   : [__('No vendors available')];
    $categoryOptions  = $categoryIsList  ? (is_array($category)  ? $category  : $category->toArray())  : [__('No categories available')];
    $accountOptions   = $accountsIsList  ? (is_array($accounts)  ? $accounts  : $accounts->toArray())  : [__('No accounts available')];
    $prodSvcOptions   = $prodSvcIsList   ? (is_array($product_services) ? $product_services : $product_services->toArray()) : [__('No items available')];
    $chartAccOptions  = $chartAccIsList  ? (is_array($chartAccounts)    ? $chartAccounts    : $chartAccounts->toArray())    : [__('No chart accounts available')];

    $empUrlBase       = VW::EXP . '.employee';
    $empUrlKebab      = Str::kebab($empUrlBase);
    $empUrlResolved   = Route::has($empUrlBase) ? $empUrlBase : (Route::has($empUrlKebab) ? $empUrlKebab : null);
    $empUrl           = $empUrlResolved ? route($empUrlResolved) : '#';
    $empGuardMsg      = Utility::fetchLinkMessage($lang, VW::EXP, 'employee_route_unavailable') ?? 'Employee endpoint is unavailable. Please contact technical support or your domain administrator.';

    $cusUrlBase       = VW::EXP . '.customer';
    $cusUrlKebab      = Str::kebab($cusUrlBase);
    $cusUrlResolved   = Route::has($cusUrlBase) ? $cusUrlBase : (Route::has($cusUrlKebab) ? $cusUrlKebab : null);
    $cusUrl           = $cusUrlResolved ? route($cusUrlResolved) : '#';
    $cusGuardMsg      = Utility::fetchLinkMessage($lang, VW::EXP, 'customer_route_unavailable') ?? 'Customer endpoint is unavailable. Please contact technical support or your domain administrator.';

    $venUrlBase       = VW::EXP . '.vendor';
    $venUrlKebab      = Str::kebab($venUrlBase);
    $venUrlResolved   = Route::has($venUrlBase) ? $venUrlBase : (Route::has($venUrlKebab) ? $venUrlKebab : null);
    $venUrl           = $venUrlResolved ? route($venUrlResolved) : '#';
    $venGuardMsg      = Utility::fetchLinkMessage($lang, VW::EXP, 'vendor_route_unavailable') ?? 'Vendor endpoint is unavailable. Please contact technical support or your domain administrator.';

    $prodUrlBase      = VW::EXP . '.product';
    $prodUrlKebab     = Str::kebab($prodUrlBase);
    $prodUrlResolved  = Route::has($prodUrlBase) ? $prodUrlBase : (Route::has($prodUrlKebab) ? $prodUrlKebab : null);
    $prodUrl          = $prodUrlResolved ? route($prodUrlResolved) : '#';
    $prodGuardMsg     = Utility::fetchLinkMessage($lang, VW::EXP, 'product_route_unavailable') ?? 'Product endpoint is unavailable. Please contact technical support or your domain administrator.';

    $indexBase        = VW::EXP . '.index';
    $indexKebab       = Str::kebab($indexBase);
    $indexResolved    = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
    $indexUrl         = $indexResolved ? route($indexResolved) : '#';
    $indexGuardMsg    = Utility::fetchLinkMessage($lang, VW::EXP, 'index_expense_route_unavailable') ?? 'Expense index route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        {{ Form::open([
            'url'               => $storeUrl,
            'id'                => $formId,
            'class'             => 'w-100',
            'data-url'          => $storeUrl,
            'data-guard-msg'    => $storeGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            <div class="{{ VC::C12 }}">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::C12 }}">
                                    <div class="{{ VC::FM_CHK_IL_GP }}">
                                        <input type="radio" id="employee_radio" value="employee" name="type" class="form-check-input" {{ $isEmployeeType ? 'checked' : '' }}>
                                        <label class="form-check-label" for="employee_radio">{{ __('Employee') }}</label>
                                    </div>
                                    <div class="{{ VC::FM_CHK_IL_GP }}">
                                        <input type="radio" id="customer_radio" value="customer" name="type" class="form-check-input" {{ $isCustomerType ? 'checked' : '' }}>
                                        <label class="form-check-label" for="customer_radio">{{ __('Customer') }}</label>
                                    </div>
                                    <div class="{{ VC::FM_CHK_IL_GP }}">
                                        <input type="radio" id="vendor_radio" value="vendor" name="type" class="form-check-input" {{ $isVendorType ? 'checked' : '' }}>
                                        <label class="form-check-label" for="vendor_radio">{{ __('Vendor') }}</label>
                                    </div>
                                </div>

                                <div class="col employee {{ $isEmployeeType ? '' : 'd-none' }}">
                                    <div class="form-group" id="employee-box">
                                        {{ Form::label('employee_id', __('Payee'), ['class' => VC::FM_LB]) }}
                                        {{ Form::select(
                                            'employee_id',
                                            $employeeOptions,
                                            null,
                                            array_merge([
                                                'class'         => VC::FM_CT_SL,
                                                'id'            => 'employee',
                                                'data-url'      => $empUrl,
                                                'data-guard-msg'=> $empGuardMsg
                                            ], $employeesIsList ? [] : ['disabled' => 'disabled'])
                                        ) }}
                                    </div>
                                    <div id="employee_detail" class="d-none"></div>
                                </div>

                                <div class="col customer {{ $isCustomerType ? '' : 'd-none' }}">
                                    <div class="form-group" id="customer-box">
                                        {{ Form::label('customer_id', __('Payee'), ['class' => VC::FM_LB]) }}
                                        {{ Form::select(
                                            'customer_id',
                                            $customerOptions,
                                            null,
                                            array_merge([
                                                'class'         => VC::FM_CT_SL,
                                                'id'            => 'customer',
                                                'data-url'      => $cusUrl,
                                                'data-guard-msg'=> $cusGuardMsg
                                            ], $customersIsList ? [] : ['disabled' => 'disabled'])
                                        ) }}
                                    </div>
                                    <div id="customer_detail" class="d-none"></div>
                                </div>

                                <div class="col vendor {{ $isVendorType ? '' : 'd-none' }}">
                                    <div class="form-group" id="vendor-box">
                                        {{ Form::label('vendor_id', __('Payee'), ['class' => VC::FM_LB]) }}
                                        {{ Form::select(
                                            'vendor_id',
                                            $vendorOptions,
                                            $Id ?? null,
                                            array_merge([
                                                'class'         => VC::FM_CT_SL,
                                                'id'            => 'vendor',
                                                'data-url'      => $venUrl,
                                                'data-guard-msg'=> $venGuardMsg
                                            ], $vendorsIsList ? [] : ['disabled' => 'disabled'])
                                        ) }}
                                    </div>
                                    <div id="vendor_detail" class="d-none"></div>
                                </div>
                            </div>

                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM6 }}">
                                        <div class="form-group">
                                            {{ Form::label('payment_date', __('Payment Date'), ['class' => VC::FM_LB]) }}
                                            {{ Form::date('payment_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                                        </div>
                                        @error('payment_date')
                                            <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="form-group">
                                            {{ Form::label('category_id', __('Category'), ['class' => VC::FM_LB]) }}
                                            {{ Form::select(
                                                'category_id',
                                                $categoryOptions,
                                                null,
                                                array_merge(['class' => VC::FM_CT_SL], $categoryIsList ? [] : ['disabled' => 'disabled'])
                                            ) }}
                                        </div>
                                        @unless($categoryIsList)
                                            <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No categories available.') }}</div>
                                        @endunless
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="form-group">
                                            {{ Form::label('account_id', __('Account'), ['class' => VC::FM_LB]) }}
                                            {{ Form::select(
                                                'account_id',
                                                $accountOptions,
                                                null,
                                                array_merge(['class' => VC::FM_CT, 'required' => 'required'], $accountsIsList ? [] : ['disabled' => 'disabled'])
                                            ) }}
                                        </div>
                                        @unless($accountsIsList)
                                            <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No accounts available.') }}</div>
                                        @endunless
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::C12 }}">
                <h5 class="d-inline-block mb-4">{{ __('Product & Services') }}</h5>
                <div class="card repeater">
                    <div class="item-section py-2">
                        <div class="{{ VC::RW }} justify-content-between align-items-center">
                            <div class="{{ VC::CM12 }} d-flex align-items-center justify-content-between justify-content-md-end">
                                <div class="all-button-box me-2">
                                    <a href="#" data-repeater-create class="{{ VC::BT_PRM }}">
                                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                                <thead>
                                <tr>
                                    <th width="20%">{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Tax') }} (%)</th>
                                    <th class="text-end">
                                        {{ __('Amount') }}
                                        <br><small class="text-danger font-bold">{{ __('after tax & discount') }}</small>
                                    </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody class="ui-sortable" data-repeater-item>
                                @php
                                    $isCurrencySymbolAvailable = method_exists($user, 'currencySymbol');
                                @endphp
                                <tr>
                                    <td width="25%" class="form-group pt-0">
                                        {{ Form::select(
                                            'item',
                                            $prodSvcOptions,
                                            '',
                                            array_merge([
                                                'class'         => VC::FM_CT.' select2 item',
                                                'data-url'      => $prodUrl,
                                                'data-guard-msg'=> $prodGuardMsg
                                            ], $prodSvcIsList ? [] : ['disabled' => 'disabled'])
                                        ) }}
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('quantity', '', ['class' => VC::FM_CT.' quantity', 'placeholder' => __('Qty')]) }}
                                            <span class="unit input-group-text bg-transparent"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('price', '', ['class' => VC::FM_CT.' price', 'placeholder' => __('Price')]) }}
                                            <span class="input-group-text bg-transparent">{{ $isCurrencySymbolAvailable ? $user->currencySymbol() : __('Failed to get currency') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('discount', '', ['class' => VC::FM_CT.' discount', 'placeholder' => __('Discount')]) }}
                                            <span class="input-group-text bg-transparent">{{ $isCurrencySymbolAvailable ? $user->currencySymbol() : __('Failed to get currency') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => VC::FM_CT.' tax']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => VC::FM_CT.' itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => VC::FM_CT.' itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end amount">0.00</td>
                                    <td>
                                        @can('delete proposal product')
                                            <a href="#" class="{{ VC::TRS_M2 }}" data-repeater-delete></a>
                                        @endcan
                                    </td>
                                </tr>
                                <tr>
                                    <td class="form-group">
                                        {{ Form::select(
                                            'chart_account_id',
                                            $chartAccOptions,
                                            '',
                                            array_merge(['class' => VC::FM_CT.' select2 js-searchBox'], $chartAccIsList ? [] : ['disabled' => 'disabled'])
                                        ) }}
                                    </td>
                                    <td class="form-group">
                                        <div class="input-group">
                                            {{ Form::text('amount', '', ['class' => VC::FM_CT.' accountAmount', 'placeholder' => __('Amount')]) }}
                                            <span class="input-group-text bg-transparent">{{ $isCurrencySymbolAvailable ? $user->currencySymbol() : __('Failed to get currency') }}</span>
                                        </div>
                                    </td>
                                    <td colspan="2" class="form-group">
                                        {{ Form::textarea('description', null, ['class' => VC::FM_CT.' pro_description', 'rows' => 1, 'placeholder' => __('Description')]) }}
                                    </td>
                                    <td></td>
                                    <td class="text-end accountamount">0.00</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td><strong>{{ __('Sub Total') }} ({{ $isCurrencySymbolAvailable ? $user->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td><strong>{{ __('Discount') }} ({{ $isCurrencySymbolAvailable ? $user->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                    <td class="text-end totalDiscount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td><strong>{{ __('Tax') }} ({{ $isCurrencySymbolAvailable ? $user->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td class="blue-text"><strong>{{ __('Total Amount') }} ({{ $isCurrencySymbolAvailable ? $user->currencySymbol() : __('Failed to get currency') }})</strong></td>
                                    <td class="blue-text text-end totalAmount">0.00</td>
                                    {{ Form::hidden('totalAmount', null, ['class' => VC::FM_CT.' totalAmount']) }}
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a
                    href="{{ $indexUrl }}"
                    data-url="{{ $indexUrl }}"
                    data-guard-msg="{{ $indexGuardMsg }}"
                    class="btn btn-light"
                    id="expense-cancel-link"
                >{{ __('Cancel') }}</a>
                <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
            </div>
        {{ Form::close() }}
    </div>
    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/expenses/create.js') }}"></script>
    @endpush
@endsection
