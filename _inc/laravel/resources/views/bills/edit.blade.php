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
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
          ar: {
            repeater_delete_confirm: 'هل أنت متأكد أنك تريد حذف هذا العنصر؟',
            vendor_detail_fetch_failed: 'فشل جلب تفاصيل المورد.',
            item_data_fetch_failed: 'فشل جلب بيانات الصنف.',
            bill_items_fetch_failed: 'فشل جلب عناصر الفاتورة.',
            product_destroy_failed: 'فشل حذف المنتج.',
            calculation_error: 'حدث خطأ في الحساب.'
          },
          da: {
            repeater_delete_confirm: 'Er du sikker på, at du vil slette dette element?',
            vendor_detail_fetch_failed: 'Kunne ikke hente leverandørdetaljer.',
            item_data_fetch_failed: 'Kunne ikke hente varedata.',
            bill_items_fetch_failed: 'Kunne ikke hente fakturalinjer.',
            product_destroy_failed: 'Kunne ikke slette produktet.',
            calculation_error: 'Der opstod en beregningsfejl.'
          },
          de: {
            repeater_delete_confirm: 'Sind Sie sicher, dass Sie dieses Element löschen möchten?',
            vendor_detail_fetch_failed: 'Fehler beim Laden der Lieferantendaten.',
            item_data_fetch_failed: 'Fehler beim Laden der Artikeldaten.',
            bill_items_fetch_failed: 'Fehler beim Laden der Rechnungspositionen.',
            product_destroy_failed: 'Fehler beim Löschen des Produkts.',
            calculation_error: 'Beim Berechnen ist ein Fehler aufgetreten.'
          },
          en: {
            repeater_delete_confirm: 'Are you sure you want to delete this element?',
            vendor_detail_fetch_failed: 'Failed to fetch vendor details.',
            item_data_fetch_failed: 'Failed to fetch item data.',
            bill_items_fetch_failed: 'Failed to fetch bill items.',
            product_destroy_failed: 'Failed to delete product.',
            calculation_error: 'A calculation error occurred.'
          },
          es: {
            repeater_delete_confirm: '¿Está seguro de que desea eliminar este elemento?',
            vendor_detail_fetch_failed: 'Error al obtener detalles del proveedor.',
            item_data_fetch_failed: 'Error al obtener datos del artículo.',
            bill_items_fetch_failed: 'Error al obtener elementos de la factura.',
            product_destroy_failed: 'Error al eliminar el producto.',
            calculation_error: 'Ocurrió un error en el cálculo.'
          },
          fr: {
            repeater_delete_confirm: 'Voulez-vous vraiment supprimer cet élément ?',
            vendor_detail_fetch_failed: 'Échec de la récupération des détails du fournisseur.',
            item_data_fetch_failed: 'Échec de la récupération des données de l’article.',
            bill_items_fetch_failed: 'Échec de la récupération des lignes de facture.',
            product_destroy_failed: 'Échec de la suppression du produit.',
            calculation_error: 'Une erreur de calcul est survenue.'
          },
          he: {
            repeater_delete_confirm: 'האם אתה בטוח שברצונך למחוק פריט זה?',
            vendor_detail_fetch_failed: 'לא ניתן להביא את פרטי הספק.',
            item_data_fetch_failed: 'לא ניתן להביא את נתוני הפריט.',
            bill_items_fetch_failed: 'לא ניתן להביא את פרטי החשבונית.',
            product_destroy_failed: 'לא ניתן למחוק את המוצר.',
            calculation_error: 'אירעה שגיאת חישוב.'
          },
          it: {
            repeater_delete_confirm: 'Sei sicuro di voler eliminare questo elemento?',
            vendor_detail_fetch_failed: 'Impossibile recuperare i dettagli del fornitore.',
            item_data_fetch_failed: 'Impossibile recuperare i dati dell’articolo.',
            bill_items_fetch_failed: 'Impossibile recuperare le voci della fattura.',
            product_destroy_failed: 'Impossibile eliminare il prodotto.',
            calculation_error: 'Si è verificato un errore di calcolo.'
          },
          ja: {
            repeater_delete_confirm: 'この項目を削除してもよろしいですか？',
            vendor_detail_fetch_failed: 'ベンダーの詳細を取得できませんでした。',
            item_data_fetch_failed: 'アイテムデータの取得に失敗しました。',
            bill_items_fetch_failed: '請求項目の取得に失敗しました。',
            product_destroy_failed: '製品の削除に失敗しました。',
            calculation_error: '計算中にエラーが発生しました。'
          },
          nl: {
            repeater_delete_confirm: 'Weet u zeker dat u dit element wilt verwijderen?',
            vendor_detail_fetch_failed: 'Kon leveranciersgegevens niet ophalen.',
            item_data_fetch_failed: 'Kon itemgegevens niet ophalen.',
            bill_items_fetch_failed: 'Kon factuuritems niet ophalen.',
            product_destroy_failed: 'Kon product niet verwijderen.',
            calculation_error: 'Er is een fout opgetreden bij het berekenen.'
          },
          pl: {
            repeater_delete_confirm: 'Czy na pewno chcesz usunąć ten element?',
            vendor_detail_fetch_failed: 'Nie udało się pobrać danych dostawcy.',
            item_data_fetch_failed: 'Nie udało się pobrać danych przedmiotu.',
            bill_items_fetch_failed: 'Nie udało się pobrać pozycji faktury.',
            product_destroy_failed: 'Nie udało się usunąć produktu.',
            calculation_error: 'Wystąpił błąd obliczeń.'
          },
          pt: {
            repeater_delete_confirm: 'Tem certeza de que deseja excluir este item?',
            vendor_detail_fetch_failed: 'Falha ao buscar detalhes do fornecedor.',
            item_data_fetch_failed: 'Falha ao buscar dados do item.',
            bill_items_fetch_failed: 'Falha ao buscar itens da fatura.',
            product_destroy_failed: 'Falha ao excluir o produto.',
            calculation_error: 'Ocorreu um erro de cálculo.'
          },
          'pt-br': {
            repeater_delete_confirm: 'Tem certeza de que deseja excluir este item?',
            vendor_detail_fetch_failed: 'Falha ao buscar detalhes do fornecedor.',
            item_data_fetch_failed: 'Falha ao buscar dados do item.',
            bill_items_fetch_failed: 'Falha ao buscar itens da fatura.',
            product_destroy_failed: 'Falha ao excluir o produto.',
            calculation_error: 'Ocorreu um erro de cálculo.'
          },
          ru: {
            repeater_delete_confirm: 'Вы уверены, что хотите удалить этот элемент?',
            vendor_detail_fetch_failed: 'Не удалось получить данные поставщика.',
            item_data_fetch_failed: 'Не удалось получить данные товара.',
            bill_items_fetch_failed: 'Не удалось получить элементы счета.',
            product_destroy_failed: 'Не удалось удалить продукт.',
            calculation_error: 'Произошла ошибка вычисления.'
          },
          tr: {
            repeater_delete_confirm: 'Bu öğeyi silmek istediğinizden emin misiniz?',
            vendor_detail_fetch_failed: 'Tedarikçi ayrıntıları alınamadı.',
            item_data_fetch_failed: 'Ürün verileri alınamadı.',
            bill_items_fetch_failed: 'Fatura öğeleri alınamadı.',
            product_destroy_failed: 'Ürün silinemedi.',
            calculation_error: 'Hesaplama hatası oluştu.'
          },
          zh: {
            repeater_delete_confirm: '您确定要删除此元素吗？',
            vendor_detail_fetch_failed: '获取供应商详情失败。',
            item_data_fetch_failed: '获取项目数据失败。',
            bill_items_fetch_failed: '获取账单项目失败。',
            product_destroy_failed: '删除产品失败。',
            calculation_error: '发生了计算错误。'
          }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
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
                  url: '{{route(ViewsConstants::BIL.'.product.destroy')}}',
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
@section('content')
    <div class="row">
        @php
            $billUpdateRoute      = Route::has(ViewsConstants::BIL . '.update')
                ? route(ViewsConstants::BIL . '.update', $bill->id)
                : '#';
            $billUpdateFormId     = 'bill-update-form-' . $bill->id;
            $billUpdateGuardMsg   = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::BIL,
                'bill_update_route_unavailable'
            ) ?? 'Bill update route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        {{ Form::model($bill, [
            'route'            => [ViewsConstants::BIL . '.update', $bill->id],
            'method'           => 'PUT',
            'id'               => $billUpdateFormId,
            'data-url'         => $billUpdateRoute,
            'data-guard-msg'   => $billUpdateGuardMsg,
            'class'            => 'w-100',
        ]) }}
            @php
                $fields=[
                    ['name'=>'bill_date','type'=>'date','label'=>__('Bill Date'),'cols'=>6,'attrs'=>['required'=>true]],
                    ['name'=>'due_date','type'=>'date','label'=>__('Due Date'),'cols'=>6,'attrs'=>['required'=>true]],
                    ['name'=>'bill_number','type'=>'readonly','label'=>__('Bill Number'),'cols'=>6,'value'=>$bill_number],
                    ['name'=>'category_id','type'=>'select','label'=>__('Category'),'cols'=>6,'options'=>$category,'attrs'=>['class'=>VC::FM_CT_SL]],
                    ['name'=>'order_number','type'=>'number','label'=>__('Order Number'),'cols'=>6,'attrs'=>[]]
                ];
            @endphp
            <div class="{{ VC::C12 }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="{{VC::CD}}">
                    <div class="{{VC::CD_MT}}">
                        <div class="{{VC::RW}}">
                            <div class="col-md-6">
                                <div id="vendor-box" class="{{VC::FM_G}}">
                                    {{ Form::label('vendor_id',__('Vendor'),['class'=>VC::FM_LB]) }}
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
                            <div class="col-md-6">
                                <div class="{{VC::RW}}">
                                    @foreach($fields as $f)
                                        <div class="col-md-{{ $f['cols'] }}">
                                            <div class="{{VC::FM_G}}">
                                                {{ Form::label($f['name'],$f['label'],['class'=>VC::FM_LB]) }}
                                                @php $attrs=array_merge(['class'=>VC::FM_CT],$f['attrs']??[]) @endphp
                                                @switch($f['type'])
                                                    @case('date')    {{ Form::date($f['name'],null,$attrs) }}       @break
                                                    @case('select')  {{ Form::select($f['name'],$f['options'],null,$attrs) }} @break
                                                    @case('number')  {{ Form::number($f['name'],null,$attrs) }}    @break
                                                    @case('readonly')<input type="text" class="{{VC::FM_CT}}" value="{{ $f['value'] }}" readonly> @break
                                                @endswitch
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(!$customFields->isEmpty())
                                        <div class="col-md-6">
                                            @include(ViewsConstants::CST_FD.'.formBuilder')
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::C12 }}">
                <h5 class="d-inline-block mb-4">{{__('Product & Services')}}</h5>
                <div class="{{ VC::CD }} repeater" data-value='{!! json_encode($items) !!}'>
                    <div class="item-section py-2">
                        <div class="{{ VC::RW }} justify-content-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create class="{{ VC::BT_SM_PM }}" data-bs-toggle="modal" data-target="#add-bank">
                                    <i class="{{ VC::TI_PLS }}"></i> {{__('Add item')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="{{ VC::CD_MT }}">
                        <div class="{{ VC::TB }} mb-0" data-repeater-list="items" id="sortable-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="20%">{{__('Items')}}</th><th>{{__('Quantity')}}</th><th>{{__('Price')}}</th><th>{{__('Discount')}}</th><th>{{__('Tax')}} (%)</th><th class="text-end">{{__('Amount')}}<br><small class="text-danger font-bold">{{__('after tax & discount')}}</small></th><th></th>
                                    </tr>
                                </thead>
                                <tbody data-repeater-item>
                                    <tr>
                                        {{ Form::hidden('id',null,['class'=>'id']) }}{{ Form::hidden('account_id',null,['class'=>'account_id']) }}
                                        @php
                                            $productRoute    = Route::has(ViewsConstants::BIL . '.product')
                                                ? route(ViewsConstants::BIL . '.product')
                                                : '#';
                                            $productGuardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BIL,
                                                'product_fetch_route_unavailable'
                                            ) ?? 'Product fetch route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <td width="25%" class="form-group pt-0">
                                            {{ Form::select('items', $product_services, null, [
                                                'id'            => 'product-select',
                                                'class'         => 'form-control select item',
                                                'data-url'      => $productRoute,
                                                'data-guard-msg'=> $productGuardMsg,
                                            ]) }}
                                        </td>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const select = document.getElementById('product-select');
                                                    if (!select || select.getAttribute('data-listener-active') === 'true') return;
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
                                                        } catch (e) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                        <td><div class="input-group"><input name="quantity" class="form-control quantity" placeholder="{{__('Qty')}}"><span class="unit input-group-text bg-transparent"></span></div></td>
                                        <td><div class="input-group"><input name="price" class="form-control price" placeholder="{{__('Price')}}"><span class="input-group-text bg-transparent">{{$user?->currencySymbol()}}</span></div></td>
                                        <td><div class="input-group"><input name="discount" class="form-control discount" placeholder="{{__('Discount')}}"><span class="input-group-text bg-transparent">{{$user?->currencySymbol()}}</span></div></td>
                                        <td><div class="input-group"><div class="taxes"></div>{{ Form::hidden('tax',null,['class'=>'tax']) }}{{ Form::hidden('itemTaxPrice',null,['class'=>'itemTaxPrice']) }}{{ Form::hidden('itemTaxRate',null,['class'=>'itemTaxRate']) }}</div></td>
                                        <td class="text-end amount">0.00</td>
                                        <td><a href="#" class="{{ VC::TRS_PARA }}" data-repeater-delete></a></td>
                                    </tr>
                                    <tr>
                                        <td>{{ Form::select('chart_account_id',$chartAccounts,null,['class'=>'form-control select js-searchBox']) }}</td>
                                        <td><div class="input-group"><input name="amount" class="form-control accountAmount" placeholder="{{__('Amount')}}"><span class="input-group-text bg-transparent">{{$user?->currencySymbol()}}</span></div></td>
                                        <td colspan="2">{{ Form::textarea('description',null,['class'=>'form-control pro_description','rows'=>1,'placeholder'=>__('Description')]) }}</td>
                                        <td></td><td class="text-end accountamount">0.00</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr><td colspan="4"></td><td><strong>{{__('Sub Total')}} ({{$user?->currencySymbol()}})</strong></td><td class="text-end subTotal">0.00</td><td></td></tr>
                                    <tr><td colspan="4"></td><td><strong>{{__('Discount')}} ({{$user?->currencySymbol()}})</strong></td><td class="text-end totalDiscount">0.00</td><td></td></tr>
                                    <tr><td colspan="4"></td><td><strong>{{__('Tax')}} ({{$user?->currencySymbol()}})</strong></td><td class="text-end totalTax">0.00</td><td></td></tr>
                                    <tr><td colspan="4"></td><td class="blue-text"><strong>{{__('Total Amount')}} ({{$user?->currencySymbol()}})</strong></td><td class="blue-text text-end totalAmount">0.00</td><td></td></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
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
                    class="{{ VC::BT_LG }} {{ VC::ME3 }}"
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
    </div>
@endsection

