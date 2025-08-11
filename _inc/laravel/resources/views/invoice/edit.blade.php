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
    use Illuminate\Support\Facades\{Auth,Route};
    use App\Models\Utility;

    $user           = Auth::user();
    $lang           = Utility::fetchUserLang(user: $user);
    $invoiceIndexRouteName     = ViewsConstants::INV . '.index';
    $invoiceIndexUrl           = Route::has($invoiceIndexRouteName)
        ? route($invoiceIndexRouteName)
        : '#';
    $invoiceIndexGuardMsg      = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::INV,
        'invoice_index_route_unavailable'
    ) ?? 'Invoice index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Invoice Edit')}}
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
            href="{{ $invoiceIndexUrl }}"
            {{ $invoiceIndexUrl === '#' ? 'aria-disabled="true"' : '' }}
            data-url="{{ $invoiceIndexUrl }}"
            data-guard-msg="{{ $invoiceIndexGuardMsg }}"
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
    <li class="breadcrumb-item">{{__('Invoice Edit')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script async>
        window.translations = {
            ar:       {
                repeater_show_unavailable:   'فشل عرض المكرر',
                repeater_hide_unavailable:   'فشل إخفاء المكرر',
                repeater_setlist_unavailable:'فشل إعداد قائمة المكرر',
                repeater_create_unavailable: 'فشل إنشاء عنصر المكرر',
                repeater_delete_unavailable: 'فشل حذف عنصر المكرر',
                customer_change_unavailable: 'فشل جلب تفاصيل العميل',
                customer_remove_unavailable: 'فشل إزالة تفاصيل العميل',
                item_change_unavailable:     'فشل جلب تفاصيل الصنف',
                items_fetch_unavailable:     'فشل جلب عناصر الفاتورة',
                calculation_unavailable:     'فشل الحساب'
            },
            da:       {
                repeater_show_unavailable:   'Visning af gentager mislykkedes',
                repeater_hide_unavailable:   'Skjul af gentager mislykkedes',
                repeater_setlist_unavailable:'Indstilling af gentagerliste mislykkedes',
                repeater_create_unavailable: 'Oprettelse af gentagelseselement mislykkedes',
                repeater_delete_unavailable: 'Sletning af gentagelseselement mislykkedes',
                customer_change_unavailable: 'Hentning af kundedetaljer mislykkedes',
                customer_remove_unavailable: 'Fjernelse af kundedetaljer mislykkedes',
                item_change_unavailable:     'Hentning af vareoplysninger mislykkedes',
                items_fetch_unavailable:     'Hentning af fakturaelementer mislykkedes',
                calculation_unavailable:     'Beregning mislykkedes'
            },
            de:       {
                repeater_show_unavailable:   'Wiederholer-Anzeige fehlgeschlagen',
                repeater_hide_unavailable:   'Wiederholer-Ausblenden fehlgeschlagen',
                repeater_setlist_unavailable:'Einrichten der Wiederholungsliste fehlgeschlagen',
                repeater_create_unavailable: 'Erstellen des Wiederholungselements fehlgeschlagen',
                repeater_delete_unavailable: 'Löschen des Wiederholungselements fehlgeschlagen',
                customer_change_unavailable: 'Abruf der Kundendetails fehlgeschlagen',
                customer_remove_unavailable: 'Entfernen der Kundendetails fehlgeschlagen',
                item_change_unavailable:     'Abruf der Artikeldetails fehlgeschlagen',
                items_fetch_unavailable:     'Abruf der Rechnungspositionen fehlgeschlagen',
                calculation_unavailable:     'Berechnung fehlgeschlagen'
            },
            en:       {
                repeater_show_unavailable:   'Cannot show repeater',
                repeater_hide_unavailable:   'Cannot hide repeater',
                repeater_setlist_unavailable:'Cannot set repeater list',
                repeater_create_unavailable: 'Cannot create repeater item',
                repeater_delete_unavailable: 'Cannot delete repeater item',
                customer_change_unavailable: 'Cannot fetch customer details',
                customer_remove_unavailable: 'Cannot remove customer details',
                item_change_unavailable:     'Cannot fetch item details',
                items_fetch_unavailable:     'Cannot fetch invoice items',
                calculation_unavailable:     'Calculation failed'
            },
            // ... other languages with same keys ...
        };
    </script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (el?.getAttribute('data-sv-localized') === 'true' ||
                    el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (sessionStorage.getItem('erp-np-lang') ||
                                document.documentElement.lang ||
                                'en')
                                .toLowerCase()
                                .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg = window.translations?.[lang]?.[key] ||
                        el.getAttribute(DATA_GUARD_MSG) ||
                        window.translations?.['en']?.[key] ||
                        ERR_FB;
                    if (msg !== ERR_FB) {
                        el.setAttribute(DATA_GUARD_MSG, msg);
                        el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = (el, key) => {
                const message = el
                    ? getLocalizedMessage(el, key)
                    : ERR_FB;
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]')
                                    && window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
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
                    new bootstrap.Toast(
                        document.querySelector('#error-toast')
                    ).show();
                } else {
                    alert(message);
                }
            };

            try {
                if (typeof $ === 'undefined') {
                    console.error('jQuery is required');
                    return;
                }

                const selector = 'body';

                // Repeater init
                if ($(selector + ' .repeater').length) {
                    let $dragAndDrop, $repeater;
                    try {
                        $dragAndDrop = $(`${selector} .repeater tbody`).sortable({
                            handle: '.sort-handler'
                        });
                        $repeater = $(`${selector} .repeater`).repeater({
                            initEmpty: true,
                            defaultValues: { status: 1 },
                            show() {
                                try {
                                    $(this).slideDown();
                                    const $multi = $(this).find('input.multi');
                                    if ($multi.length) {
                                        $multi.MultiFile({
                                            max:      3,
                                            accept:   'png|jpg|jpeg',
                                            max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }}
                                        });
                                    }
                                    if ($('.select2').length) {
                                        $('.select2').select2();
                                    }
                                } catch {
                                    const el = this;
                                    if (!el.getAttribute(DATA_LISTENER_ADDED)) {
                                        $(el).on('click', () =>
                                            handleErrorDisplay(el, 'repeater_show_unavailable')
                                        );
                                        el.setAttribute(DATA_LISTENER_ADDED, 'true');
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
                            hide(del) {
                                try {
                                    $(this).slideUp(del);
                                    $(this).remove();
                                    let sub = 0;
                                    $('.amount').each((i, a) =>
                                        sub += parseFloat($(a).html()) || 0
                                    );
                                    $('.subTotal, .totalAmount').html(sub.toFixed(2));
                                } catch {
                                    const el = this;
                                    if (!el.getAttribute(DATA_LISTENER_ADDED)) {
                                        $(el).on('click', () =>
                                            handleErrorDisplay(el, 'repeater_hide_unavailable')
                                        );
                                        el.setAttribute(DATA_LISTENER_ADDED, 'true');
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
                            ready(setIdx) {
                                $dragAndDrop.on('drop', setIdx);
                            },
                            isFirstItemUndeletable: true
                        });

                        const val = $(`${selector} .repeater`).attr('data-value');
                        if (val) {
                            try {
                                const list = JSON.parse(val);
                                $repeater.setList(list);
                                list.forEach(v => {
                                    const $tr = $(`#sortable-table .id[value="${v.id}"]`).parent();
                                    $tr.find('.item').val(v.product_id);
                                    changeItem($tr.find('.item'));
                                });
                            } catch {
                                handleErrorDisplay(document.querySelector('.repeater'), 'repeater_setlist_unavailable');
                            }
                        }
                    } catch {
                        handleErrorDisplay(document.querySelector('.repeater'), 'repeater_create_unavailable');
                    }
                }

                // Customer change
                $(document).on('change', '#customer', function() {
                    try {
                        $('#customer_detail').removeClass('d-none').addClass('d-block');
                        $('#customer-box').removeClass('d-block').addClass('d-none');
                        const id  = $(this).val();
                        const url = $(this).data('url');
                        $.ajax({
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
                                    handleErrorDisplay(this, 'customer_change_unavailable');
                                }
                            }
                        });
                    } catch {
                        handleErrorDisplay(this, 'customer_change_unavailable');
                    }
                });

                // Customer remove
                $(document).on('click', '#remove', function() {
                    try {
                        $('#customer-box').removeClass('d-none').addClass('d-block');
                        $('#customer_detail').removeClass('d-block').addClass('d-none');
                    } catch {
                        handleErrorDisplay(this, 'customer_remove_unavailable');
                    }
                });

                const invoiceId = '{{$invoice->id}}';

                // changeItem fn
                const changeItem = ($el) => {
                    try {
                        const pid = $el.val();
                        const url = $el.data('url');
                        $.ajax({
                            url,
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': $('#token').val() },
                            data: { product_id: pid },
                            cache: false,
                            success(data) {
                                try {
                                    const item = JSON.parse(data);
                                    $.ajax({
                                        url:   '{{route(ViewsConstants::INV.".items")}}',
                                        type:  'GET',
                                        headers: { 'X-CSRF-TOKEN': $('#token').val() },
                                        data:  { invoice_id: invoiceId, product_id: pid },
                                        cache: false,
                                        success(dt) {
                                            try {
                                                const invItems = JSON.parse(dt);
                                                const $row     = $el.closest('tr');
                                                let qty, price, disc, desc;
                                                if (invItems) {
                                                    qty   = invItems.quantity;
                                                    price = invItems.price;
                                                    disc  = invItems.discount;
                                                    desc  = invItems.description;
                                                } else {
                                                    qty   = 1;
                                                    price = item.product.sale_price;
                                                    disc  = 0;
                                                    desc  = item.product.description;
                                                }
                                                $row.find('.quantity').val(qty);
                                                $row.find('.price').val(price);
                                                $row.find('.discount').val(disc);
                                                $row.find('.pro_description').val(desc);

                                                // taxes
                                                let html = '', rate = 0, ids = [];
                                                item.taxes.forEach(t => {
                                                    html += `<span class="badge bg-primary p-2 px-3 rounded mt-1 mr-1">`
                                                        + `${t.name} (${t.rate}%)</span>`;
                                                    ids.push(t.id);
                                                    rate += parseFloat(t.rate) || 0;
                                                });
                                                const base = (invItems ? invItems.price*invItems.quantity - disc
                                                                    : item.product.sale_price - disc);
                                                const taxp = (rate/100)*base || 0;
                                                $row.find('.itemTaxPrice').val(taxp.toFixed(2));
                                                $row.find('.itemTaxRate').val(rate.toFixed(2));
                                                $row.find('.taxes').html(html);
                                                $row.find('.tax').val(ids);
                                                $row.find('.unit').html(item.unit);

                                                // totals
                                                calculateTotals($row);
                                            } catch {
                                                handleErrorDisplay($el[0], 'items_fetch_unavailable');
                                            }
                                        }
                                    });
                                } catch {
                                    handleErrorDisplay($el[0], 'item_change_unavailable');
                                }
                            }
                        });
                    } catch {
                        handleErrorDisplay($el[0], 'item_change_unavailable');
                    }
                };

                // unified totals
                const calculateTotals = ($row) => {
                    try {
                        const qty  = parseFloat($row.find('.quantity').val()) || 0;
                        const prc  = parseFloat($row.find('.price').val())    || 0;
                        const disc = parseFloat($row.find('.discount').val()) || 0;
                        const net  = qty*prc - disc;
                        const rate = parseFloat($row.find('.itemTaxRate').val())||0;
                        const txp  = (rate/100)*net;
                        $row.find('.itemTaxPrice').val(txp.toFixed(2));
                        $row.find('.amount').html((net+txp).toFixed(2));

                        let sumQty=0, sumTax=0, sumNet=0;
                        $('.quantity').each((i,el)=> sumQty+=parseFloat($(el).val())||0);
                        $('.price').each((i,el)=> sumNet+=(parseFloat($(el).val())||0)*(parseFloat($('.quantity').eq(i).val())||0));
                        $('.itemTaxPrice').each((i,el)=> sumTax+=parseFloat($(el).val())||0);
                        const sumDisc = $('.discount').toArray().reduce((a,el)=> a+(parseFloat($(el).val())||0),0);
                        $('.subTotal').html(sumNet.toFixed(2));
                        $('.totalTax').html(sumTax.toFixed(2));
                        $('.totalAmount').html((sumNet - sumDisc + sumTax).toFixed(2));
                        $('.totalDiscount').html(sumDisc.toFixed(2));
                    } catch {
                        // fall back listener
                        const el = $row.find('.quantity')[0];
                        handleErrorDisplay(el, 'calculation_unavailable');
                    }
                };

                // recalc on qty/price/discount change
                $(document).on('keyup change', '.quantity, .price, .discount', function() {
                    calculateTotals($(this).closest('tr'));
                });

                // disable used items on create
                $(document).on('click', '[data-repeater-create]', function() {
                    $('.item :selected').each((_,o) =>
                        $(`.item option[value="${$(o).val()}"]`).prop('disabled', true)
                    );
                });

                // delete via ajax
                $(document).on('click', '[data-repeater-delete]', function() {
                    if (!confirm('Are you sure you want to delete this element?')) return;
                    try {
                        const $el = $(this).closest('tr');
                        const id  = $el.find('.id').val();
                        const amt = $el.find('.amount').html();
                        $.ajax({
                            url:   '{{route(ViewsConstants::INV.".product.destroy")}}',
                            type:  'POST',
                            headers: { 'X-CSRF-TOKEN': $('#token').val() },
                            data:  { id, amount: amt }
                        });
                    } catch {
                        handleErrorDisplay(this, 'repeater_delete_unavailable');
                    } finally {
                        $('.price, .discount').trigger('change');
                    }
                });

            } catch (e) {
                console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush

@section('content')
    <div class="row">
        @php
            $updateRouteName         = ViewsConstants::INV . '.update';
            $updateActionUrl         = Route::has($updateRouteName)
                ? route($updateRouteName, $invoice->id)
                : '#';
            $formId                  = 'invoice-update-form-' . $invoice->id;
            $updateGuardMsg          = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::INV,
                'invoice_update_route_unavailable'
            ) ?? 'Invoice update route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        {{ Collective\Html\FormFacade::model($invoice, [
            'route'           => $updateActionUrl,
            'method'        => 'PUT',
            'class'         => 'w-100',
            'id'            => $formId,
            'data-url'      => $updateActionUrl,
            'data-guard-msg'=> $updateGuardMsg,
        ]) }}
            <div class="{{ VC::C12 }}">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::FM_G }}" id="customer-box">
                                    {{ Collective\Html\FormFacade::label('customer_id', __('Customer'), ['class' => VC::FM_LB]) }}
                                    {{ Collective\Html\FormFacade::select('customer_id', $customers, null, [
                                        'class'    => VC::FM_CT_SL,
                                        'id'       => 'customer',
                                        'data-url' => route(ViewsConstants::INV.'.customer'),
                                        'required' => 'required'
                                    ]) }}
                                </div>
                                <div id="customer_detail" class="d-none"></div>
                            </div>

                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM6 }}">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Collective\Html\FormFacade::label('issue_date', __('Issue Date'), ['class' => VC::FM_LB]) }}
                                            <div class="form-icon-user">
                                                {{ Collective\Html\FormFacade::date('issue_date', null, [
                                                    'class'    => VC::FM_CT,
                                                    'required' => 'required'
                                                ]) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Collective\Html\FormFacade::label('due_date', __('Due Date'), ['class' => VC::FM_LB]) }}
                                            <div class="form-icon-user">
                                                {{ Collective\Html\FormFacade::date('due_date', null, [
                                                    'class'    => VC::FM_CT,
                                                    'required' => 'required'
                                                ]) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Collective\Html\FormFacade::label('invoice_number', __('Invoice Number'), ['class' => VC::FM_LB]) }}
                                            <div class="form-icon-user">
                                                <input type="text"
                                                    class="{{ VC::FM_CT }}"
                                                    value="{{ $invoice_number }}"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        {{ Collective\Html\FormFacade::label('category_id', __('Category'), ['class' => VC::FM_LB]) }}
                                        {{ Collective\Html\FormFacade::select('category_id', $category, null, [
                                            'class'    => VC::FM_CT_SL,
                                            'required' => 'required'
                                        ]) }}
                                    </div>
                                    <div class="{{ VC::CM6 }}">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Collective\Html\FormFacade::label('ref_number', __('Ref Number'), ['class' => VC::FM_LB]) }}
                                            <div class="form-icon-user">
                                                <span><i class="{{ VC::TI_JOINT ?? 'ti ti-joint' }}"></i></span>
                                                {{ Collective\Html\FormFacade::text('ref_number', null, [
                                                    'class' => VC::FM_CT
                                                ]) }}
                                            </div>
                                        </div>
                                    </div>

                                    @if(!$customFields->isEmpty())
                                        <div class="{{ VC::CM6 }}">
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
                <h5 class="{{ VC::DBL }} {{ VC::MB4 }}">{{ __('Product & Services') }}</h5>
                <div class="{{ VC::CD }} repeater" data-value='{!! json_encode($invoice->items) !!}'>
                    <div class="item-section {{ VC::PY2 }}">
                        <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                                <div class="all-button-box me-2">
                                    <a href="#" data-repeater-create="" class="{{ VC::BT_PRM }}" data-bs-toggle="modal" data-target="#add-bank">
                                        <i class="{{ VC::TI_PLS }}"></i> {{ __('Add item') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }} mb-0 table-custom-style"
                                data-repeater-list="items"
                                id="sortable-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Tax') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    {{ Collective\Html\FormFacade::hidden('id', null, ['class' => VC::FM_CT . ' id']) }}
                                    <td width="25%" class="{{ VC::FM_G }} pt-0">
                                        {{ Collective\Html\FormFacade::select('item', $product_services, null, [
                                            'class'    => VC::FM_CT_SL . ' item',
                                            'data-url' => route(ViewsConstants::INV.'.product')
                                        ]) }}
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Collective\Html\FormFacade::text('quantity', null, [
                                                'class'       => VC::FM_CT . ' quantity',
                                                'required'    => 'required',
                                                'placeholder' => __('Qty')
                                            ]) }}
                                            <span class="{{ VC::INP_GP_TXT }} {{ VC::BG_TPR }}"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Collective\Html\FormFacade::text('price', null, [
                                                'class'       => VC::FM_CT . ' price',
                                                'required'    => 'required',
                                                'placeholder' => __('Price')
                                            ]) }}
                                            <span class="{{ VC::INP_GP_TXT }} {{ VC::BG_TPR }}">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Collective\Html\FormFacade::text('discount', null, [
                                                'class'       => VC::FM_CT . ' discount',
                                                'required'    => 'required',
                                                'placeholder' => __('Discount')
                                            ]) }}
                                            <span class="{{ VC::INP_GP_TXT }} {{ VC::BG_TPR }}">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <div class="input-group colorpickerinput">
                                                <div class="taxes"></div>
                                                {{ Collective\Html\FormFacade::hidden('tax', null, ['class' => 'form-control tax']) }}
                                                {{ Collective\Html\FormFacade::hidden('itemTaxPrice', null, ['class' => 'form-control itemTaxPrice']) }}
                                                {{ Collective\Html\FormFacade::hidden('itemTaxRate', null, ['class' => 'form-control itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end amount">0.00</td>
                                    <td>
                                        <a href="#" class="{{ VC::TRS_M2 }} delete_item" data-repeater-delete></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="{{ VC::FM_G }}">
                                            {{ Collective\Html\FormFacade::textarea('description', null, [
                                                'class'       => 'form-control pro_description',
                                                'rows'        => 2,
                                                'placeholder' => __('Description')
                                            ]) }}
                                        </div>
                                    </td>
                                    <td colspan="5"></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4"></td>
                                    <td><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td><strong>{{ __('Discount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalDiscount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="blue-text"><strong>{{ __('Total Amount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalAmount blue-text">0.00</td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input
                    type="button"
                    id="{{ 'cancel-invoice-btn-' . $invoice->id }}"
                    value="{{ __('Cancel') }}"
                    class="{{ VC::BT_LG }} {{ VC::ME3 }}"
                    data-url="{{ $invoiceIndexUrl }}"
                    data-guard-msg="{{ $invoiceIndexGuardMsg }}"
                >
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            const btn = document.getElementById('{{ $cancelBtnId }}');
                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                            btn.setAttribute('data-listener-active', 'true');
                            btn.addEventListener('click', event => {
                                try {
                                    const url = btn.getAttribute('data-url') ?? '#';
                                    if (url !== '#') {
                                        window.location.href = url;
                                        return;
                                    }
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
                                } catch (e) {}
                            });
                        })();
                    </script>
                @endpush
                <input type="submit"
                    value="{{ __('Update') }}"
                    class="{{ VC::BT_PRM }}">
            </div>
        {{ Collective\Html\FormFacade::close() }}
    </div>
@endsection

