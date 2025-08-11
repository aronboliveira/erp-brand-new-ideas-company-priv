@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewsClassNamesConstants as VC
        YieldingConstants,
    };
    use Form as Form;
    use App\Models\{Bill,Utility};
    use Illuminate\Support\Facades\{Auth,Crypt,Route};
    $settings = Utility::settings();
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
    {{__('Bill Detail')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        window.translations = {
        ar: { shipping_update_failed: 'فشل تحديث عرض الشحن.' },
        da: { shipping_update_failed: 'Opdatering af forsendelsesvisning mislykkedes.' },
        de: { shipping_update_failed: 'Aktualisierung der Versandanzeige fehlgeschlagen.' },
        en: { shipping_update_failed: 'Failed to update shipping display.' },
        es: { shipping_update_failed: 'Error al actualizar la visualización de envío.' },
        fr: { shipping_update_failed: 'Échec de la mise à jour de l’affichage d’expédition.' },
        he: { shipping_update_failed: 'נכשל עדכון תצוגת המשלוח.' },
        it: { shipping_update_failed: 'Aggiornamento della visualizzazione di spedizione non riuscito.' },
        ja: { shipping_update_failed: '配送表示の更新に失敗しました。' },
        nl: { shipping_update_failed: 'Bijwerken van verzending weergave mislukt.' },
        pl: { shipping_update_failed: 'Aktualizacja wyświetlania wysyłki nie powiodła się.' },
        pt: { shipping_update_failed: 'Falha ao atualizar exibição de envio.' },
        'pt-br': { shipping_update_failed: 'Falha ao atualizar exibição de envio.' },
        ru: { shipping_update_failed: 'Не удалось обновить отображение доставки.' },
        tr: { shipping_update_failed: 'Kargo gösterimi güncellenemedi.' },
        zh: { shipping_update_failed: '更新送货显示失败。' }
        };
    </script>
    <script defer>
        (() => {
        const selector = '#shipping';
        const flag = 'data-listener-active';
        const toastId = 'toast-container';
        const guardMsg = 'data-guard-msg';
        const clientFlag = 'data-client-localized';
        const langKey = 'erp-np-lang';
        
        function getLocalizedMessage(key, el) {
            let msg = '# ERROR';
            if (el.getAttribute(clientFlag) === 'true') {
            msg = el.getAttribute(guardMsg) ?? msg;
            } else {
            let lang = (
                sessionStorage.getItem(langKey) ??
                document.documentElement.lang ??
                'en'
            ).toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg =
                translations?.[lang]?.[key] ??
                el.getAttribute(guardMsg) ??
                translations?.['en']?.[key] ??
                msg;
            if (msg !== '# ERROR') {
                el.setAttribute(guardMsg, msg);
                el.setAttribute(clientFlag, 'true');
            }
            }
            return msg;
        }
        
        function showError(message) {
            try {
            let container = document.getElementById(toastId);
            if (!container) {
                container = document.createElement('div');
                container.id = toastId;
                document.body.appendChild(container);
            }
            const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
            if (bs) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
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
        }
        
        let errorMessage = '';
        const onPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m => m.removedNodes.forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList: true, subtree: true });
        
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.querySelector(selector);
            if (!el || el.getAttribute(flag) === 'true') return;
            el.setAttribute(flag, 'true');
            el.addEventListener('click', onClick);
            new MutationObserver((m, o) => {
            m.forEach(mut => mut.removedNodes.forEach(node => {
                if (node === el) {
                el.removeEventListener('click', onClick);
                o.disconnect();
                }
            }));
            }).observe(document.body, { childList: true, subtree: true });
        });
        
        function onClick(event) {
            try {
            const el = event.currentTarget;
            const url = el.getAttribute('data-url');
            if (!url || url === '#') throw new Error('shipping_update_failed');
            $.ajax({
                url,
                type: 'GET',
                data: { is_display: el.checked }
            }).fail(() => {
                throw new Error('shipping_update_failed');
            });
            } catch (err) {
            errorMessage = getLocalizedMessage(err.message, document.querySelector(selector) || document.body);
            }
        }
        })();
    </script>
