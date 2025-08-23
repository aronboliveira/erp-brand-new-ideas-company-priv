
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Expense Create')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::EXP.'.index')}}">{{__('Expense')}}</a></li>
    <li class="breadcrumb-item">{{__('Expense Create')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{ asset('js/jquery-searchbox.js') }}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
          ar: {
            selection_failed:             'فشل تغيير النوع.',
            employee_fetch_failed:        'فشل جلب بيانات الموظف.',
            customer_fetch_failed:        'فشل جلب بيانات العميل.',
            vendor_fetch_failed:          'فشل جلب بيانات البائع.',
            repeater_initialization_failed:'فشل تهيئة المكرر.',
            item_fetch_failed:            'فشل جلب بيانات الصنف.',
            calculation_failed:           'فشل حساب الإجماليات.',
            repeater_delete_failed:       'فشل حذف عنصر التكرار.'
          },
          da: {
            selection_failed:             'Kunne ikke ændre typen.',
            employee_fetch_failed:        'Kunne ikke hente medarbejderdata.',
            customer_fetch_failed:        'Kunne ikke hente kundedata.',
            vendor_fetch_failed:          'Kunne ikke hente leverandørdata.',
            repeater_initialization_failed:'Kunne ikke initialisere gentager.',
            item_fetch_failed:            'Kunne ikke hente varedata.',
            calculation_failed:           'Kunne ikke beregne totaler.',
            repeater_delete_failed:       'Kunne ikke slette gentagelseselement.'
          },
          de: {
            selection_failed:             'Auswahl konnte nicht geändert werden.',
            employee_fetch_failed:        'Mitarbeiterdaten konnten nicht geladen werden.',
            customer_fetch_failed:        'Kundendaten konnten nicht geladen werden.',
            vendor_fetch_failed:          'Anbieterdaten konnten nicht geladen werden.',
            repeater_initialization_failed:'Initialisierung des Repeaters fehlgeschlagen.',
            item_fetch_failed:            'Elementdaten konnten nicht abgerufen werden.',
            calculation_failed:           'Berechnung der Summen fehlgeschlagen.',
            repeater_delete_failed:       'Fehler beim Löschen des Wiederholungselements.'
          },
          en: {
            selection_failed:             'Failed to change type.',
            employee_fetch_failed:        'Failed to load employee details.',
            customer_fetch_failed:        'Failed to load customer details.',
            vendor_fetch_failed:          'Failed to load vendor details.',
            repeater_initialization_failed:'Failed to initialize repeater.',
            item_fetch_failed:            'Failed to fetch item data.',
            calculation_failed:           'Failed to calculate totals.',
            repeater_delete_failed:       'Failed to delete repeater item.'
          },
          es: {
            selection_failed:             'Error al cambiar el tipo.',
            employee_fetch_failed:        'Error al cargar datos del empleado.',
            customer_fetch_failed:        'Error al cargar datos del cliente.',
            vendor_fetch_failed:          'Error al cargar datos del proveedor.',
            repeater_initialization_failed:'No se pudo inicializar el repetidor.',
            item_fetch_failed:            'No se pudieron obtener los datos del artículo.',
            calculation_failed:           'No se pudieron calcular los totales.',
            repeater_delete_failed:       'No se pudo eliminar el elemento repetidor.'
          },
          fr: {
            selection_failed:             'Échec du changement de type.',
            employee_fetch_failed:        'Échec du chargement des détails de l’employé.',
            customer_fetch_failed:        'Échec du chargement des détails du client.',
            vendor_fetch_failed:          'Échec du chargement des détails du fournisseur.',
            repeater_initialization_failed:'Échec de l’initialisation du répéteur.',
            item_fetch_failed:            'Échec de la récupération des données de l’article.',
            calculation_failed:           'Échec du calcul des totaux.',
            repeater_delete_failed:       'Échec de la suppression de l’élément répétiteur.'
          },
          he: {
            repeater_delete_failed:       'המחיקה של פריט החזרה נכשלה.'
          },
          it: {
            repeater_delete_failed:       'Impossibile eliminare l’elemento ripetitore.'
          },
          ja: {
            repeater_delete_failed:       'リピーター項目の削除に失敗しました。'
          },
          nl: {
            repeater_delete_failed:       'Kan herhaler-item niet verwijderen.'
          },
          pl: {
            repeater_delete_failed:       'Nie udało się usunąć elementu repeatera.'
          },
          pt: {
            repeater_delete_failed:       'Falha ao excluir item do repetidor.'
          },
          'pt-br': {
            repeater_delete_failed:       'Falha ao excluir item do repetidor.'
          },
          ru: {
            repeater_delete_failed:       'Не удалось удалить элемент повторителя.'
          },
          tr: {
            repeater_delete_failed:       'Tekrarlayıcı öğe silinemedi.'
          },
          zh: {
            repeater_delete_failed:       '无法删除重复器项目。'
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
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langSessionKey = 'erp-np-lang';
        let errorMessage = '';
        
        const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (
            el.getAttribute('data-sv-localized') === 'true' ||
            el.getAttribute(dataClientLocalized) === 'true'
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                'en'
            )
                .toLowerCase()
                .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ||
                window.translations?.['en']?.[msgKey] ||
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
            const bsLink = document.querySelector('link[href*="bootstrap"]');
            let container = document.getElementById('toast-container');
            if (bsLink && window.bootstrap) {
                if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
                }
                const toastEl = document.createElement('div');
                toastEl.className = 'toast';
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                body.textContent = message;
                toastEl.appendChild(body);
                container.appendChild(toastEl);
                window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };
        
        const onPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        
        document.addEventListener('pointerup', onPointerUp);
        
        document.querySelectorAll('[data-repeater-delete]').forEach(el => {
            if (el.getAttribute('data-guard-listener-active') === 'true') return;
            el.setAttribute('data-guard-listener-active', 'true');
            el.addEventListener('click', () => {
            try {
                $('.price').change();
                $('.discount').change();
            } catch {
                errorMessage = getLocalizedMessage('repeater_delete_failed', el);
            }
            });
        });
        
        new MutationObserver((muts, obs) => {
            muts.forEach(m => m.removedNodes.forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList: true, subtree: true });
        })();
    </script>
    {{--  start for user select--}}
    <script defer>
        (() => {
          const errFb = '# ERROR';
          const clientLoc = 'data-client-localized';
          const guardMsg = 'data-guard-msg';
          const langKey = 'erp-np-lang';
          let errorMessage = '';
        
          const getMsg = (key, el) => {
            let msg = errFb;
            if (
              el.getAttribute('data-sv-localized') === 'true' ||
              el.getAttribute(clientLoc) === 'true'
            ) {
              msg = el.getAttribute(guardMsg) || errFb;
            } else {
              let lang = (
                window.sessionStorage.getItem(langKey) ||
                document.documentElement.lang ||
                'en'
              )
                .toLowerCase()
                .replace(/_/g, '-');
              lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
              msg =
                window.translations?.[lang]?.[key] ||
                window.translations?.['en']?.[key] ||
                errFb;
              if (msg !== errFb) {
                el.setAttribute(guardMsg, msg);
                el.setAttribute(clientLoc, 'true');
              }
            }
            return msg;
          };
        
          const showError = message => {
            try {
              const bs = document.querySelector('link[href*="bootstrap"]');
              let c = document.getElementById('toast-container');
              if (bs && window.bootstrap) {
                if (!c) {
                  c = document.createElement('div');
                  c.id = 'toast-container';
                  document.body.appendChild(c);
                }
                const t = document.createElement('div');
                t.className = 'toast';
                t.setAttribute('role', 'alert');
                t.setAttribute('aria-live', 'assertive');
                t.setAttribute('aria-atomic', 'true');
                const b = document.createElement('div');
                b.className = 'toast-body';
                b.textContent = message;
                t.appendChild(b);
                c.appendChild(t);
                window.bootstrap.Toast.getOrCreateInstance(t).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          };
        
          document.addEventListener('pointerup', () => {
            if (errorMessage) {
              showError(errorMessage);
              errorMessage = '';
            }
          });
        
          const initSelection = () => {
            const first = document.querySelector('input[name=type]');
            if (!first) return;
            first.checked = true;
            const radios = document.querySelectorAll('input[name="type"]');
            radios.forEach(r => {
              if (r.getAttribute('data-listener-active') === 'true') return;
              r.setAttribute('data-listener-active', 'true');
              r.addEventListener('change', onTypeChange);
              new MutationObserver((m, o) => {
                m.forEach(mut => mut.removedNodes.forEach(n => {
                  if (n === r) {
                    r.removeEventListener('change', onTypeChange);
                    o.disconnect();
                  }
                }));
              }).observe(document.body, { childList: true, subtree: true });
            });
            onTypeChange.call(document.querySelector('input[name="type"]:checked'));
          };
        
          const onTypeChange = function() {
            const type = this.value;
            ['employee','customer','vendor'].forEach(cls => {
              document.querySelectorAll(`.${cls}`).forEach(el => {
                el.classList.toggle('d-block', cls === type);
                el.classList.toggle('d-none', cls !== type);
              });
            });
          };
        
          const setupAjax = type => {
            const sel = document.getElementById(type);
            if (!sel || sel.getAttribute('data-listener-active') === 'true') return;
            sel.setAttribute('data-listener-active', 'true');
            const detail = document.getElementById(`${type}_detail`);
            const box = document.getElementById(`${type}-box`);
            sel.addEventListener('change', () => {
              if (detail) detail.classList.replace('d-none','d-block');
              if (box) box.classList.replace('d-block','d-none');
              const url = sel.getAttribute('data-url');
              if (!url) {
                errorMessage = getMsg(`${type}_fetch_failed`, sel);
                return;
              }
              const id = sel.value;
              $.ajax({
                url,
                type: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: { id }
              })
              .done(data => {
                if (data && detail) detail.innerHTML = data;
                else if (box && detail) {
                  box.classList.replace('d-none','d-block');
                  detail.classList.replace('d-block','d-none');
                }
              })
              .fail(() => {
                errorMessage = getMsg(`${type}_fetch_failed`, sel);
              });
            });
            new MutationObserver((m,o) => {
              m.forEach(mut => mut.removedNodes.forEach(n => {
                if (n === sel) {
                  sel.removeEventListener('change', ()=>{});
                  o.disconnect();
                }
              }));
            }).observe(document.body, { childList: true, subtree: true });
          };
        
          document.addEventListener('DOMContentLoaded', () => {
            try {
              initSelection();
              ['employee','customer','vendor'].forEach(setupAjax);
            } catch {
              console.error('Initialization error');
            }
          });
        })();
    </script>
    {{--   end for user select--}}
@endpush
@section('content')
    <div class="row">
        {{ Collective\Html\FormFacade::open(array('url' => ViewsConstants::EXP,'class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col">
                                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                    <input type="radio" id="employee_radio" value="employee" name="type" class="form-check-input {{isset($_GET['type']) && $_GET['type']=='employee' ?'checked':'checked'}}" >
                                    <label class="form-check-label" for="employee">{{__('Employee')}}</label>
                                </div>
                                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                    <input type="radio" id="customer_radio" value="customer" name="type" class="form-check-input" >
                                    <label class="form-check-label" for="customer">{{__('Customer')}}</label>
                                </div>
                                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                    <input type="radio" id="vendor_radio" value="vendor" name="type" class="form-check-input" >
                                    <label class="form-check-label" for="vendor">{{__('Vendor')}}</label>
                                </div>
                            </div>

                            <div class="col employee">
                                <div class="form-group" id="employee-box">
                                    {{ Collective\Html\FormFacade::label('employee_id', __('Payee'),['class'=>'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('employee_id', $employees,null, array('class' => 'form-control select','id'=>'employee','data-url'=>route(ViewsConstants::EXP.'.employee'))) }}
                                </div>
                                <div id="employee_detail" class="d-none">
                                </div>
                            </div>
                            <div class="col customer d-none">
                                <div class="form-group" id="customer-box">
                                    {{ Collective\Html\FormFacade::label('customer_id', __('Payee'),['class'=>'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('customer_id', $customers,null, array('class' => 'form-control select','id'=>'customer','data-url'=>route(ViewsConstants::EXP.'.customer'))) }}
                                </div>
                                <div id="customer_detail" class="d-none">
                                </div>
                            </div>
                            <div class="col vendor d-none">
                                <div class="form-group" id="vendor-box">
                                    {{ Collective\Html\FormFacade::label('vendor_id', __('Payee'),['class'=>'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('vendor_id', $vendors,$Id, array('class' => 'form-control select','id'=>'vendor','data-url'=>route(ViewsConstants::EXP.'.vendor'))) }}
                                </div>
                                <div id="vendor_detail" class="d-none">
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Collective\Html\FormFacade::label('payment_date', __('Payment Date'),['class'=>'form-label']) }}
                                        {{Collective\Html\FormFacade::date('payment_date',null,array('class'=>'form-control','required'=>'required'))}}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Collective\Html\FormFacade::label('category_id', __('Category'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('category_id', $category,null, array('class' => 'form-control select')) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Collective\Html\FormFacade::label('account_id', __('Account'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('account_id',$accounts,null, array('class' => 'form-control','required'=>'required')) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <h5 class="d-inline-block mb-4">{{__('Product & Services')}}</h5>
            <div class="card repeater">
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="{{ ViewClassNamesConstants::BT_PRM }}" data-bs-toggle="modal" data-target="#add-bank">
                                    <i class="ti ti-plus"></i> {{__('Add Item')}}
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
                                <th width="20%">{{__('Items')}}</th>
                                <th>{{__('Quantity')}}</th>
                                <th>{{__('Price')}} </th>
                                <th>{{__('Discount')}}</th>
                                <th>{{__('Tax')}} (%)</th>
                                <th class="text-end">{{__('Amount')}}
                                    <br><small class="text-danger font-bold">{{__('after tax & discount')}}</small>
                                </th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                <td width="25%" class="form-group pt-0">
                                    {{ Collective\Html\FormFacade::select('item', $product_services,'', array('class' => 'form-control select2 item','data-url'=>route(ViewsConstants::EXP.'.product'))) }}
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Collective\Html\FormFacade::text('quantity','', array('class' => 'form-control quantity','placeholder'=>__('Qty'))) }}
                                        <span class="unit input-group-text bg-transparent"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Collective\Html\FormFacade::text('price','', array('class' => 'form-control price','placeholder'=>__('Price'))) }}
                                        <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Collective\Html\FormFacade::text('discount','', array('class' => 'form-control discount','placeholder'=>__('Discount'))) }}
                                        <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="taxes"></div>
                                            {{ Collective\Html\FormFacade::hidden('tax','', array('class' => 'form-control tax')) }}
                                            {{ Collective\Html\FormFacade::hidden('itemTaxPrice','', array('class' => 'form-control itemTaxPrice')) }}
                                            {{ Collective\Html\FormFacade::hidden('itemTaxRate','', array('class' => 'form-control itemTaxRate')) }}
                                        </div>
                                    </div>
                                </td>

                                <td class="text-end amount">
                                    0.00
                                </td>
                                <td>
                                    @can('delete proposal product')
                                        <a href="#" class="{{ ViewClassNamesConstants::TRS_M2 }}" data-repeater-delete></a>
                                    @endcan
                                </td>
                            </tr>
                            <tr>
                                <td  class="form-group">
                                    {{ Collective\Html\FormFacade::select('chart_account_id', $chartAccounts,'', array('class' => 'form-control select2 js-searchBox')) }}
                                </td>
                                <td class="form-group">
                                    <div class="input-group ">
                                        {{ Collective\Html\FormFacade::text('amount','', array('class' => 'form-control accountAmount','placeholder'=>__('Amount'))) }}
                                        <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td>
                                <td colspan="2" class="form-group">
                                    {{ Collective\Html\FormFacade::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'1','placeholder'=>__('Description')]) }}
                                </td>
                                <td></td>
                                <td class="text-end accountamount">
                                    0.00
                                </td>
                            </tr>

                            </tbody>
                            <tfoot>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Sub Total')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end subTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Discount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Tax')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end totalTax">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="blue-text"><strong>{{__('Total Amount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>

                                <td class="blue-text text-end totalAmount">0.00</td>
                                {{ Collective\Html\FormFacade::hidden('totalAmount',null, array('class' => 'form-control totalAmount')) }}

                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = ' {{ route(ViewsConstants::EXP.'.index') }}';" class="btn btn-light">
            <input type="submit" value="{{__('Create')}}" class="{{ ViewClassNamesConstants::BT_PRM }}">
        </div>
        {{ Collective\Html\FormFacade::close() }}
    </div>
@endsection

