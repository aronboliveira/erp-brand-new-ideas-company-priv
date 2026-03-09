
@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
        $billIndexRoute = Route::has(ViewsConstants::BIL . '.index')
            ? route(ViewsConstants::BIL . '.index')
            : '#';
        $billIndexId  = 'bill-index-link';
        $billIndexMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BIL,
            'bill_index_route_unavailable'
        ) ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('bills/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $product_services ??= [];
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Bill Create')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">
        <a
            id="{{ $billIndexId }}"
            href="{{ $billIndexRoute }}"
            data-url="{{ $billIndexRoute }}"
            data-guard-msg="{{ base64_encode($billIndexMsg) }}"
        >
            {{ __('Bill') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Bill Create')}}</li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/bills/index.js') }}"></script>
    @endpush
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/bills/lang/vendor.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        @php
            try {
                $billsStoreRoute    = Route::has(ViewsConstants::BIL)
                    ? route(ViewsConstants::BIL)
                    : '#';
                $billsStoreFormId   = 'bills-store-form';
                $billsStoreMsg      = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::BIL,
                    'bill_store_route_unavailable'
                ) ?? 'Bill store route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('bills/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        {{ Form::open([
            'url'            => $billsStoreRoute,
            'id'             => $billsStoreFormId,
            'data-url'       => $billsStoreRoute,
            'data-guard-msg' => $billsStoreMsg,
            'class'          => 'w-100',
        ]) }}
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_MT }}">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::FM_G }}">
                                {{ Form::label('vendor_id', __('Vendor'), ['class'=>VC::FM_LB]) }}
                                @php
                                    try {
                                        $vendorRoute = Route::has(ViewsConstants::BIL . '.vendor')
                                            ? route(ViewsConstants::BIL . '.vendor')
                                            : '#';
                                        $vendorMsg      = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BIL,
                                                'bill_vendor_fetch_route_unavailable'
                                            ) ?? 'Vendor fetch route is unavailable. Please contact technical support or your domain administrator.';
                                        $vendorSelectId = 'vendor_select';
                                    } catch (\Throwable $e) {
                                        \Log::error('bills/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                @if(!empty($vendors) && ((is_array($vendors) && count($vendors)) || ($vendors instanceof Collection && $vendors->isNotEmpty())))
                                    {{ Form::select('vendor_id', $vendors, $vendorId, [
                                        'class'         => VC::FM_CT_SL,
                                        'id'            => $vendorSelectId,
                                        'data-url'      => $vendorRoute,
                                        'data-guard-msg'=> $vendorMsg,
                                        'required'      => true,
                                    ]) }}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/bills/select.js') }}"></script>
                                    @endpush
                                    @foreach($vendors as $vendorId => $vendorName)
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const errFb = '# ERROR';
                                                    const dataClientLocalized = 'data-client-localized';
                                                    const dataGuardMsg = 'data-guard-msg';
                                                    const langKey = 'erp-np-lang';
                                                    const getMsg = (key, el) => {
                                                        let msg = errFb;
                                                        if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
                                                        msg = el.getAttribute(dataGuardMsg) ?? errFb;
                                                        } else {
                                                        let lang = (sessionStorage.getItem(langKey) ?? document.documentElement.lang ?? 'en')
                                                            .toLowerCase().replace(/_/g,'-');
                                                        lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                                                        msg = translations?.[lang]?.[key] ??
                                                                el.getAttribute(dataGuardMsg) ??
                                                                translations?.['en']?.[key] ??
                                                                errFb;
                                                        if (msg !== errFb) {
                                                            el.setAttribute(dataGuardMsg, msg);
                                                            el.setAttribute(dataClientLocalized, 'true');
                                                        }
                                                        }
                                                        return msg;
                                                    };
                                                    const showError = message => {
                                                        (window.RouteGuard?.showToast || (m => alert(m)))(message);
                                                    };
                                                    let errorMsg = '';
                                                    const onPointerUp = () => {
                                                        if (errorMsg) {
                                                        showError(errorMsg);
                                                        errorMsg = '';
                                                        }
                                                    };
                                                    document.addEventListener('pointerup', onPointerUp);
                                                    new MutationObserver((mut, obs) => {
                                                        mut.forEach(m => m.removedNodes.forEach(n => {
                                                        if (n === document.documentElement) {
                                                            document.removeEventListener('pointerup', onPointerUp);
                                                            obs.disconnect();
                                                        }
                                                        }));
                                                    }).observe(document.body, { childList:true, subtree:true });
                                                    document.addEventListener('DOMContentLoaded', () => {
                                                        try {
                                                        const sel = 'body .repeater';
                                                        const container = document.querySelector(sel);
                                                        if (!container) return;

                                                        const drag = $(sel + ' tbody').sortable({ handle: '.sort-handler' });
                                                        const rep = $(sel).repeater({
                                                            initEmpty: false,
                                                            defaultValues: { status:1 },
                                                            show() {
                                                            try {
                                                                $(this).slideDown();
                                                                $(this).find('input.multi').MultiFile?.({
                                                                max:3, accept:'png|jpg|jpeg', max_size:{{ SettingsConstants::MAX_U_SIZE_DEF }}
                                                                });
                                                                JsSearchBox();
                                                                $('.select2').select2();
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
                                                                $('.subTotal').text(sub.toFixed(2));
                                                                $('.totalAmount').text(sub.toFixed(2));
                                                            } catch {
                                                                errorMsg = getMsg('calculation_error', this);
                                                            }
                                                            },
                                                            ready(setIdx) { drag.on('drop', setIdx); },
                                                            isFirstItemUndeletable:true
                                                        });
                                                        const dataVal = container.getAttribute('data-value');
                                                        if (dataVal) rep.setList(JSON.parse(dataVal));
                                                        } catch {
                                                        errorMsg = getMsg('calculation_error', document.body);
                                                        }

                                                        const onVendorChange = async () => {
                                                        try {
                                                            $('#vendor_detail').toggleClass('d-none d-block', true);
                                                            $('#vendor-box').toggleClass('d-block d-none', true);
                                                            const id = $('#vendor').val(), url = $('#vendor').data('url');
                                                            const resp = await $.ajax({
                                                            url, type:'POST',
                                                            headers:{ 'X-CSRF-TOKEN':$('#token').val() },
                                                            data:{ id }
                                                            });
                                                            if (resp) $('#vendor_detail').html(resp);
                                                            else {
                                                            $('#vendor-detail').toggleClass('d-none d-block', false);
                                                            $('#vendor-box').toggleClass('d-block d-none', false);
                                                            }
                                                        } catch {
                                                            errorMsg = getMsg('vendor_detail_fetch_failed', document.body);
                                                        }
                                                        };
                                                        $('#vendor').on('change', onVendorChange);
                                                        $('#remove').on('click', () => {
                                                        $('#vendor-box').toggleClass('d-none d-block', true);
                                                        $('#vendor_detail').toggleClass('d-block d-none', true);
                                                        });

                                                        const onItemChange = async function(){
                                                        const el = this;
                                                        try {
                                                            const pid = $(el).val(), url = $(el).data('url');
                                                            const data = JSON.parse(await $.ajax({ url, type:'POST',
                                                            headers:{ 'X-CSRF-TOKEN':$('#token').val() },
                                                            data:{ product_id:pid }
                                                            }));
                                                            const row = $(el).closest('tr');
                                                            row.find('.quantity').val(1);
                                                            row.find('.price').val(data.product.purchase_price);
                                                            row.find('.pro_description').val(data.product.description);
                                                            let totalRate=0, taxesHtml='', taxIds=[];
                                                            if (data.taxes && data.taxes.length) {
                                                            data.taxes.forEach(t=>{
                                                                taxesHtml+=`<span class="badge {{ VC::BG_P }} {{ VC::MT1 }} {{ VC::MR2 }}">${t.name} (${t.rate}%)</span>`;
                                                                taxIds.push(t.id);
                                                                totalRate+=parseFloat(t.rate);
                                                            });
                                                            } else taxesHtml='-';
                                                            const itemTax = (totalRate/100)*(data.product.purchase_price*1);
                                                            row.find('.itemTaxPrice').val(itemTax.toFixed(2));
                                                            row.find('.itemTaxRate').val(totalRate.toFixed(2));
                                                            row.find('.taxes').html(taxesHtml);
                                                            row.find('.tax').val(taxIds);
                                                            row.find('.unit').html(data.unit);
                                                            row.find('.discount').val(0);

                                                            $('.quantity, .price, .discount, .accountAmount').trigger('keyup change');
                                                        } catch {
                                                            errorMsg = getMsg('item_data_fetch_failed', this);
                                                        }
                                                        };
                                                        $(document).on('change', '.item', onItemChange);

                                                        const recalc = () => {
                                                        try {
                                                            let totalPrice=0, totalTax=0, totalAmt=0, totalAccount=0;
                                                            $('.quantity').each((i,q)=> {
                                                            const pr=parseFloat($('.price').eq(i).val())||0;
                                                            const dq=parseFloat($(q).val())||0;
                                                            const dc=parseFloat($('.discount').eq(i).val())||0;
                                                            const sub=(dq*pr)-dc;
                                                            const rate=parseFloat($('.itemTaxRate').eq(i).val())||0;
                                                            const tax=(rate/100)*sub;
                                                            $('.itemTaxPrice').eq(i).val(tax.toFixed(2));
                                                            $('.amount').eq(i).text((sub+tax).toFixed(2));
                                                            totalPrice+=sub;
                                                            totalTax+=tax;
                                                            });
                                                            $('.accountAmount').each((i,a)=> {
                                                            const v=parseFloat($(a).val())||0;
                                                            totalAccount+=v;
                                                            $('.accountamount').eq(i).text(v.toFixed(2));
                                                            });
                                                            totalAmt = totalPrice - $('.discount').toArray().reduce((s,el)=>s+(parseFloat(el.value)||0),0) + totalTax;
                                                            $('.subTotal').text((totalPrice+totalAccount).toFixed(2));
                                                            $('.totalTax').text(totalTax.toFixed(2));
                                                            $('.totalAmount').text((totalAmt+totalAccount).toFixed(2));
                                                        } catch {
                                                            errorMsg = getMsg('calculation_error', document.body);
                                                        }
                                                        };
                                                        $(document).on('keyup change', '.quantity, .price, .discount, .accountAmount', recalc);
                                                        const vid = '{{ $vendorId }}';
                                                        if (+vid > 0) $('#vendor').val(vid).change();
                                                        $(document).on('click','[data-repeater-delete]', recalc);
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    @endforeach
                                @else
                                    <select class="{{ VC::FM_CT_SL }}" disabled>
                                        <option>{{ __('No vendor available') }}</option>
                                    </select>
                                @endif
                            </div>
                            <div id="vendor_detail" class="d-none"></div>
                        </div>
                        @php
                            try {
                                $fields = [
                                    ['bill_date','date', __('Bill Date'), true],
                                    ['due_date','date', __('Due Date'), true],
                                    ['bill_id','readonly', __('Bill Identifier'), false, $bill_id],
                                    ['category_id','select', __('Category'), false, null, $category],
                                    ['order_id','text', __('Order Identifier'), false],
                                ];
                            } catch (\Throwable $e) {
                                \Log::error('bills/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
                            $fields ??= [];
@endphp
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::RW }}">
                                @foreach($fields as $f)
                                    <div class="{{ VC::CM6 }}">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label($f[0], $f[2], ['class'=>VC::FM_LB]) }}
                                            @switch($f[1])
                                                @case('date')
                                                    {{ Form::date($f[0], null, ['class'=>VC::FM_CT, 'required'=>$f[3]]) }}
                                                    @break
                                                @case('number')
                                                    {{ Form::number($f[0], null, ['class'=>VC::FM_CT]) }}
                                                    @break
                                                @case('select')
                                                    {{ Form::select($f[0], $f[5] ?? [], null, ['class'=>VC::FM_CT_SL]) }}
                                                    @break
                                                @case('readonly')
                                                    <input type="text" class="{{ VC::FM_CT }}" value="{{ $f[4] }}" readonly>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                @endforeach

                                @if(!$customFields->isEmpty())
                                    <div class="{{ VC::CM6 }}">
                                        @include(ViewsConstants::CST_FD . '.formBuilder')
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::C12 }} mt-4">
            <h5 class="{{ VC::MB3 }}">{{ __('Product & Services') }}</h5>
            <div class="{{ VC::CD }} repeater">
                <div class="item-section py-2 {{ VC::RW }} {{ VC::JCE }}">
                    <a href="#" data-repeater-create class="{{ VC::BT_SM_PM }}">
                        <i class="{{ VC::TI_PLS }}"></i> {{ __('Add Item') }}
                    </a>
                </div>
                <div class="{{ VC::CD_MT }}">
                    <div class="{{ VC::TABLE_RESPONSIVE }}">
                        <table class="{{ VC::TB }} mb-0" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Tax') }} (%)</th>
                                    <th class="{{ VC::TX_END }}">
                                        {{ __('Amount') }}
                                        <br><small class="{{ VC::TX_DNG }} fw-bold">{{ __('after tax & discount') }}</small>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody data-repeater-item>
                                <tr>
                                    @php
                                        try {
                                            $productRoute     = Route::has(ViewsConstants::BIL . '.product')
                                                ? route(ViewsConstants::BIL . '.product')
                                                : '#';
                                            $itemGuardMsg     = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BIL,
                                                'product_fetch_route_unavailable'
                                            ) ?? 'Product fetch route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('bills/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <td class="{{ VC::FM_G }}">
                                        {{ Form::select('item', $product_services, '', [
                                            'class'         => VC::FM_CT_SL . ' item-select',
                                            'data-url'      => $productRoute,
                                            'data-guard-msg'=> $itemGuardMsg,
                                        ]) }}
                                    </td>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                document.querySelectorAll('select.item-select').forEach(select => {
                                                    if (select.getAttribute('data-listener-active') === 'true') return;
                                                    select.setAttribute('data-listener-active', 'true');
                                                    select.addEventListener('change', async () => {
                                                        try {
                                                            const url = select.getAttribute('data-url');
                                                            if (!url || url === '#') {
                                                                const msg           = select.getAttribute('data-guard-msg');
                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                select.setAttribute('data-failed-route', 'true');
                                                                return;
                                                            }

                                                            const productId = select.value ?? '';
                                                            const response  = await fetch(`${url}?product_id=${encodeURIComponent(productId)}`, {
                                                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                                            });
                                                            if (!response.ok) throw new Error(`Network error: ${response.status}`);
                                                            const data = await response.json();

                                                            document.querySelectorAll('[data-product-field]').forEach(el => {
                                                                const key = el.getAttribute('data-product-field');
                                                                const val = data[key] ?? '';
                                                                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                                                                    el.value = val;
                                                                } else {
                                                                    el.textContent = val;
                                                                }
                                                            });
                                                        } catch (e) {
                                                            if (
                                                                window.location.hostname === "localhost" ||
                                                                window.location.hostname === "127.0.0.1"
                                                            ) console.error(e);
                                                        }
                                                    });
                                                });
                                            })();
                                        </script>
                                    @endpush
                                    <td class="{{ VC::FM_G }}">
                                        <div class="{{ VC::DFL }} {{ VC::INP_GP_TXT }}">
                                            {{ Form::text('quantity', '', ['class'=>'form-control quantity','placeholder'=>__('Qty')]) }}
                                            <span class="unit {{ VC::TXTS_TRP }}"></span>
                                        </div>
                                    </td>
                                    <td class="{{ VC::FM_G }}">
                                        <div class="{{ VC::DFL }} {{ VC::INP_GP_TXT }}">
                                            {{ Form::text('price', '', ['class'=>'form-control price','placeholder'=>__('Price')]) }}
                                            <span class="{{ VC::TXTS_TRP }}">{{ $user->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td class="{{ VC::FM_G }}">
                                        <div class="{{ VC::DFL }} {{ VC::INP_GP_TXT }}">
                                            {{ Form::text('discount','',['class'=>'form-control discount','placeholder'=>__('Discount')]) }}
                                            <span class="{{ VC::TXTS_TRP }}">{{ $user->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td class="{{ VC::FM_G }}">
                                        <div class="{{ VC::DFL }}">
                                            <div class="taxes"></div>
                                            {{ Form::hidden('tax','',['class'=>'tax']) }}
                                            {{ Form::hidden('itemTaxPrice','',['class'=>'itemTaxPrice']) }}
                                            {{ Form::hidden('itemTaxRate','',['class'=>'itemTaxRate']) }}
                                        </div>
                                    </td>
                                    <td class="{{ VC::TX_END }} amount">0.00</td>
                                    <td>
                                        @can('delete proposal product')
                                            <a href="#" class="{{ VC::TRS_M2 }}" data-repeater-delete></a>
                                        @endcan
                                    </td>
                                </tr>
                                <tr>
                                    <td class="{{ VC::FM_G }}">
                                        {{ Form::select('chart_account_id', $chartAccounts, '', [
                                            'class'=>VC::FM_CT_SL . ' js-searchBox'
                                        ]) }}
                                    </td>
                                    <td colspan="2" class="{{ VC::FM_G }}">
                                        {{ Form::textarea('description',null,[
                                            'class'=>'form-control pro_description',
                                            'rows'=>1,
                                            'placeholder'=>__('Description')
                                        ]) }}
                                    </td>
                                    <td></td>
                                    <td class="{{ VC::TX_END }} accountamount">0.00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                @foreach ([
                                    ['Sub Total', 'subTotal'],
                                    ['Discount', 'totalDiscount'],
                                    ['Tax', 'totalTax']
                                ] as $r)
                                    <tr>
                                        <td colspan="4"></td>
                                        <td><strong>{{ __($r[0]) }} ({{ $user->currencySymbol() }})</strong></td>
                                        <td class="{{ VC::TX_END }} {{ $r[1] }}">0.00</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="{{ VC::TX_PM }}"><strong>{{ __('Total Amount') }} ({{ $user->currencySymbol() }})</strong></td>
                                    <td class="{{ VC::TX_PM }} {{ VC::TX_END }} totalAmount">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="{{ VC::RW }} mt-4">
                <div class="{{ VC::C12 }} {{ VC::JCE }}">
                    @php
                        try {
                            $cancelRoute        = Route::has(ViewsConstants::BIL . '.index')
                                ? route(ViewsConstants::BIL . '.index')
                                : '#';
                            $cancelBtnId        = 'bill-cancel-btn';
                            $cancelGuardMsg     = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::BIL,
                                'bill_index_route_unavailable'
                            ) ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
                        } catch (\Throwable $e) {
                            \Log::error('bills/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <button
                        id="{{ $cancelBtnId }}"
                        type="button"
                        class="{{ VC::BT_LG }}"
                        data-url="{{ $cancelRoute }}"
                        data-guard-msg="{{ base64_encode($cancelGuardMsg) }}"
                    >
                        {{ __('Cancel') }}
                    </button>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                const btn = document.getElementById('{{ $cancelBtnId }}');
                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                btn.setAttribute('data-listener-active', 'true');
                                btn.addEventListener('click', event => {
                                    try {
                                        const url = btn.getAttribute('data-url');
                                        if (!url || url === '#') {
                                            event.preventDefault();
                                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                            let container       = document.getElementById('toast-container');
                                            if (!container) {
                                                container       = document.createElement('div');
                                                container.id    = 'toast-container';
                                                document.body.appendChild(container);
                                            }
                                            if (bootstrapLink && window.bootstrap) {
                                                const toastEl      = document.createElement('div');
                                                toastEl.className  = 'toast';
                                                toastEl.setAttribute('role', 'alert');
                                                toastEl.setAttribute('aria-live', 'assertive');
                                                toastEl.setAttribute('aria-atomic', 'true');
                                                const body         = document.createElement('div');
                                                body.className     = 'toast-body';
                                                body.textContent   = msg;
                                                toastEl.appendChild(body);
                                                container.appendChild(toastEl);
                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                            } else {
                                                alert(msg);
                                            }
                                            btn.setAttribute('data-failed-route', 'true');
                                            return;
                                        }
                                        window.location.href = url;
                                    } catch (e) {}
                                });
                            })();
                        </script>
                    @endpush
                    <button type="submit" class="{{ VC::BT_PRM }}">
                        {{ __('Create') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/bills/store.js') }}"></script>
        @endpush
    </div>
@endsection
