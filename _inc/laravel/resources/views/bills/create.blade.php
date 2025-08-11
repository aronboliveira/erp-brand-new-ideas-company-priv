
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
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
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
    {{__('Bill Create')}}
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
    <li class="breadcrumb-item">{{__('Bill Create')}}</li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const link = document.getElementById('{{ $billIndexId }}');
                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                link.setAttribute('data-listener-active', 'true');
                link.addEventListener('click', event => {
                    try {
                        const href = link.getAttribute('href');
                        const url  = link.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg = link.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        link.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            })();
        </script>
    @endpush
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        window.translations = {
          ar: {
            repeater_delete_confirm: 'هل أنت متأكد أنك تريد حذف هذا العنصر؟',
            vendor_detail_fetch_failed: 'فشل جلب تفاصيل المورد.',
            item_data_fetch_failed: 'فشل جلب بيانات الصنف.',
            calculation_error: 'حدث خطأ في الحساب.'
          },
          da: {
            repeater_delete_confirm: 'Er du sikker på, at du vil slette dette element?',
            vendor_detail_fetch_failed: 'Kunne ikke hente leverandørdetaljer.',
            item_data_fetch_failed: 'Kunne ikke hente varedata.',
            calculation_error: 'Der opstod en beregningsfejl.'
          },
          de: {
            repeater_delete_confirm: 'Sind Sie sicher, dass Sie dieses Element löschen möchten?',
            vendor_detail_fetch_failed: 'Fehler beim Laden der Lieferantendaten.',
            item_data_fetch_failed: 'Fehler beim Laden der Artikeldaten.',
            calculation_error: 'Beim Berechnen ist ein Fehler aufgetreten.'
          },
          en: {
            repeater_delete_confirm: 'Are you sure you want to delete this element?',
            vendor_detail_fetch_failed: 'Failed to fetch vendor details.',
            item_data_fetch_failed: 'Failed to fetch item data.',
            calculation_error: 'A calculation error occurred.'
          },
          es: {
            repeater_delete_confirm: '¿Está seguro de que desea eliminar este elemento?',
            vendor_detail_fetch_failed: 'Error al obtener detalles del proveedor.',
            item_data_fetch_failed: 'Error al obtener datos del artículo.',
            calculation_error: 'Ocurrió un error en el cálculo.'
          },
          fr: {
            repeater_delete_confirm: 'Voulez-vous vraiment supprimer cet élément ?',
            vendor_detail_fetch_failed: 'Échec de la récupération des détails du fournisseur.',
            item_data_fetch_failed: 'Échec de la récupération des données de l’article.',
            calculation_error: 'Une erreur de calcul est survenue.'
          },
          he: {
            repeater_delete_confirm: 'האם אתה בטוח שברצונך למחוק פריט זה?',
            vendor_detail_fetch_failed: 'לא ניתן להביא את פרטי הספק.',
            item_data_fetch_failed: 'לא ניתן להביא את נתוני הפריט.',
            calculation_error: 'אירעה שגיאת חישוב.'
          },
          it: {
            repeater_delete_confirm: 'Sei sicuro di voler eliminare questo elemento?',
            vendor_detail_fetch_failed: 'Impossibile recuperare i dettagli del fornitore.',
            item_data_fetch_failed: 'Impossibile recuperare i dati dell’articolo.',
            calculation_error: 'Si è verificato un errore di calcolo.'
          },
          ja: {
            repeater_delete_confirm: 'この項目を削除してもよろしいですか？',
            vendor_detail_fetch_failed: 'ベンダーの詳細を取得できませんでした。',
            item_data_fetch_failed: 'アイテムデータの取得に失敗しました。',
            calculation_error: '計算中にエラーが発生しました。'
          },
          nl: {
            repeater_delete_confirm: 'Weet u zeker dat u dit element wilt verwijderen?',
            vendor_detail_fetch_failed: 'Kon leveranciersgegevens niet ophalen.',
            item_data_fetch_failed: 'Kon itemgegevens niet ophalen.',
            calculation_error: 'Er is een fout opgetreden bij het berekenen.'
          },
          pl: {
            repeater_delete_confirm: 'Czy na pewno chcesz usunąć ten element?',
            vendor_detail_fetch_failed: 'Nie udało się pobrać danych dostawcy.',
            item_data_fetch_failed: 'Nie udało się pobrać danych przedmiotu.',
            calculation_error: 'Wystąpił błąd obliczeń.'
          },
          pt: {
            repeater_delete_confirm: 'Tem certeza de que deseja excluir este item?',
            vendor_detail_fetch_failed: 'Falha ao buscar detalhes do fornecedor.',
            item_data_fetch_failed: 'Falha ao buscar dados do item.',
            calculation_error: 'Ocorreu um erro de cálculo.'
          },
          'pt-br': {
            repeater_delete_confirm: 'Tem certeza de que deseja excluir este item?',
            vendor_detail_fetch_failed: 'Falha ao buscar detalhes do fornecedor.',
            item_data_fetch_failed: 'Falha ao buscar dados do item.',
            calculation_error: 'Ocorreu um erro de cálculo.'
          },
          ru: {
            repeater_delete_confirm: 'Вы уверены, что хотите удалить этот элемент?',
            vendor_detail_fetch_failed: 'Не удалось получить данные поставщика.',
            item_data_fetch_failed: 'Не удалось получить данные товара.',
            calculation_error: 'Произошла ошибка вычисления.'
          },
          tr: {
            repeater_delete_confirm: 'Bu öğeyi silmek istediğinizden emin misiniz?',
            vendor_detail_fetch_failed: 'Tedarikçi ayrıntıları alınamadı.',
            item_data_fetch_failed: 'Ürün verileri alınamadı.',
            calculation_error: 'Hesaplama hatası oluştu.'
          },
          zh: {
            repeater_delete_confirm: '您确定要删除此元素吗？',
            vendor_detail_fetch_failed: '获取供应商详情失败。',
            item_data_fetch_failed: '获取项目数据失败。',
            calculation_error: '发生了计算错误。'
          }
        };
    </script>
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
        
            // vendor change
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
                    taxesHtml+=`<span class="badge bg-primary mt-1 mr-2">${t.name} (${t.rate}%)</span>`;
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
        
            // force initial vendor if set
            const vid = '{{ $vendorId }}';
            if (+vid > 0) $('#vendor').val(vid).change();
            // recalc on delete
            $(document).on('click','[data-repeater-delete]', recalc);
          });
        })();
    </script>
