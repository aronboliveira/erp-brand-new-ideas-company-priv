@php
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Auth;

    $user           = Auth::user();
    $lang           = Utility::fetchUserLang(user: $user);
    $indexName      = ViewsConstants::INV . '.index';
    $indexRoute     = Route::has($indexName)
        ? route($indexName)
        : '#';
    $indexGuardMsg  = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'invoice_index_route_unavailable'
    ) ?? 'Invoice list route is unavailable. Please contact technical support or your domain administrator.';
    $storeName      = ViewsConstants::INV . '.store';
    $storeRoute     = Route::has($storeName)
        ? route($storeName)
        : '#';
    $formId         = 'invoiceCreateForm';
    $storeGuardMsg  = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'invoice_store_route_unavailable'
    ) ?? 'Invoice create route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Invoice Create')}}
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
            id="breadcrumb-invoice-link"
            href="{{ $indexRoute }}"
            {{ $indexRoute === '#' ? 'aria-disabled="true"' : '' }}
            data-url="{{ $indexRoute }}"
            data-guard-msg="{{ $indexGuardMsg }}"
        >
            {{ __('Invoice') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const link = document.getElementById('breadcrumb-invoice-link');
                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                link.setAttribute('data-listener-active', 'true');
                link.addEventListener('click', event => {
                    try {
                        const url = link.getAttribute('data-url') ?? '#';
                        if (url !== '#') return;
                        event.preventDefault();

                        const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
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

                        link.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            })();
        </script>
    @endpush
    <li class="breadcrumb-item">{{__('Invoice Create')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script async>
        window.translations = {
            ar:       { repeater_show_unavailable: 'فشل عرض المكرر', repeater_hide_unavailable: 'فشل إخفاء المكرر', customer_change_unavailable: 'فشل جلب تفاصيل العميل', customer_remove_unavailable: 'فشل إزالة العميل', item_change_unavailable: 'فشل جلب تفاصيل الصنف', calculation_unavailable: 'فشل الحساب', customer_initial_unavailable: 'فشل تحديد العميل' },
            da:       { repeater_show_unavailable: 'Visning af gentager mislykkedes', repeater_hide_unavailable: 'Skjul af gentager mislykkedes', customer_change_unavailable: 'Hentning af kundedetaljer mislykkedes', customer_remove_unavailable: 'Fjernelse af kunde mislykkedes', item_change_unavailable: 'Hentning af vareoplysninger mislykkedes', calculation_unavailable: 'Beregning mislykkedes', customer_initial_unavailable: 'Indstilling af kunde mislykkedes' },
            de:       { repeater_show_unavailable: 'Wiederholer-Anzeige fehlgeschlagen', repeater_hide_unavailable: 'Wiederholer-Ausblenden fehlgeschlagen', customer_change_unavailable: 'Kundendetailsabruf fehlgeschlagen', customer_remove_unavailable: 'Kunde entfernen fehlgeschlagen', item_change_unavailable: 'Artikeldetailsabruf fehlgeschlagen', calculation_unavailable: 'Berechnung fehlgeschlagen', customer_initial_unavailable: 'Kundeinstellung fehlgeschlagen' },
            en:       { repeater_show_unavailable: 'Cannot show repeater', repeater_hide_unavailable: 'Cannot hide repeater', customer_change_unavailable: 'Cannot fetch customer details', customer_remove_unavailable: 'Cannot remove customer', item_change_unavailable: 'Cannot fetch item details', calculation_unavailable: 'Calculation failed', customer_initial_unavailable: 'Cannot select customer' },
            es:       { repeater_show_unavailable: 'Error al mostrar repetidor', repeater_hide_unavailable: 'Error al ocultar repetidor', customer_change_unavailable: 'Error al obtener detalles del cliente', customer_remove_unavailable: 'Error al eliminar cliente', item_change_unavailable: 'Error al obtener detalles del artículo', calculation_unavailable: 'Error en el cálculo', customer_initial_unavailable: 'Error al seleccionar cliente' },
            fr:       { repeater_show_unavailable: 'Échec de l\'affichage du répéteur', repeater_hide_unavailable: 'Échec de la suppression du répéteur', customer_change_unavailable: 'Impossible de récupérer les détails client', customer_remove_unavailable: 'Impossible de supprimer le client', item_change_unavailable: 'Impossible de récupérer les détails de l\'article', calculation_unavailable: 'Échec du calcul', customer_initial_unavailable: 'Impossible de sélectionner le client' },
            he:       { repeater_show_unavailable: 'הצגת החוזר נכשלה', repeater_hide_unavailable: 'הסתרת החוזר נכשלה', customer_change_unavailable: 'שגיאה בקבלת פרטי הלקוח', customer_remove_unavailable: 'שגיאה בהסרת הלקוח', item_change_unavailable: 'שגיאה בקבלת פרטי הפריט', calculation_unavailable: 'החישוב נכשל', customer_initial_unavailable: 'שגיאה בבחירת הלקוח' },
            it:       { repeater_show_unavailable: 'Impossibile mostrare il ripetitore', repeater_hide_unavailable: 'Impossibile nascondere il ripetitore', customer_change_unavailable: 'Errore nel recupero dettagli cliente', customer_remove_unavailable: 'Errore nella rimozione cliente', item_change_unavailable: 'Errore nel recupero dettagli articolo', calculation_unavailable: 'Errore nel calcolo', customer_initial_unavailable: 'Errore nella selezione cliente' },
            ja:       { repeater_show_unavailable: 'リピーターの表示に失敗しました', repeater_hide_unavailable: 'リピーターの非表示に失敗しました', customer_change_unavailable: '顧客詳細の取得に失敗しました', customer_remove_unavailable: '顧客の削除に失敗しました', item_change_unavailable: 'アイテム詳細の取得に失敗しました', calculation_unavailable: '計算に失敗しました', customer_initial_unavailable: '顧客の選択に失敗しました' },
            nl:       { repeater_show_unavailable: 'Herhaler weergeven mislukt', repeater_hide_unavailable: 'Herhaler verbergen mislukt', customer_change_unavailable: 'Klantgegevens ophalen mislukt', customer_remove_unavailable: 'Klant verwijderen mislukt', item_change_unavailable: 'Artikelgegevens ophalen mislukt', calculation_unavailable: 'Berekening mislukt', customer_initial_unavailable: 'Klantselectie mislukt' },
            pl:       { repeater_show_unavailable: 'Nie można wyświetlić powtarzacza', repeater_hide_unavailable: 'Nie można ukryć powtarzacza', customer_change_unavailable: 'Błąd pobierania danych klienta', customer_remove_unavailable: 'Błąd usuwania klienta', item_change_unavailable: 'Błąd pobierania danych przedmiotu', calculation_unavailable: 'Błąd obliczeń', customer_initial_unavailable: 'Błąd wyboru klienta' },
            pt:       { repeater_show_unavailable: 'Não foi possível exibir o repetidor', repeater_hide_unavailable: 'Não foi possível ocultar o repetidor', customer_change_unavailable: 'Falha ao obter detalhes do cliente', customer_remove_unavailable: 'Falha ao remover cliente', item_change_unavailable: 'Falha ao obter detalhes do item', calculation_unavailable: 'Falha no cálculo', customer_initial_unavailable: 'Falha ao selecionar cliente' },
            'pt-br': { repeater_show_unavailable: 'Não foi possível exibir o repetidor', repeater_hide_unavailable: 'Não foi possível ocultar o repetidor', customer_change_unavailable: 'Falha ao obter detalhes do cliente', customer_remove_unavailable: 'Falha ao remover cliente', item_change_unavailable: 'Falha ao obter detalhes do item', calculation_unavailable: 'Falha no cálculo', customer_initial_unavailable: 'Falha ao selecionar cliente' },
            ru:       { repeater_show_unavailable: 'Не удалось показать повторитель', repeater_hide_unavailable: 'Не удалось скрыть повторитель', customer_change_unavailable: 'Не удалось получить данные клиента', customer_remove_unavailable: 'Не удалось удалить клиента', item_change_unavailable: 'Не удалось получить данные товара', calculation_unavailable: 'Ошибка вычисления', customer_initial_unavailable: 'Не удалось выбрать клиента' },
            tr:       { repeater_show_unavailable: 'Tekrar gösterilemedi', repeater_hide_unavailable: 'Tekrar gizlenemedi', customer_change_unavailable: 'Müşteri bilgileri alınamadı', customer_remove_unavailable: 'Müşteri kaldırılamadı', item_change_unavailable: 'Ürün bilgileri alınamadı', calculation_unavailable: 'Hesaplama başarısız', customer_initial_unavailable: 'Müşteri seçilemedi' },
            zh:       { repeater_show_unavailable: '无法显示重复项', repeater_hide_unavailable: '无法隐藏重复项', customer_change_unavailable: '无法获取客户详情', customer_remove_unavailable: '无法移除客户', item_change_unavailable: '无法获取商品详情', calculation_unavailable: '计算失败', customer_initial_unavailable: '无法选择客户' }
        };
    </script>
    <script defer>
        (() => {
            const dataListenerAdded   = 'data-listener-added';
            const errFb               = '# ERROR';
            const dataClientLocalized = 'data-client-localized';
            const dataGuardMsg        = 'data-guard-msg';
    
            const getLocalizedMessage = (el, msgKey) => {
                let msg = errFb;
                if (el.getAttribute('data-sv-localized') === 'true'
                    || el.getAttribute(dataClientLocalized) === 'true') {
                    msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                    let lang = (window.sessionStorage.getItem('erp-np-lang')
                                || document.documentElement.lang
                                || 'en')
                                .toLowerCase()
                                .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                    msg = window.translations?.[lang]?.[msgKey]
                          || el.getAttribute(dataGuardMsg)
                          || window.translations?.['en']?.[msgKey]
                          || errFb;
                    if (msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, 'true');
                    }
                }
                return msg;
            };
    
            const handleErrorDisplay = (el, msgKey) => {
                const message = el ? getLocalizedMessage(el, msgKey) : errFb;
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]')
                                     && window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id       = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
                        toast.innerHTML = `
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button"
                                        class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"
                                        aria-label="Close"></button>
                            </div>`;
                        document.body.appendChild(toast);
                    }
                    new bootstrap.Toast(document.querySelector('#error-toast')).show();
                } else {
                    alert(message);
                }
            };
    
            try {
                if (!window.jQuery) {
                    console.error('jQuery is required');
                    return;
                }
    
                const selector = 'body';
    
                if ($(`${selector} .repeater`).length) {
                    const $dragAndDrop = $(`${selector} .repeater tbody`)
                        .sortable({ handle: '.sort-handler' });
    
                    const $repeater = $(`${selector} .repeater`).repeater({
                        initEmpty: false,
                        defaultValues: { status: 1 },
                        show() {
                            try {
                                $(this).slideDown();
                                const fileUploads = $(this).find('input.multi');
                                if (fileUploads.length) {
                                    fileUploads.MultiFile({
                                        max:       3,
                                        accept:    'png|jpg|jpeg',
                                        max_size:  {{ SettingsConstants::MAX_U_SIZE_DEF }}
                                    });
                                }
                                if ($('.select2').length) {
                                    $('.select2').select2();
                                }
                            } catch {
                                const el = this;
                                if (el.getAttribute(dataListenerAdded) !== 'true') {
                                    $(el).on('click',
                                        () => handleErrorDisplay(el, 'repeater_show_unavailable'));
                                    el.setAttribute(dataListenerAdded, 'true');
                                    const obs = new MutationObserver((_, o) => {
                                        if (!document.body.contains(el)) {
                                            $(el).off('click');
                                            o.disconnect();
                                        }
                                    });
                                    obs.observe(document.body, { childList: true, subtree: true });
                                }
                            }
                        },
                        hide(deleteElement) {
                            try {
                                if (confirm('Are you sure you want to delete this element?')) {
                                    $(this).slideUp(deleteElement);
                                    $(this).remove();
                                    let subTotal = 0;
                                    $('.amount').each((_i, amt) => {
                                        subTotal += parseFloat($(amt).html()) || 0;
                                    });
                                    $('.subTotal, .totalAmount')
                                        .html(subTotal.toFixed(2));
                                }
                            } catch {
                                const el = this;
                                if (el.getAttribute(dataListenerAdded) !== 'true') {
                                    $(el).on('click',
                                        () => handleErrorDisplay(el, 'repeater_hide_unavailable'));
                                    el.setAttribute(dataListenerAdded, 'true');
                                    const obs = new MutationObserver((_, o) => {
                                        if (!document.body.contains(el)) {
                                            $(el).off('click');
                                            o.disconnect();
                                        }
                                    });
                                    obs.observe(document.body, { childList: true, subtree: true });
                                }
                            }
                        },
                        ready(setIndexes) {
                            $dragAndDrop.on('drop', setIndexes);
                        },
                        isFirstItemUndeletable: true
                    });
    
                    const value = $(`${selector} .repeater`).attr('data-value');
                    if (value) {
                        try {
                            $repeater.setList(JSON.parse(value));
                        } catch {
                            // ignore initialization parse errors
                        }
                    }
                }
    
                $(document).on('change', '#customer', function () {
                    try {
                        $('#customer_detail').removeClass('d-none').addClass('d-block');
                        $('#customer-box').removeClass('d-block').addClass('d-none');
                        const id  = $(this).val();
                        const url = $(this).data('url');
                        jQuery.ajax({
                            url,
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': $('#token').val() },
                            data: { id },
                            cache: false,
                            success(data) {
                                try {
                                    if (data) {
                                        $('#customer_detail').html(data);
                                    } else {
                                        $('#customer-box').removeClass('d-none').addClass('d-block');
                                        $('#customer_detail').removeClass('d-block').addClass('d-none');
                                    }
                                } catch {
                                    const el = this;
                                    if (el.getAttribute(dataListenerAdded) !== 'true') {
                                        $(el).on('click',
                                            () => handleErrorDisplay(el, 'customer_change_unavailable'));
                                        el.setAttribute(dataListenerAdded, 'true');
                                        const obs = new MutationObserver((_, o) => {
                                            if (!document.body.contains(el)) {
                                                $(el).off('click');
                                                o.disconnect();
                                            }
                                        });
                                        obs.observe(document.body, { childList: true, subtree: true });
                                    }
                                }
                            }
                        });
                    } catch {
                        const el = this;
                        if (el.getAttribute(dataListenerAdded) !== 'true') {
                            $(el).on('click',
                                () => handleErrorDisplay(el, 'customer_change_unavailable'));
                            el.setAttribute(dataListenerAdded, 'true');
                            const obs = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    $(el).off('click');
                                    o.disconnect();
                                }
                            });
                            obs.observe(document.body, { childList: true, subtree: true });
                        }
                    }
                });
    
                $(document).on('click', '#remove', function () {
                    try {
                        $('#customer-box').removeClass('d-none').addClass('d-block');
                        $('#customer_detail').removeClass('d-block').addClass('d-none');
                    } catch {
                        const el = this;
                        if (el.getAttribute(dataListenerAdded) !== 'true') {
                            $(el).on('click',
                                () => handleErrorDisplay(el, 'customer_remove_unavailable'));
                            el.setAttribute(dataListenerAdded, 'true');
                            const obs = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    $(el).off('click');
                                    o.disconnect();
                                }
                            });
                            obs.observe(document.body, { childList: true, subtree: true });
                        }
                    }
                });
    
                $(document).on('change', '.item', function () {
                    try {
                        const $el    = $(this);
                        const itemId = $el.val();
                        const url    = $el.data('url');
                        jQuery.ajax({
                            url,
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': $('#token').val() },
                            data: { product_id: itemId },
                            cache: false,
                            success(data) {
                                try {
                                    const item = JSON.parse(data);
                                    const $row = $el.closest('tr');
                                    $row.find('.quantity').val(1);
                                    $row.find('.price').val(item.product.sale_price);
                                    $row.find('.pro_description')
                                        .val(item.product.description);
    
                                    let taxesHtml = '';
                                    let totalRate = 0;
                                    const taxIds  = [];
                                    if (item.taxes?.length) {
                                        item.taxes.forEach(t => {
                                            taxesHtml +=
                                                `<span class="badge bg-primary mt-1 mr-2">`
                                                + `${t.name} (${t.rate}%)</span>`;
                                            taxIds.push(t.id);
                                            totalRate += parseFloat(t.rate) || 0;
                                        });
                                    } else {
                                        taxesHtml = '-';
                                    }
    
                                    const taxPrice = ((totalRate / 100)
                                                      * item.product.sale_price) || 0;
                                    $row.find('.itemTaxPrice')
                                        .val(taxPrice.toFixed(2));
                                    $row.find('.itemTaxRate')
                                        .val(totalRate.toFixed(2));
                                    $row.find('.taxes').html(taxesHtml);
                                    $row.find('.tax').val(taxIds);
                                    $row.find('.unit').html(item.unit);
                                    $row.find('.discount').val(0);
    
                                    let subTotal      = 0;
                                    let totalPrice    = 0;
                                    let totalTaxPrice = 0;
                                    let totalDiscount = 0;
    
                                    $('.amount').each((_i, amt) => {
                                        subTotal += parseFloat($(amt).html()) || 0;
                                    });
                                    $('.price').each((_i, prc) => {
                                        totalPrice += parseFloat(prc.value) || 0;
                                    });
                                    $('.itemTaxPrice').each((_i, tx) => {
                                        totalTaxPrice += parseFloat(tx.value) || 0;
                                    });
                                    $('.discount').each((_i, dc) => {
                                        totalDiscount += parseFloat(dc.value) || 0;
                                    });
    
                                    $('.subTotal').html(totalPrice.toFixed(2));
                                    $('.totalTax').html(totalTaxPrice.toFixed(2));
                                    $('.totalAmount')
                                        .html((totalPrice - totalDiscount + totalTaxPrice)
                                              .toFixed(2));
                                } catch {
                                    const el = this;
                                    if (el.getAttribute(dataListenerAdded) !== 'true') {
                                        $(el).on('click',
                                            () => handleErrorDisplay(el, 'item_change_unavailable'));
                                        el.setAttribute(dataListenerAdded, 'true');
                                        const obs = new MutationObserver((_, o) => {
                                            if (!document.body.contains(el)) {
                                                $(el).off('click');
                                                o.disconnect();
                                            }
                                        });
                                        obs.observe(document.body, { childList: true, subtree: true });
                                    }
                                }
                            }
                        });
                    } catch {
                        const el = this;
                        if (el.getAttribute(dataListenerAdded) !== 'true') {
                            $(el).on('click',
                                () => handleErrorDisplay(el, 'item_change_unavailable'));
                            el.setAttribute(dataListenerAdded, 'true');
                            const obs = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    $(el).off('click');
                                    o.disconnect();
                                }
                            });
                            obs.observe(document.body, { childList: true, subtree: true });
                        }
                    }
                });
    
                const calculateTotals = $row => {
                    const qty      = parseFloat($row.find('.quantity').val()) || 0;
                    const prc      = parseFloat($row.find('.price').val())    || 0;
                    const disc     = parseFloat($row.find('.discount').val()) || 0;
                    const net      = (qty * prc) - disc;
                    const rate     = parseFloat($row.find('.itemTaxRate').val()) || 0;
                    const taxPrice = (rate / 100) * net;
                    $row.find('.itemTaxPrice').val(taxPrice.toFixed(2));
                    $row.find('.amount').html((net + taxPrice).toFixed(2));
    
                    let totalTax   = 0;
                    let totalPrice = 0;
                    let subTotal   = 0;
    
                    $('.itemTaxPrice').each((_i, tx) => totalTax += parseFloat(tx.value) || 0);
                    $('.price').each((_i, pr) => {
                        const q = parseFloat($('.quantity').eq(_i).val()) || 0;
                        totalPrice += (parseFloat(pr.value) || 0) * q;
                    });
                    $('.amount').each((_i, amt) => subTotal += parseFloat($(amt).html()) || 0);
    
                    $('.subTotal').html(totalPrice.toFixed(2));
                    $('.totalTax').html(totalTax.toFixed(2));
                    $('.totalAmount').html(subTotal.toFixed(2));
                };
    
                $(document).on('keyup change', '.quantity, .price, .discount', function () {
                    try {
                        calculateTotals($(this).closest('tr'));
                    } catch {
                        const el = this;
                        if (el.getAttribute(dataListenerAdded) !== 'true') {
                            el.addEventListener('pointerup',
                                () => handleErrorDisplay(el, 'calculation_unavailable'));
                            el.setAttribute(dataListenerAdded, 'true');
                            const obs = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    el.removeEventListener('pointerup', handleErrorDisplay);
                                    o.disconnect();
                                }
                            });
                            obs.observe(document.body, { childList: true, subtree: true });
                        }
                    }
                });
    
                const customerId = parseInt('{{$customer_id}}', 10) || 0;
                if (customerId > 0) {
                    try {
                        $('#customer').val(customerId).trigger('change');
                    } catch {
                        const el = document.querySelector('#customer');
                        if (el && el.getAttribute(dataListenerAdded) !== 'true') {
                            el.addEventListener('click',
                                () => handleErrorDisplay(el, 'customer_initial_unavailable'));
                            el.setAttribute(dataListenerAdded, 'true');
                            const obs = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    el.removeEventListener('click', handleErrorDisplay);
                                    o.disconnect();
                                }
                            });
                            obs.observe(document.body, { childList: true, subtree: true });
                        }
                    }
                }
                $(document).on('click', '[data-repeater-delete]', () => {
                    $('.price').trigger('change');
                    $('.discount').trigger('change');
                });
    
            } catch (e) {
                console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush
@section('content')
    <div class="{{ VC::RW }}">
        {{ Form::open(['url' => ViewsConstants::INV, 'class' => 'w-100', 'data-url' => $storeRoute]) }}
        <div class="{{ VC::C12 }}">
            {{ Form::hidden('_token', csrf_token(), ['id' => 'token']) }}
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_MT }}">
                    <div class="{{ VC::RW }}">
                        <div class="col-12 col-sm-12 {{ VC::CM6 }} {{ VC::CL6 ?? 'col-lg-6' }}">
                            <div class="{{ VC::FM_G }}" id="customer-box">
                                {{ Form::label('customer_id', __('Customer'), ['class' => VC::FM_LB]) }}
                                @php
                                    $routeName      = ViewsConstants::INV . '.customer';
                                    $customerRoute  = Route::has($routeName)
                                        ? route($routeName)
                                        : '#';
                                    $guardMsg = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::INV,
                                        'invoice_customer_route_unavailable'
                                    ) ?? 'Invoice customer route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                {{ Form::select(
                                    'customer_id',
                                    $customers,
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
                                    <script defer>
                                        (() => {
                                            const selectEl = document.getElementById('customer');
                                            if (!selectEl || selectEl.getAttribute('data-listener-active') === 'true') return;
                                            selectEl.setAttribute('data-listener-active', 'true');
                                            selectEl.addEventListener('change', event => {
                                                try {
                                                    const url = selectEl.getAttribute('data-url');
                                                    if (url && url !== '#') return;
                                                    event.preventDefault();
                                
                                                    const msg           = selectEl.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container    = document.createElement('div');
                                                        container.id = 'toast-container';
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
                                
                                                    selectEl.setAttribute('data-failed-route', 'true');
                                                } catch (e) {}
                                            });
                                        })();
                                    </script>
                                @endpush
                            </div>
                            <div id="customer_detail" class="d-none"></div>
                        </div>
                        <div class="col-12 col-sm-12 {{ VC::CM6 }} {{ VC::CL6 ?? 'col-lg-6' }}">
                            <div class="{{ VC::RW }}">
                                @php
                                    $fields = [
                                        ['name'=>'issue_date','type'=>'date','label'=>__('Issue Date'),'cols'=>6,'required'=>true],
                                        ['name'=>'due_date','type'=>'date','label'=>__('Due Date'),'cols'=>6,'required'=>true],
                                        ['name'=>'invoice_number','type'=>'readonly','label'=>__('Invoice Number'),'cols'=>6,'value'=>$invoice_number],
                                        ['name'=>'category_id','type'=>'select','label'=>__('Category'),'cols'=>6,'options'=>$category,'attrs'=>['class'=>VC::FM_CT . ' select2','required'=>'required']],
                                        ['name'=>'ref_number','type'=>'text','label'=>__('Ref Number'),'cols'=>6,'icon'=>'<span><i class="ti ti-joint"></i></span>','attrs'=>['class'=>VC::FM_CT]],
                                    ];
                                @endphp
                                @foreach($fields as $f)
                                    <div class="col-md-6">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                                            @php
                                                $attrs = $f['attrs'] ?? [];
                                                if (!empty($f['required']))
                                                    $attrs['required'] = 'required';
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
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include(ViewsConstants::CST_FD . '.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::C12 }}">
            <h5 class="mb-4">{{ __('Product & Services') }}</h5>
            <div class="{{ VC::CD }} repeater">
                <div class="item-section py-2">
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
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Tax') }} (%)</th>
                                    <th class="text-end">{{ __('Amount') }}<br><small class="text-danger fw-bold">{{ __('after tax & discount') }}</small></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    <td class="form-group pt-0" style="width:25%">
                                        @php
                                            $routeName      = ViewsConstants::INV . '.product';
                                            $productRoute   = Route::has($routeName)
                                                ? route($routeName)
                                                : (Route::has(Str::kebab($routeName))
                                                    ? route(Str::kebab($routeName))
                                                    : '#');
                                            $guardMsg       = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::INV,
                                                'invoice_product_route_unavailable'
                                            ) ?? 'Invoice product route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        {{ Form::select('item', $product_services, '', [
                                            'class'         => VC::FM_CT . ' select2 item',
                                            'data-url'      => $productRoute,
                                            'data-guard-msg'=> $guardMsg,
                                            'required'      => 'required',
                                        ]) }}
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const selectEl = document.querySelector('.item');
                                                    if (!selectEl || selectEl.getAttribute('data-listener-active') === 'true') return;
                                                    selectEl.setAttribute('data-listener-active', 'true');
                                                    selectEl.addEventListener('change', event => {
                                                        try {
                                                            const url = selectEl.getAttribute('data-url');
                                                            if (url && url !== '#') return;
                                                            event.preventDefault();
                                                            const msg           = selectEl.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                            selectEl.setAttribute('data-failed-route', 'true');
                                                        } catch (e) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            {{ Form::text('quantity', '', ['class'=>VC::FM_CT . ' quantity','required'=>'required','placeholder'=>__('Qty')]) }}
                                            <span class="unit input-group-text bg-transparent"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            {{ Form::text('price', '', ['class'=>VC::FM_CT . ' price','required'=>'required','placeholder'=>__('Price')]) }}
                                            <span class="input-group-text bg-transparent">{{ $user?->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            {{ Form::text('discount', '', ['class'=>VC::FM_CT . ' discount','required'=>'required','placeholder'=>__('Discount')]) }}
                                            <span class="input-group-text bg-transparent">{{ $user?->currencySymbol() }}</span>
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
                                    <td class="text-end amount">0.00</td>
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
                                    <td><strong>{{ __('Sub Total') }} ({{ $user?->currencySymbol() }})</strong></td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td><strong>{{ __('Discount') }} ({{ $user?->currencySymbol() }})</strong></td>
                                    <td class="text-end totalDiscount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td><strong>{{ __('Tax') }} ({{ $user?->currencySymbol() }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-primary"><strong>{{ __('Total Amount') }} ({{ $user?->currencySymbol() }})</strong></td>
                                    <td class="text-end totalAmount text-primary"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::C12 }} mt-3">
            <a href="{{ route($indexRoute) }}" data-guard-msg="{{ $indexGuardMsg }}" data-url="{{ $indexRoute }}" class="{{ VC::BT_LG }}" data-listener-alias="cancel-invoice">{{ __('Cancel') }}</a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const el = document.querySelector('[data-listener-alias="cancel-invoice"]');
                        if (!el || el.getAttribute('data-listener-active') === 'true') return;
                        el.setAttribute('data-listener-active', 'true');
                        el.addEventListener('click', event => {
                            try {
                                const url = el.getAttribute('data-url') ?? '#';
                                if (url !== '#') return;
                                event.preventDefault();
                                const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                el.setAttribute('data-failed-route', 'true');
                            } catch (e) {}
                        });
                    })();
                </script>
            @endpush
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
        </div>
        {{ Form::close() }}
    </div>
@endsection


