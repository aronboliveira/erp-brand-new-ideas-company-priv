@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;
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
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Bill Edit')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a
            id="{{ $billIndexId }}"
            href="{{ $billIndexRoute }}"
            data-url="{{ $billIndexRoute }}"
            data-guard-msg="{{ $billIndexMsg }}"
        >
            {{ __('Bill') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Bill Edit')}}</li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/bills/index.js') }}"></script>
    @endpush
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/bills/lang/edit.js') }}"></script>
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
                  url: '{{route(ViewsConstants::BIL.'.items')}}',
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
                  url: '{{route(ViewsConstants::BIL.".product.destroy")}}',
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
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        @if(isset($bill) && !empty($bill))
            @php
                $resolvedUpdateName = Route::has(ViewsConstants::BIL . '.update') ? (ViewsConstants::BIL . '.update') : (Route::has(Str::kebab(ViewsConstants::BIL . '.update')) ? Str::kebab(ViewsConstants::BIL . '.update') : null);
                $billUpdateUrl = $resolvedUpdateName ? route($resolvedUpdateName, $bill->id) : '#';
                $billUpdateFormId = 'bill-update-form-' . ($bill->id ?? 'unknown');
                $billUpdateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_update_route_unavailable') ?? 'Bill update route is unavailable. Please contact technical support or your domain administrator.';
                $fields = [
                    ['name' => 'bill_date',    'type' => 'date',     'label' => __('Bill Date'),   'cols' => 6, 'attrs' => ['required' => true]],
                    ['name' => 'due_date',     'type' => 'date',     'label' => __('Due Date'),    'cols' => 6, 'attrs' => ['required' => true]],
                    ['name' => 'bill_id',  'type' => 'readonly', 'label' => __('Bill Identifier'), 'cols' => 6, 'value' => ($bill_id ?? '') !== '' ? $bill_id : 'No Bill Identifier available'],
                    ['name' => 'category_id',  'type' => 'select',   'label' => __('Category'),    'cols' => 6, 'options' => $category ?? [], 'attrs' => ['class' => VC::FM_CT_SL]],
                    ['name' => 'order_id', 'type' => 'text',   'label' => __('Order Identifier'),'cols' => 6, 'attrs' => []],
                ];
                $resolvedVendorName = Route::has(ViewsConstants::BIL . '.vendor') ? (ViewsConstants::BIL . '.vendor') : (Route::has(Str::kebab(ViewsConstants::BIL . '.vendor')) ? Str::kebab(ViewsConstants::BIL . '.vendor') : null);
                $vendorRoute = $resolvedVendorName ? route($resolvedVendorName) : '#';
                $vendorMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_vendor_fetch_route_unavailable') ?? 'Vendor fetch route is unavailable. Please contact technical support or your domain administrator.';
                $resolvedProductName = Route::has(ViewsConstants::BIL . '.product') ? (ViewsConstants::BIL . '.product') : (Route::has(Str::kebab(ViewsConstants::BIL . '.product')) ? Str::kebab(ViewsConstants::BIL . '.product') : null);
                $productRoute = $resolvedProductName ? route($resolvedProductName) : '#';
                $productGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'product_fetch_route_unavailable') ?? 'Product fetch route is unavailable. Please contact technical support or your domain administrator.';
                $resolvedIndexName = Route::has(ViewsConstants::BIL . '.index') ? (ViewsConstants::BIL . '.index') : (Route::has(Str::kebab(ViewsConstants::BIL . '.index')) ? Str::kebab(ViewsConstants::BIL . '.index') : null);
                $cancelRoute = $resolvedIndexName ? route($resolvedIndexName) : '#';
                $cancelGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'bill_index_route_unavailable') ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
                $currencySymbol = (is_object($user ?? null) && method_exists($user, 'currencySymbol')) ? ((string) ($user->currencySymbol() ?? '')) : '';
                $currencySymbol = $currencySymbol !== '' ? $currencySymbol : '¤';
                $itemsData = (is_array($items ?? null) && count($items ?? [])) || (($items ?? null) instanceof \Illuminate\Support\Collection && ($items ?? collect())->isNotEmpty()) ? $items : [];
                $hasCustomFields = (is_array($customFields ?? null) && count($customFields ?? [])) || (($customFields ?? null) instanceof \Illuminate\Support\Collection && ($customFields ?? collect())->isNotEmpty());
            @endphp

            {{ Form::model($bill, [
                'url'            => $billUpdateUrl,
                'method'         => 'PUT',
                'id'             => $billUpdateFormId,
                'data-url'       => $billUpdateUrl,
                'data-guard-msg' => $billUpdateGuardMsg,
                'class'          => 'w-100',
            ]) }}
                <div class="{{ VC::C12 }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_MT }}">
                            <div class="{{ VC::RW }}">
                                <div class="col-md-6">
                                    <div id="vendor-box" class="{{ VC::FM_G }}">
                                        {{ Form::label('vendor_id', __('Vendor'), ['class' => VC::FM_LB]) }}
                                        {{ Form::select('vendor_id', $vendors ?? [], $vendorId ?? null, [
                                            'class'          => VC::FM_CT_SL,
                                            'id'             => 'vendor_select',
                                            'data-url'       => $vendorRoute,
                                            'data-guard-msg' => $vendorMsg,
                                            'required'       => true,
                                        ]) }}
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/bills/vendorEditSelect.js') }}"></script>
                                        @endpush
                                    </div>
                                    <div id="vendor_detail" class="d-none"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="{{ VC::RW }}">
                                        @foreach($fields as $f)
                                            @php
                                                $attrs = array_merge(['class' => VC::FM_CT], $f['attrs'] ?? []);
                                            @endphp
                                            <div class="col-md-{{ $f['cols'] }}">
                                                <div class="{{ VC::FM_G }}">
                                                    {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                                                    @switch($f['type'])
                                                        @case('date')
                                                            {{ Form::date($f['name'], null, $attrs) }}
                                                            @break
                                                        @case('select')
                                                            {{ Form::select($f['name'], $f['options'] ?? [], null, $attrs) }}
                                                            @break
                                                        @case('number')
                                                            {{ Form::number($f['name'], null, $attrs) }}
                                                            @break
                                                        @case('readonly')
                                                            <input type="text" class="{{ VC::FM_CT }}" value="{{ $f['value'] }}" readonly>
                                                            @break
                                                    @endswitch
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($hasCustomFields)
                                            <div class="col-md-6">
                                                @include(ViewsConstants::CST_FD . '.formBuilder')
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::C12 }}">
                    <h5 class="d-inline-block mb-4">{{ __('Product & Services') }}</h5>
                    <div class="{{ VC::CD }} repeater" data-value='{!! json_encode($itemsData) !!}'>
                        <div class="item-section py-2">
                            <div class="{{ VC::RW }} justify-content-end">
                                <div class="all-button-box me-2">
                                    <a href="#" data-repeater-create class="{{ VC::BT_SM_PM }}" data-bs-toggle="modal" data-target="#add-bank">
                                        <i class="{{ VC::TI_PLS }}"></i> {{ __('Add item') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CD_MT }}">
                            <div class="{{ VC::TB }} mb-0" data-repeater-list="items" id="sortable-table">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th width="20%">{{ __('Items') }}</th>
                                            <th>{{ __('Quantity') }}</th>
                                            <th>{{ __('Price') }}</th>
                                            <th>{{ __('Discount') }}</th>
                                            <th>{{ __('Tax') }} (%)</th>
                                            <th class="text-end">
                                                {{ __('Amount') }}<br>
                                                <small class="text-danger font-bold">{{ __('after tax & discount') }}</small>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody data-repeater-item>
                                        <tr>
                                            {{ Form::hidden('id', null, ['class' => 'id']) }}
                                            {{ Form::hidden('account_id', null, ['class' => 'account_id']) }}
                                            @php
                                                $productSelectOptions = $product_services ?? [];
                                            @endphp
                                            <td width="25%" class="form-group pt-0">
                                                {{ Form::select('items', $productSelectOptions, null, [
                                                    'id'             => 'product-select',
                                                    'class'          => 'form-control select item',
                                                    'data-url'       => $productRoute,
                                                    'data-guard-msg' => $productGuardMsg,
                                                ]) }}
                                            </td>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/bills/editSelect.js') }}"></script>
                                            @endpush
                                            <td>
                                                <div class="input-group">
                                                    <input name="quantity" class="form-control quantity" placeholder="{{ __('Qty') }}">
                                                    <span class="unit input-group-text bg-transparent"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input name="price" class="form-control price" placeholder="{{ __('Price') }}">
                                                    <span class="input-group-text bg-transparent">{{ $currencySymbol }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input name="discount" class="form-control discount" placeholder="{{ __('Discount') }}">
                                                    <span class="input-group-text bg-transparent">{{ $currencySymbol }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <div class="taxes"></div>
                                                    {{ Form::hidden('tax', null, ['class' => 'tax']) }}
                                                    {{ Form::hidden('itemTaxPrice', null, ['class' => 'itemTaxPrice']) }}
                                                    {{ Form::hidden('itemTaxRate', null, ['class' => 'itemTaxRate']) }}
                                                </div>
                                            </td>
                                            <td class="text-end amount">0.00</td>
                                            <td><a href="#" class="{{ VC::TRS_PARA }}" data-repeater-delete></a></td>
                                        </tr>
                                        <tr>
                                            <td>{{ Form::select('chart_account_id', $chartAccounts ?? [], null, ['class' => 'form-control select js-searchBox']) }}</td>
                                            <td>
                                                <div class="input-group">
                                                    <input name="amount" class="form-control accountAmount" placeholder="{{ __('Amount') }}">
                                                    <span class="input-group-text bg-transparent">{{ $currencySymbol }}</span>
                                                </div>
                                            </td>
                                            <td colspan="2">
                                                {{ Form::textarea('description', null, ['class' => 'form-control pro_description', 'rows' => 1, 'placeholder' => __('Description')]) }}
                                            </td>
                                            <td></td>
                                            <td class="text-end accountamount">0.00</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td><strong>{{ __('Sub Total') }} ({{ $currencySymbol }})</strong></td>
                                            <td class="text-end subTotal">0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td><strong>{{ __('Discount') }} ({{ $currencySymbol }})</strong></td>
                                            <td class="text-end totalDiscount">0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td><strong>{{ __('Tax') }} ({{ $currencySymbol }})</strong></td>
                                            <td class="text-end totalTax">0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td class="blue-text"><strong>{{ __('Total Amount') }} ({{ $currencySymbol }})</strong></td>
                                            <td class="blue-text text-end totalAmount">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    @php
                        $cancelBtnId = 'bill-cancel-btn';
                    @endphp
                    <button
                        id="{{ $cancelBtnId }}"
                        type="button"
                        class="{{ VC::BT_LG }} {{ VC::ME3 }}"
                        data-url="{{ $cancelRoute }}"
                        data-guard-msg="{{ $cancelGuardMsg }}"
                    >
                        {{ __('Cancel') }}
                    </button>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer src="{{ asset('assets/js/routes/bills/editCancel.js') }}"></script>
                    @endpush
                    <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                </div>
            {{ Form::close() }}

            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const form = document.getElementById('{{ $billUpdateFormId }}');
                        if (!form || form.getAttribute('data-listener-active') === 'true') return;
                        form.setAttribute('data-listener-active', 'true');
                        form.addEventListener('submit', event => {
                            try {
                                const action = form.getAttribute('action');
                                const url    = form.getAttribute('data-url');
                                if ((action && action !== '#') || (url && url !== '#')) return;
                                event.preventDefault();
                                const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                form.setAttribute('data-failed-route', 'true');
                            } catch (e) {}
                        });
                    })();
                </script>
            @endpush
        @else
            <div class="{{ VC::C12 }}">
                <div class="alert alert-danger">
                    {{ __('Bill record not found.') }}
                </div>
            </div>
        @endif
    </div>
@endsection