@endpush
@section('content')
    <div class="{{ VC::RW }}">
        @php
            $billsStoreRoute    = Route::has(ViewsConstants::BIL)
                ? route(ViewsConstants::BIL)
                : '#';
            $billsStoreFormId   = 'bills-store-form';
            $billsStoreMsg      = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::BIL,
                'bill_store_route_unavailable'
            ) ?? 'Bill store route is unavailable. Please contact technical support or your domain administrator.';
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
                                    $vendorRoute = Route::has(ViewsConstants::BIL . '.vendor')
                                        ? route(ViewsConstants::BIL . '.vendor')
                                        : '#';
                                    $vendorMsg      = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BIL,
                                            'bill_vendor_fetch_route_unavailable'
                                        ) ?? 'Vendor fetch route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                {{ Form::select('vendor_id', $vendors, $vendorId, [
                                    'class'         => VC::FM_CT_SL,
                                    'id'            => $vendorSelectId,
                                    'data-url'      => $vendorRoute,
                                    'data-guard-msg'=> $vendorMsg,
                                    'required'      => true,
                                ]) }}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const select        = document.getElementById('{{ $vendorSelectId }}');
                                            if (!select || select.getAttribute('data-listener-active') === 'true') return;
                                            select.setAttribute('data-listener-active', 'true');

                                            const urlAttr       = 'data-url';
                                            const guardMsgAttr  = 'data-guard-msg';
                                            const failedAttr    = 'data-failed-route';

                                            select.addEventListener('change', async () => {
                                                try {
                                                    const url = select.getAttribute(urlAttr);
                                                    if (!url || url === '#') {
                                                        const msg           = select.getAttribute(guardMsgAttr);
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
                                                        select.setAttribute(failedAttr, 'true');
                                                        return;
                                                    }

                                                    const vendorId = select.value ?? '';
                                                    const response = await fetch(`${url}?vendor_id=${encodeURIComponent(vendorId)}`, {
                                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                                    });

                                                    if (!response.ok) {
                                                        throw new Error(`Network error: ${response.status}`);
                                                    }

                                                    const data = await response.json();
                                                    document.querySelectorAll('[data-vendor-field]').forEach(el => {
                                                        const key = el.getAttribute('data-vendor-field');
                                                        const val = data[key] ?? '';
                                                        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                                                            el.value = val;
                                                        } else {
                                                            el.textContent = val;
                                                        }
                                                    });
                                                } catch (e) {}
                                            });
                                        })();
                                    </script>
                                @endpush
                            </div>
                            <div id="vendor_detail" class="d-none"></div>
                        </div>
                        @php
                            $fields = [
                                ['bill_date','date', __('Bill Date'), true],
                                ['due_date','date', __('Due Date'), true],
                                ['bill_number','readonly', __('Bill Number'), false, $bill_number],
                                ['category_id','select', __('Category'), false, null, $category],
                                ['order_number','number', __('Order Number'), false],
                            ];
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
            <h5 class="mb-3">{{ __('Product & Services') }}</h5>
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
                                    <th class="text-end">
                                        {{ __('Amount') }}
                                        <br><small class="text-danger fw-bold">{{ __('after tax & discount') }}</small>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody data-repeater-item>
                                <tr>
                                    @php
                                        $productRoute     = Route::has(ViewsConstants::BIL . '.product')
                                            ? route(ViewsConstants::BIL . '.product')
                                            : '#';
                                        $itemGuardMsg     = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BIL,
                                            'product_fetch_route_unavailable'
                                        ) ?? 'Product fetch route is unavailable. Please contact technical support or your domain administrator.';
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
                                                            console.error(e);
                                                        }
                                                    });
                                                });
                                            })();
                                        </script>
                                    @endpush
                                    <td class="{{ VC::FM_G }}">
                                        <div class="{{ VC::DFL }} {{ VC::INP_GP_TXT }}">
                                            {{ Form::text('quantity', '', ['class'=>'form-control quantity','placeholder'=>__('Qty')]) }}
                                            <span class="unit input-group-text bg-transparent"></span>
                                        </div>
                                    </td>
                                    <td class="{{ VC::FM_G }}">
                                        <div class="{{ VC::DFL }} {{ VC::INP_GP_TXT }}">
                                            {{ Form::text('price', '', ['class'=>'form-control price','placeholder'=>__('Price')]) }}
                                            <span class="input-group-text bg-transparent">{{ $user->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td class="{{ VC::FM_G }}">
                                        <div class="{{ VC::DFL }} {{ VC::INP_GP_TXT }}">
                                            {{ Form::text('discount','',['class'=>'form-control discount','placeholder'=>__('Discount')]) }}
                                            <span class="input-group-text bg-transparent">{{ $user->currencySymbol() }}</span>
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
                                    <td class="text-end amount">0.00</td>
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
                                    <td class="text-end accountamount">0.00</td>
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
                                        <td class="text-end {{ $r[1] }}">0.00</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-primary"><strong>{{ __('Total Amount') }} ({{ $user->currencySymbol() }})</strong></td>
                                    <td class="text-primary text-end totalAmount">0.00</td>
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
                        $cancelRoute        = Route::has(ViewsConstants::BIL . '.index')
                            ? route(ViewsConstants::BIL . '.index')
                            : '#';
                        $cancelBtnId        = 'bill-cancel-btn';
                        $cancelGuardMsg     = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::BIL,
                            'bill_index_route_unavailable'
                        ) ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <button
                        id="{{ $cancelBtnId }}"
                        type="button"
                        class="{{ VC::BT_LG }}"
                        data-url="{{ $cancelRoute }}"
                        data-guard-msg="{{ $cancelGuardMsg }}"
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
            <script defer>
                (() => {
                    const form = document.getElementById('{{ $billsStoreFormId }}');
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
    </div>
@endsection