@endpush
@php
@endphp
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
    <li class="breadcrumb-item">{{$user?->billNumberFormat($bill->bill_id) }}</li>
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

@section('content')
    @can('send bill')
        @if($bill->status!=4)
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-body">
                            <div class="{{ VC::RW }} timeline-wrapper">
                                <div class="{{ VC::CM6 }} {{ VC::CL3 }} {{ VC::CL10 }}">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots"></span>
                                        <i class="{{ VC::TI_PLS }} {{ VC::TXT_WT }}"></i>
                                    </div>
                                    <h6 class="{{ VC::TXT_MT }} {{ VC::MY3 }}">{{ __('Create Bill') }}</h6>
                                    <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}">
                                        <i class="{{ VC::TI_CLD }} {{ VC::MR2 }}"></i>
                                        {{ __('Created on ') }} {{ $user?->dateFormat($bill->bill_date) }}
                                    </p>
                                    @can('edit bill')
                                        @php
                                            $lang               = Utility::fetchUserLang();
                                            $billEditRoute      = Route::has(ViewsConstants::BIL . '.edit')
                                                ? route(ViewsConstants::BIL . '.edit', Crypt::encrypt($bill->id))
                                                : '#';
                                            $billEditLinkId     = 'bill-edit-link-' . $bill->id;
                                            $billEditGuardMsg   = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BIL,
                                                'bill_edit_route_unavailable'
                                            ) ?? 'Bill edit route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <a
                                            id="{{ $billEditLinkId }}"
                                            href="{{ $billEditRoute }}"
                                            data-url="{{ $billEditRoute }}"
                                            data-guard-msg="{{ $billEditGuardMsg }}"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Edit') }}"
                                        >
                                            <i class="{{ VC::TI_PC_WT }} {{ VC::MR2 }}"></i>{{ __('Edit') }}
                                        </a>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const btn = document.getElementById('{{ $billEditLinkId }}');
                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                    btn.setAttribute('data-listener-active', 'true');
                                                    btn.addEventListener('click', event => {
                                                        try {
                                                            const href = btn.getAttribute('href');
                                                            const url  = btn.getAttribute('data-url');
                                                            if ((href && href !== '#') || (url && url !== '#')) return;
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
                                    @endcan
                                </div>
                                <div class="{{ VC::CM6 }} {{ VC::CL3 }} {{ VC::CL10 }}">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots"></span>
                                        <i class="{{ VC::TI_MAIL }} {{ VC::TXT_MT }}"></i>
                                    </div>
                                    <h6 class="{{ VC::TXT_MT }} {{ VC::MY3 }}">{{ __('Send Bill') }}</h6>
                                    <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}">
                                        @if($bill->status != 0)
                                            <i class="{{ VC::TI_CLD }} {{ VC::MR2 }}"></i>
                                            {{ __('Sent on') }} {{ $user?->dateFormat($bill->send_date) }}
                                        @else
                                            @can('send bill')
                                                <small>{{ __('Status') }} : {{ __('Not Sent') }}</small>
                                            @endcan
                                        @endif
                                    </p>
                                    @if($bill->status == 0)
                                        @can('send bill')
                                            @php
                                                $sendRoute    = Route::has(ViewsConstants::BIL . '.sent')
                                                    ? route(ViewsConstants::BIL . '.sent', $bill->id)
                                                    : '#';
                                                $sendBtnId    = 'bill-send-btn-' . $bill->id;
                                                $sendGuardMsg = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BIL,
                                                    'bill_sent_route_unavailable'
                                                ) ?? 'Bill send route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a
                                                id="{{ $sendBtnId }}"
                                                href="{{ $sendRoute }}"
                                                data-url="{{ $sendRoute }}"
                                                data-guard-msg="{{ $sendGuardMsg }}"
                                                class="{{ VC::BT_SM_WRN }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Send') }}"
                                            >
                                                <i class="{{ VC::TI_SRC }} {{ VC::MR2 }}"></i>{{ __('Send') }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const btn = document.getElementById('{{ $sendBtnId }}');
                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                        btn.setAttribute('data-listener-active', 'true');
                                                        btn.addEventListener('click', event => {
                                                            try {
                                                                const href = btn.getAttribute('href');
                                                                const url  = btn.getAttribute('data-url');
                                                                if ((href && href !== '#') || (url && url !== '#')) return;
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
                                        @endcan
                                    @endif
                                </div>
                                <div class="{{ VC::CM6 }} {{ VC::CL3 }} {{ VC::CL10 }}">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots"></span>
                                        <i class="{{ VC::TI_RPT_MN }} {{ VC::TXT_MT }}"></i>
                                    </div>
                                    <h6 class="{{ VC::TXT_MT }} {{ VC::MY3 }}">{{ __('Get Paid') }}</h6>
                                    <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB3 }}">
                                        {{ __('Status') }} : {{ __('Awaiting payment') }}
                                    </p>
                                    @if($bill->status != 0)
                                        @can('create payment bill')
                                            @php
                                                $paymentRoute       = Route::has(ViewsConstants::BIL . '.payment')
                                                    ? route(ViewsConstants::BIL . '.payment', $bill->id)
                                                    : '#';
                                                $paymentBtnId       = 'bill-payment-btn-' . $bill->id;
                                                $paymentGuardMsg    = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BIL,
                                                    'bill_payment_route_unavailable'
                                                ) ?? 'Add Payment route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a
                                                id="{{ $paymentBtnId }}"
                                                href="#"
                                                data-url="{{ $paymentRoute }}"
                                                data-guard-msg="{{ $paymentGuardMsg }}"
                                                data-ajax-popup="true"
                                                data-title="{{ __('Add Payment') }}"
                                                class="{{ VC::BT_SM_INF }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Add Payment') }}"
                                            >
                                                <i class="{{ VC::TI_RPT_MN }} {{ VC::MR2 }}"></i>{{ __('Add Payment') }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const btn = document.getElementById('{{ $paymentBtnId }}');
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
                                                                }
                                                            } catch (e) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endcan
    @if($user?->type=='company')
        @if($bill->status!=0)
            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
                <div class="col-md-12 {{ VC::DFL_AIC_JCB }} justify-content-md-end">
                    @if(!empty($billPayment))
                        <div class="all-button-box mx-2">
                            @php
                                $debitNoteRoute        = Route::has(ViewsConstants::BIL . '.debit.note')
                                    ? route(ViewsConstants::BIL . '.debit.note', $bill->id)
                                    : '#';
                                $debitNoteBtnId        = 'bill-debit-note-btn-' . $bill->id;
                                $debitNoteGuardMsg     = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::BIL,
                                    'bill_debit_note_route_unavailable'
                                ) ?? 'Add Debit Note route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <a
                                id="{{ $debitNoteBtnId }}"
                                href="#"
                                data-url="{{ $debitNoteRoute }}"
                                data-guard-msg="{{ $debitNoteGuardMsg }}"
                                data-ajax-popup="true"
                                data-title="{{ __('Add Debit Note') }}"
                                class="{{ VC::BT_SM_PM }}"
                                data-bs-toggle="tooltip"
                                title="{{ __('Add Debit Note') }}"
                            >
                                {{ __('Add Debit Note') }}
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const btn = document.getElementById('{{ $debitNoteBtnId }}');
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
                                            } catch (e) {}
                                        });
                                    })();
                                </script>
                            @endpush
                        </div>
                    @endif
                    <div class="all-button-box mx-2">
                        @php
                            $resentRoute          = Route::has(ViewsConstants::BIL . '.resent')
                                ? route(ViewsConstants::BIL . '.resent', $bill->id)
                                : '#';
                            $resentBtnId          = 'bill-resent-btn-' . $bill->id;
                            $resentGuardMsg       = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::BIL,
                                'bill_resent_route_unavailable'
                            ) ?? 'Resend Bill route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a
                            id="{{ $resentBtnId }}"
                            href="{{ $resentRoute }}"
                            data-url="{{ $resentRoute }}"
                            data-guard-msg="{{ $resentGuardMsg }}"
                            class="{{ VC::BT_SM_PM }}"
                            data-bs-toggle="tooltip"
                            title="{{ __('Resend Bill') }}"
                        >
                            {{ __('Resend Bill') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const btn = document.getElementById('{{ $resentBtnId }}');
                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                    btn.setAttribute('data-listener-active', 'true');
                                    btn.addEventListener('click', event => {
                                        try {
                                            const url = btn.getAttribute('data-url');
                                            if (url && url !== '#') return;
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
                    </div>
                    <div class="all-button-box">
                        @php
                            $downloadRoute    = Route::has(ViewsConstants::BIL . '.pdf')
                                ? route(ViewsConstants::BIL . '.pdf', Crypt::encrypt($bill->id))
                                : '#';
                            $downloadLinkId   = 'bill-download-' . $bill->id;
                            $downloadGuardMsg = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::BIL,
                                'bill_pdf_route_unavailable'
                            ) ?? 'Bill PDF route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a
                            id="{{ $downloadLinkId }}"
                            href="{{ $downloadRoute }}"
                            target="_blank"
                            class="{{ VC::BT_SM_PM }}"
                            data-url="{{ $downloadRoute }}"
                            data-guard-msg="{{ $downloadGuardMsg }}"
                        >
                            {{ __('Download') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const link = document.getElementById('{{ $downloadLinkId }}');
                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                    link.setAttribute('data-listener-active', 'true');
                        
                                    link.addEventListener('click', event => {
                                        try {
                                            const href = link.getAttribute('href');
                                            const url  = link.getAttribute('data-url');
                                            if ((href && href !== '#') || (url && url !== '#')) return;
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
                    </div>
                </div>
            </div>
        @endif
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="{{ VC::CLMS3 }} {{ VC::DFL }} {{ VC::ALC }}">
                                    <div class="{{ VC::C12 }} {{ VC::CL3 }}">
                                        <h4>{{ __('Bill') }}</h4>
                                    </div>
                                    <div class="{{ VC::C12 }} {{ VC::CL3 }} {{ VC::FEND }}">
                                        <h4 class="{{ VC::H6 }} {{ VC::TXT_WT }} invoice-number">
                                            {{ $user?->billNumberFormat($bill->bill_id) }}
                                        </h4>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::C12 }} {{ VC::DFL_AIC_JCB }}">
                                    <div class="{{ VC::ME4 }}">
                                        <small>
                                            <strong>{{ __('Issue Date') }} :</strong><br>
                                            {{ $user?->dateFormat($bill->bill_date) }}<br><br>
                                        </small>
                                    </div>
                                    <div>
                                        <small>
                                            <strong>{{ __('Due Date') }} :</strong><br>
                                            {{ $user?->dateFormat($bill->due_date) }}<br><br>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <small class="font-style">
                                        <strong>{{__('Billed To')}} :</strong><br>
                                        @if(!empty($vendor->billing_name))
                                            {{!empty($vendor->billing_name)?$vendor->billing_name:''}}<br>
                                            {{!empty($vendor->billing_address)?$vendor->billing_address:''}}<br>
                                            {{!empty($vendor->billing_city)?$vendor->billing_city:'' .', '}}<br>
                                            {{!empty($vendor->billing_state)?$vendor->billing_state:'',', '}},
                                            {{!empty($vendor->billing_zip)?$vendor->billing_zip:''}}<br>
                                            {{!empty($vendor->billing_country)?$vendor->billing_country:''}}<br>
                                            {{!empty($vendor->billing_phone)?$vendor->billing_phone:''}}<br>
                                            @if($settings['vat_gst_number_switch'] == 'on')
                                                <strong>{{__('Tax Number ')}} : </strong>{{!empty($vendor->tax_number)?$vendor->tax_number:''}}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </small>
                                </div>
                                @if(Utility::getValByName('shipping_display')=='on')
                                    <div class="col">
                                        <small>
                                            <strong>{{__('Shipped To')}} :</strong><br>
                                            @if(!empty($vendor->shipping_name))
                                            {{!empty($vendor->shipping_name)?$vendor->shipping_name:''}}<br>
                                            {{!empty($vendor->shipping_address)?$vendor->shipping_address:''}}<br>
                                            {{!empty($vendor->shipping_city)?$vendor->shipping_city:'' . ', '}}<br>
                                            {{!empty($vendor->shipping_state)?$vendor->shipping_state:'' .', '}},
                                            {{!empty($vendor->shipping_zip)?$vendor->shipping_zip:''}}<br>
                                            {{!empty($vendor->shipping_country)?$vendor->shipping_country:''}}<br>
                                            {{!empty($vendor->shipping_phone)?$vendor->shipping_phone:''}}<br>
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </div>
                                @endif
                                <div class="col">
                                    @php
                                        $qrRoute        = Route::has(ViewsConstants::BIL . '.link.copy')
                                            ? route(ViewsConstants::BIL . '.link.copy', Crypt::encrypt($bill->id))
                                            : '#';
                                        $qrId           = 'bill-qr-copy-' . $bill->id;
                                        $qrGuardMsg     = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BIL,
                                            'bill_link_copy_route_unavailable'
                                        ) ?? 'Bill link copy route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div
                                        id="{{ $qrId }}"
                                        class="float-end mt-3"
                                        data-url="{{ $qrRoute }}"
                                        data-guard-msg="{{ $qrGuardMsg }}"
                                        style="cursor: pointer;"
                                    >
                                        {!! DNS2D::getBarcodeHTML($qrRoute, 'QRCODE', 2, 2) !!}
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const el = document.getElementById('{{ $qrId }}');
                                                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                                                el.setAttribute('data-listener-active', 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        if (!url || url === '#') {
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
                                                            return;
                                                        }
                                                        navigator.clipboard.writeText(url);
                                                    } catch (e) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                </div>
                            </div>
                            <div class="row mt-3">
                                @php
                                    $statusClasses = [
                                        0 => 'bg-primary',
                                        1 => 'bg-warning', 
                                        2 => 'bg-danger',
                                        3 => 'bg-info',
                                        4 => 'bg-primary'
                                    ];
                                @endphp
                                <div class="col">
                                    <small>
                                        <strong>{{__('Status')}} :</strong><br>
                                        <span class="badge {{ $statusClasses[$bill->status] ?? 'bg-secondary' }} p-2 px-3 rounded">
                                            {{ __(Bill::$statuses[$bill->status]) }}
                                        </span>
                                    </small>
                                </div>
                                @if(!empty($customFields) && count($bill->customField)>0)
                                    @foreach($customFields as $field)
                                        <div class="col text-md-end">
                                            <small>
                                                <strong>{{$field->name}} :</strong><br>
                                                {{!empty($bill->customField)?$bill->customField[$field->id]:'-'}}
                                                <br><br>
                                            </small>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-bold mb-2">{{__('Product Summary')}}</div>
                                    <small class="mb-2">{{__('All items here cannot be deleted.')}}</small>
                                    <div class="table-responsive mt-3">
                                        <table class="table mb-0 table-striped">
                                            @php
                                                $headers = [
                                                    ['text' => '#', 'data-width' => '40'],
                                                    ['text' => __('Product')],
                                                    ['text' => __('Quantity')],
                                                    ['text' => __('Rate')],
                                                    ['text' => __('Discount')],
                                                    ['text' => __('Tax')],
                                                    ['text' => __('Chart Of Account')],
                                                    ['text' => __('Account Amount')],
                                                    ['text' => __('Description')],
                                                    ['text' => __('Price'), 'class' => 'text-end text-dark', 'width' => '12%', 'subtitle' => __('after tax & discount')],
                                                    ['text' => '', 'class' => '']
                                                ];
                                            @endphp
                                            <tr>
                                                @foreach($headers as $header)
                                                    <th class="{{ $header['class'] ?? 'text-dark' }}"
                                                        @if(isset($header['width'])) width="{{ $header['width'] }}" @endif
                                                        @if(isset($header['data-width'])) data-width="{{ $header['data-width'] }}" @endif>
                                                        {{ $header['text'] }}
                                                        @if(isset($header['subtitle']))
                                                            <br><small class="text-danger font-weight-bold">{{ $header['subtitle'] }}</small>
                                                        @endif
                                                    </th>
                                                @endforeach
                                            </tr>
                                            @php
                                               $totalQuantity=0;
                                               $totalRate=0;
                                               $totalTaxPrice=0;
                                               $totalDiscount=0;
                                               $taxesData=[];
                                            @endphp
                                            @foreach($items as $key =>$item)
                                                @if(!empty($item->tax))
                                                    @php
                                                        $taxes= Utility::tax($item->tax);
                                                        $totalQuantity+=$item->quantity;
                                                        $totalRate+=$item->price;
                                                        $totalDiscount+=$item->discount;
                                                        foreach($taxes as $taxe){
                                                            $taxDataPrice= Utility::taxRate($taxe->rate,$item->price,$item->quantity,$item->discount);
                                                            if (array_key_exists($taxe->name,$taxesData))
                                                            {
                                                                $taxesData[$taxe->name] = $taxesData[$taxe->name]+$taxDataPrice;
                                                            }
                                                            else
                                                            {
                                                                $taxesData[$taxe->name] = $taxDataPrice;
                                                            }
                                                        }
                                                    @endphp
                                                @endif

                                                @if(!empty($item->product_id))
                                                        <tr>
                                                            <td>{{$key+1}}</td>
                                                            
                                                            @php
                                                                $itemProduct = $item->product();
                                                                $unit = !empty($itemProduct)?$itemProduct->unit_id:'-';
                                                                $unitName = App\Models\ProductServiceUnit::find($unit);
                                                            @endphp
                                                            <td>{{!empty($itemProduct)?$itemProduct->name:'-'}}</td>
                                                            <td>{{$item->quantity . ' (' . $unitName->name . ')'}}</td>
                                                            <td>{{$user?->priceFormat($item->price)}}</td>
                                                            <td>{{$user?->priceFormat($item->discount)}}</td>
                                                            <td>
                                                                @if(!empty($item->tax))
                                                                    <table>
                                                                        @php
                                                                            $totalTaxRate = 0;
                                                                        @endphp
                                                                        @foreach($taxes as $tax)

                                                                            @php
                                                                                $taxPrice= Utility::taxRate($tax->rate,$item->price,$item->quantity,$item->discount) ;
                                                                                $totalTaxPrice+=$taxPrice;
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{$tax->name .' ('.$tax->rate .'%)'}}</td>
                                                                                <td>{{$user?->priceFormat($taxPrice)}}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </table>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>

                                                            @php
                                                                $chartAccount = \App\Models\ChartOfAccount::find($item->chart_account_id);
                                                            @endphp

                                                            <td>{{!empty($chartAccount) ? $chartAccount->name : '-'}}</td>
                                                            <td>{{$user?->priceFormat($item->amount)}}</td>

                                                            <td>{{!empty($item->description)?$item->description:'-'}}</td>

                                                            <td class="text-end">{{$user?->priceFormat(($item->price * $item->quantity - $item->discount) + $totalTaxPrice)}}</td>
                                                            <td></td>
                                                        </tr>
                                                    @else
                                                    <tr>
                                                        <td>{{$key+1}}</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        @php
                                                            $chartAccount = \App\Models\ChartOfAccount::find($item['chart_account_id']);
                                                        @endphp
                                                        <td>{{!empty($chartAccount) ? $chartAccount->name : '-'}}</td>
                                                        <td>{{$user?->priceFormat($item['amount'])}}</td>
                                                        <td>-</td>
                                                        <td class="text-end">{{$user?->priceFormat($item['amount'])}}</td>
                                                        <td></td>


                                                    </tr>

                                                @endif


                                            @endforeach
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td><b>{{__('Total')}}</b></td>
                                                    <td><b>{{$totalQuantity}}</b></td>
                                                    <td><b>{{$user?->priceFormat($totalRate)}}</b></td>
                                                    <td><b>{{$user?->priceFormat($totalDiscount)}}</b></td>
                                                    <td><b>{{$user?->priceFormat($totalTaxPrice)}}</b></td>
                                                    <td></td>
                                                    <td><b>{{$user?->priceFormat($bill->getAccountTotal())}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b>{{__('Sub Total')}}</b></td>
                                                    <td class="text-end">{{$user?->priceFormat($bill->getSubTotal())}}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b>{{__('Discount')}}</b></td>
                                                    <td class="text-end">{{$user?->priceFormat($bill->getTotalDiscount())}}</td>
                                                </tr>
                                                @if(!empty($taxesData))
                                                    @foreach($taxesData as $taxName => $taxPrice)
                                                        <tr>
                                                            <td colspan="8"></td>
                                                            <td class="text-end"><b>{{$taxName}}</b></td>
                                                            <td class="text-end">{{ $user?->priceFormat($taxPrice) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                @php
                                                    $billSummaryRows = [
                                                        ['label' => __('Total'), 'value' => $bill->getTotal(), 'class' => 'blue-text'],
                                                        ['label' => __('Paid'), 'value' => ($bill->getTotal() - $bill->getDue()) - ($bill->billTotalDebitNote()), 'class' => ''],
                                                        ['label' => __('Debit Note'), 'value' => $bill->billTotalDebitNote(), 'class' => ''],
                                                        ['label' => __('Due'), 'value' => $bill->getDue(), 'class' => '']
                                                    ];
                                                @endphp
                                                @foreach ($billSummaryRows as $row)
                                                    <tr>
                                                        <td colspan="8"></td>
                                                        <td class="{{ $row['class'] }} text-end"><b>{{ $row['label'] }}</b></td>
                                                        <td class="{{ $row['class'] }} text-end">{{ $user?->priceFormat($row['value']) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <h5 class="d-inline-block mb-5">{{ __('Payment Summary') }}</h5>
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    @php
                                        $headers = [
                                            __('Payment Receipt'),
                                            __('Date'),
                                            __('Amount'),
                                            __('Account'),
                                            __('Reference'),
                                            __('Description')
                                        ];
                                    @endphp
                                    @foreach ($headers as $header)
                                        <th class="text-dark">{{ $header }}</th>
                                    @endforeach
                                    @can('delete payment bill')
                                        <th class="text-dark">{{ __('Action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bill->payments as $payment)
                                    <tr>
                                        <td>
                                            @if(!empty($payment->add_receipt))
                                                @php
                                                    $receiptUrl          = asset(Storage::url('uploads/payment') . '/' . $payment->add_receipt);
                                                    $receiptLinkId       = 'payment-receipt-' . $payment->id;
                                                    $receiptGuardMsg     = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::BIL,
                                                        'payment_receipt_unavailable'
                                                    ) ?? 'Payment receipt is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <a
                                                    id="{{ $receiptLinkId }}"
                                                    href="{{ $receiptUrl }}"
                                                    download
                                                    target="_blank"
                                                    class="{{ VC::BT }} {{ VC::BT_SM }} btn-secondary btn-icon rounded-pill"
                                                    data-url="{{ $receiptUrl }}"
                                                    data-guard-msg="{{ $receiptGuardMsg }}"
                                                >
                                                    <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const link = document.getElementById('{{ $receiptLinkId }}');
                                                            if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                            link.setAttribute('data-listener-active', 'true');
                                                            link.addEventListener('click', event => {
                                                                try {
                                                                    const url = link.getAttribute('data-url');
                                                                    if (!url || url === '#') {
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
                                                                    }
                                                                } catch (e) {}
                                                            });
                                                        })();
                                                    </script>
                                                @endpush
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $user?->dateFormat($payment->date) }}</td>
                                        <td>{{ $user?->priceFormat($payment->amount) }}</td>
                                        <td>{{ optional($payment->bankAccount)->bank_name . ' ' . optional($payment->bankAccount)->holder_name }}</td>
                                        <td>{{ $payment->reference }}</td>
                                        <td>{{ $payment->description }}</td>
                                        @can('delete bill product')
                                            <td>
                                                @php
                                                    $deletePaymentRoute         = Route::has(ViewsConstants::BIL . '.payment.destroy')
                                                        ? route(ViewsConstants::BIL . '.payment.destroy', [$bill->id, $payment->id])
                                                        : '#';
                                                    $deletePaymentFormId        = 'delete-form-' . $payment->id;
                                                    $deletePaymentBtnId         = 'delete-payment-btn-' . $payment->id;
                                                    $deletePaymentGuardMsg      = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::BIL,
                                                        'payment_destroy_route_unavailable'
                                                    ) ?? 'Payment delete route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'route'            => $deletePaymentRoute,
                                                        'method'         => 'post',
                                                        'id'             => $deletePaymentFormId,
                                                        'data-url'       => $deletePaymentRoute,
                                                        'data-guard-msg' => $deletePaymentGuardMsg,
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deletePaymentBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $deletePaymentFormId }}').submit();"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const btn = document.getElementById('{{ $deletePaymentBtnId }}');
                                                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                            btn.setAttribute('data-listener-active', 'true');
                                                            btn.addEventListener('click', event => {
                                                                try {
                                                                    const href = btn.getAttribute('href');
                                                                    const url  = btn.getAttribute('data-url');
                                                                    if ((href && href !== '#') || (url && url !== '#')) return;
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
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-dark">{{ __('No Data Found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <h5 class="mb-5">{{ __('Debit Note Summary') }}</h5>
                    <div class="table-responsive">
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th class="text-dark">{{ __('Date') }}</th>
                                    <th class="text-dark">{{ __('Amount') }}</th>
                                    <th class="text-dark">{{ __('Description') }}</th>
                                    @if(Gate::check('edit debit note') || Gate::check('delete debit note'))
                                        <th class="text-dark">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bill->debitNote as $debitNote)
                                    <tr>
                                        <td>{{ $user?->dateFormat($debitNote->date) }}</td>
                                        <td>{{ $user?->priceFormat($debitNote->amount) }}</td>
                                        <td>{{ $debitNote->description }}</td>
                                        @if(Gate::check('edit debit note') || Gate::check('delete debit note'))
                                            <td>
                                                @can('edit debit note')
                                                    <a
                                                        href="#"
                                                        data-url="{{ route(ViewsConstants::BIL.'.edit.debit.note', [$debitNote->bill, $debitNote->id]) }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Debit Note') }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                @endcan
    
                                                @can('delete debit note')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route'  => [ViewsConstants::BIL.'.delete.debit.note', $debitNote->bill, $debitNote->id],
                                                            'id'     => 'delete-form-'.$debitNote->id,
                                                        ]) !!}
                                                            <a
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{$debitNote->id}}').submit();"
                                                            >
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-dark">{{ __('No Data Found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
