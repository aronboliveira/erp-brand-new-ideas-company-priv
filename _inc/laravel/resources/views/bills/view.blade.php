@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewsClassNamesConstants as VC
        YieldingConstants,
    };
    use Form as Form;
    use App\Models\{Bill,ChartOfAccount,Utility};
    use Illuminate\Support\Facades\{Auth,Crypt,Route};
    use Illuminate\Support\{Collection, Str};
    $settings = Utility::settings();
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $billIndexRoute = Route::has(VW::BIL . '.index')
        ? route(VW::BIL . '.index')
        : '#';
    $billIndexId  = 'bill-index-link';
    $billIndexMsg = Utility::fetchLinkMessage(
        $lang,
        VW::BIL,
        'bill_index_route_unavailable'
    ) ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Bill Detail')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
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

@section(YieldingConstants::ADM_CTT)
    @if(!empty($bill) && isset($bill))
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
                                            $billEditRoute    = Route::has(VW::BIL . '.edit')
                                                ? route(VW::BIL . '.edit', Crypt::encrypt($bill->id))
                                                : '#';
                                            $billEditLinkId   = 'bill-edit-link-' . $bill->id;
                                            $billEditGuardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                VW::BIL,
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
                                                    const btn = document.getElementById("{{ $billEditLinkId }}");
                                                    if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                    btn.setAttribute("data-listener-active", "true");
                                                    btn.addEventListener("click", event => {
                                                        try {
                                                        const href = btn.getAttribute("href");
                                                        const url = btn.getAttribute("data-url");
                                                        if ((href && href !== "#") || (url && url !== "#")) return;
                                                        event.preventDefault();
                                                        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container = document.getElementById("toast-container");
                                                        if (!container) {
                                                            container = document.createElement("div");
                                                                      container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement("div");
                                                            toastEl.className = "toast";
                                                            toastEl.setAttribute("role", "alert");
                                                            toastEl.setAttribute("aria-live", "assertive");
                                                            toastEl.setAttribute("aria-atomic", "true");
                                                            const body = document.createElement("div");
                                                            body.className = "toast-body";
                                                            body.textContent = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        btn.setAttribute("data-failed-route", "true");
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
                                                $sendRoute    = Route::has(VW::BIL . '.sent')
                                                    ? route(VW::BIL . '.sent', $bill->id)
                                                    : '#';
                                                $sendBtnId    = 'bill-send-btn-' . $bill->id;
                                                $sendGuardMsg = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::BIL,
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
                                                        const btn = document.getElementById("{{ $sendBtnId }}");
                                                        if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                        btn.setAttribute("data-listener-active", "true");
                                                        btn.addEventListener("click", event => {
                                                            try {
                                                            const href = btn.getAttribute("href");
                                                            const url = btn.getAttribute("data-url");
                                                            if ((href && href !== "#") || (url && url !== "#")) return;
                                                            event.preventDefault();
                                                            const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById("toast-container");
                                                            if (!container) {
                                                                container = document.createElement("div");
                                                                          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement("div");
                                                                toastEl.className = "toast";
                                                                toastEl.setAttribute("role", "alert");
                                                                toastEl.setAttribute("aria-live", "assertive");
                                                                toastEl.setAttribute("aria-atomic", "true");
                                                                const body = document.createElement("div");
                                                                body.className = "toast-body";
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            btn.setAttribute("data-failed-route", "true");
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
                                                $paymentRoute    = Route::has(VW::BIL . '.payment')
                                                    ? route(VW::BIL . '.payment', $bill->id)
                                                    : '#';
                                                $paymentBtnId    = 'bill-payment-btn-' . $bill->id;
                                                $paymentGuardMsg = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::BIL,
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
                                                        const btn = document.getElementById("{{ $paymentBtnId }}");
                                                        if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                        btn.setAttribute("data-listener-active", "true");
                                                        btn.addEventListener("click", event => {
                                                            try {
                                                            const url = btn.getAttribute("data-url");
                                                            if (!url || url === "#") {
                                                                event.preventDefault();
                                                                const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                let container = document.getElementById("toast-container");
                                                                if (!container) {
                                                                container = document.createElement("div");
                                                                          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
                                                                document.body.appendChild(container);
                                                                }
                                                                if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement("div");
                                                                toastEl.className = "toast";
                                                                toastEl.setAttribute("role", "alert");
                                                                toastEl.setAttribute("aria-live", "assertive");
                                                                toastEl.setAttribute("aria-atomic", "true");
                                                                const body = document.createElement("div");
                                                                body.className = "toast-body";
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                } else {
                                                                alert(msg);
                                                                }
                                                                btn.setAttribute("data-failed-route", "true");
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
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
        @if($bill->status!=0)
            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
                <div class="col-md-12 {{ VC::DFL_AIC_JCB }} justify-content-md-end">
                    @if(!empty($billPayment))
                        @can('create debit note')
                            <div class="all-button-box mx-2">
                                @php
                                    $debitNoteRoute    = Route::has(VW::BIL . '.debit.note')
                                        ? route(VW::BIL . '.debit.note', $bill->id)
                                        : '#';
                                    $debitNoteBtnId    = 'bill-debit-note-btn-' . $bill->id;
                                    $debitNoteGuardMsg = Utility::fetchLinkMessage(
                                        $lang,
                                        VW::BIL,
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
                        @endcan
                    @endif

                    @can('send bill')
                        <div class="all-button-box mx-2">
                            @php
                                $resentRoute    = Route::has(VW::BIL . '.resent')
                                    ? route(VW::BIL . '.resent', $bill->id)
                                    : '#';
                                $resentBtnId    = 'bill-resent-btn-' . $bill->id;
                                $resentGuardMsg = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::BIL,
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
                    @endcan

                    @can('show bill')
                        <div class="all-button-box">
                            @php
                                $downloadRoute    = Route::has(VW::BIL . '.pdf')
                                    ? route(VW::BIL . '.pdf', Crypt::encrypt($bill->id))
                                    : '#';
                                $downloadLinkId   = 'bill-download-' . $bill->id;
                                $downloadGuardMsg = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::BIL,
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
                    @endcan
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
                                        @php
                                            $formattedBillNumber = ($user ?? null) && method_exists($user, 'billNumberFormat') && isset($bill->bill_id) ? (string) $user->billNumberFormat($bill->bill_id) : '';
                                            $billNumberOutput    = $formattedBillNumber !== '' ? $formattedBillNumber : __('No bill number available');
                                        @endphp
                                        <h4 class="{{ VC::H6 }} {{ VC::TXT_WT }} invoice-number">
                                            {{ $billNumberOutput }}
                                        </h4>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::C12 }} {{ VC::DFL_AIC_JCB }}">
                                    @php
                                        $formattedIssue = ($user ?? null) && method_exists($user, 'dateFormat') && !empty($bill->bill_date) ? (string) $user->dateFormat($bill->bill_date) : '';
                                        $issueOutput    = $formattedIssue !== '' ? $formattedIssue : __('No issue date available');
                                        $formattedDue   = ($user ?? null) && method_exists($user, 'dateFormat') && !empty($bill->due_date) ? (string) $user->dateFormat($bill->due_date) : '';
                                        $dueOutput      = $formattedDue !== '' ? $formattedDue : __('No due date available');
                                    @endphp
                                    <div class="{{ VC::ME4 }}">
                                        <small>
                                            <strong>{{ __('Issue Date') }} :</strong><br>
                                            {{ $issueOutput }}<br><br>
                                        </small>
                                    </div>
                                    <div>
                                        <small>
                                            <strong>{{ __('Due Date') }} :</strong><br>
                                            {{ $dueOutput }}<br><br>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <small class="font-style">
                                        <strong>{{ __('Billed To') }} :</strong><br>
                                        {{ (!empty($vendor) && !empty($vendor->billing_name)) ? $vendor->billing_name : __('No billing name available') }}<br>
                                        {{ (!empty($vendor) && !empty($vendor->billing_address)) ? $vendor->billing_address : __('No billing address available') }}<br>
                                        {{ (!empty($vendor) && !empty($vendor->billing_city)) ? $vendor->billing_city : __('No billing city available') }}<br>
                                        {{ (!empty($vendor) && !empty($vendor->billing_state)) ? $vendor->billing_state : __('No billing state available') }}<br>
                                        {{ (!empty($vendor) && !empty($vendor->billing_zip)) ? $vendor->billing_zip : __('No billing zip available') }}<br>
                                        {{ (!empty($vendor) && !empty($vendor->billing_country)) ? $vendor->billing_country : __('No billing country available') }}<br>
                                        {{ (!empty($vendor) && !empty($vendor->billing_phone)) ? $vendor->billing_phone : __('No billing phone available') }}<br>
                                        @php
                                            $vatSwitchOn = isset($settings['vat_gst_number_switch']) && $settings['vat_gst_number_switch'] === 'on';
                                        @endphp
                                        @if($vatSwitchOn)
                                            <strong>{{ __('Tax Number') }} : </strong>{{ (!empty($vendor) && !empty($vendor->tax_number)) ? $vendor->tax_number : __('No tax number available') }}
                                        @endif
                                    </small>
                                </div>
                                @if(Utility::getValByName('shipping_display')=='on')
                                    <div class="col">
                                        <small>
                                            <strong>{{ __('Shipped To') }} :</strong><br>
                                            {{ (!empty($vendor) && !empty($vendor->shipping_name)) ? $vendor->shipping_name : __('No shipping name available') }}<br>
                                            {{ (!empty($vendor) && !empty($vendor->shipping_address)) ? $vendor->shipping_address : __('No shipping address available') }}<br>
                                            {{ (!empty($vendor) && !empty($vendor->shipping_city)) ? $vendor->shipping_city : __('No shipping city available') }}<br>
                                            {{ (!empty($vendor) && !empty($vendor->shipping_state)) ? $vendor->shipping_state : __('No shipping state available') }}<br>
                                            {{ (!empty($vendor) && !empty($vendor->shipping_zip)) ? $vendor->shipping_zip : __('No shipping zip available') }}<br>
                                            {{ (!empty($vendor) && !empty($vendor->shipping_country)) ? $vendor->shipping_country : __('No shipping country available') }}<br>
                                            {{ (!empty($vendor) && !empty($vendor->shipping_phone)) ? $vendor->shipping_phone : __('No shipping phone available') }}<br>
                                        </small>
                                    </div>
                                @endif
                                <div class="col">
                                    @php
                                        $qrRoute    = Route::has(VW::BIL . '.link.copy') ? route(VW::BIL . '.link.copy', Crypt::encrypt($bill->id)) : '#';
                                        $qrId       = 'bill-qr-copy-' . $bill->id;
                                        $qrGuardMsg = Utility::fetchLinkMessage($lang, VW::BIL, 'bill_link_copy_route_unavailable') ?? 'Bill link copy route is unavailable. Please contact technical support or your domain administrator.';
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
                                                const el = document.getElementById("{{ $qrId }}");
                                                if (!el || el.getAttribute("data-listener-active") === "true") return;
                                                el.setAttribute("data-listener-active", "true");
                                                el.addEventListener("click", event => {
                                                    try {
                                                    const url = el.getAttribute("data-url");
                                                    if (!url || url === "#") {
                                                        event.preventDefault();
                                                        const msg = el.getAttribute("data-guard-msg") ?? "# ERROR";
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container = document.getElementById("toast-container");
                                                        if (!container) {
                                                        container = document.createElement("div");
                                                                  container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
                                                        document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement("div");
                                                        toastEl.className = "toast";
                                                        toastEl.setAttribute("role", "alert");
                                                        toastEl.setAttribute("aria-live", "assertive");
                                                        toastEl.setAttribute("aria-atomic", "true");
                                                        const body = document.createElement("div");
                                                        body.className = "toast-body";
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                        alert(msg);
                                                        }
                                                        el.setAttribute("data-failed-route", "true");
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
                                    $statusIndex = isset($bill->status) ? (int) $bill->status : -1;
                                    $badgeClass  = $statusClasses[$statusIndex] ?? 'bg-secondary';
                                    $statusLabel = isset(Bill::$statuses[$statusIndex]) ? (string) Bill::$statuses[$statusIndex] : __('No status available');
                                @endphp
                                <div class="col">
                                    <small>
                                        <strong>{{ __('Status') }} :</strong><br>
                                        <span class="badge {{ $badgeClass }} p-2 px-3 rounded">
                                            {{ __($statusLabel) }}
                                        </span>
                                    </small>
                                </div>
                                @php
                                    $hasCustomFields = (is_array($customFields ?? null) && count($customFields ?? []) > 0) || (($customFields ?? null) instanceof Collection && ($customFields)->isNotEmpty());
                                    $billCustomData  = is_array($bill->customField ?? null) ? $bill->customField : [];
                                @endphp
                                @if($hasCustomFields)
                                    @foreach($customFields as $field)
                                        <div class="col text-md-end">
                                            <small>
                                                <strong>{{ $field->name }} :</strong><br>
                                                {{ array_key_exists($field->id, $billCustomData) && $billCustomData[$field->id] !== '' ? $billCustomData[$field->id] : __('No value available') }}
                                                <br><br>
                                            </small>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-bold mb-2">{{ __('Product Summary') }}</div>
                                    <small class="mb-2">{{ __('All items here cannot be deleted.') }}</small>
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
                                                $totalQuantity  = 0.0;
                                                $totalRate      = 0.0;
                                                $totalTaxAmount = 0.0;
                                                $totalDiscount  = 0.0;
                                                $taxesSummary   = [];
                                                $priceFmtAvail  = ($user ?? null) && method_exists($user, 'priceFormat');
                                            @endphp
                                            @php
                                                $itemsIterable = (is_array($items ?? null) && count($items ?? []) > 0) || (($items ?? null) instanceof Collection && ($items)->isNotEmpty());
                                            @endphp
                                            @if($itemsIterable)
                                                @foreach($items as $index => $item)
                                                    @php
                                                        $qty   = is_numeric($item->quantity ?? null) ? (float) $item->quantity : 0.0;
                                                        $rate  = is_numeric($item->price ?? null) ? (float) $item->price : 0.0;
                                                        $disc  = is_numeric($item->discount ?? null) ? (float) $item->discount : 0.0;
                                                        $totalQuantity += $qty;
                                                        $totalRate     += $rate;
                                                        $totalDiscount += $disc;
                                                        $taxList = [];
                                                        if (!empty($item->tax)) {
                                                            $tmp = Utility::tax($item->tax);
                                                            if (is_iterable($tmp)) {
                                                                foreach ($tmp as $t) {
                                                                    $taxList[] = $t;
                                                                }
                                                            }
                                                        }
                                                        $lineTaxTotal = 0.0;
                                                        foreach ($taxList as $taxObj) {
                                                            $taxName       = isset($taxObj->name) && $taxObj->name !== '' ? (string) $taxObj->name : __('Tax');
                                                            $taxRate       = is_numeric($taxObj->rate ?? null) ? (float) $taxObj->rate : 0.0;
                                                            $taxAmount     = Utility::taxRate($taxRate, $rate, $qty, $disc);
                                                            $lineTaxTotal += $taxAmount;
                                                            $taxesSummary[$taxName] = ($taxesSummary[$taxName] ?? 0.0) + $taxAmount;
                                                        }
                                                        $totalTaxAmount += $lineTaxTotal;
                                                        $linePrice = max(0, ($rate * $qty) - $disc) + $lineTaxTotal;
                                                        $product   = method_exists($item, 'product') ? $item->product() : null;
                                                        $unitId    = !empty($product) ? ($product->unit_id ?? null) : null;
                                                        $unitModel = $unitId ? \App\Models\ProductServiceUnit::find($unitId) : null;
                                                        $chart     = isset($item->chart_account_id) ? \App\Models\ChartOfAccount::find($item->chart_account_id) : null;
                                                        $accountAmount = is_numeric($item->amount ?? null) ? (float) $item->amount : 0.0;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ !empty($product) ? $product->name : __('No product available') }}</td>
                                                        <td>{{ $qty > 0 ? ($unitModel ? ($qty . ' (' . $unitModel->name . ')') : $qty) : __('No quantity available') }}</td>
                                                        <td>{{ $priceFmtAvail ? $user->priceFormat($rate) : number_format($rate, 2) }}</td>
                                                        <td>{{ $priceFmtAvail ? $user->priceFormat($disc) : number_format($disc, 2) }}</td>
                                                        <td>
                                                            @if(!empty($taxList))
                                                                <table>
                                                                    @foreach($taxList as $tx)
                                                                        @php
                                                                            $txName  = isset($tx->name) && $tx->name !== '' ? (string) $tx->name : __('Tax');
                                                                            $txRate  = is_numeric($tx->rate ?? null) ? (float) $tx->rate : 0.0;
                                                                            $txPrice = Utility::taxRate($txRate, $rate, $qty, $disc);
                                                                        @endphp
                                                                        <tr>
                                                                            <td>{{ $txName . ' (' . $txRate . '%)' }}</td>
                                                                            <td>{{ $priceFmtAvail ? $user->priceFormat($txPrice) : number_format($txPrice, 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </table>
                                                            @else
                                                                {{ __('No tax available') }}
                                                            @endif
                                                        </td>
                                                        <td>{{ !empty($chart) ? $chart->name : __('No account available') }}</td>
                                                        <td>{{ $priceFmtAvail ? $user->priceFormat($accountAmount) : number_format($accountAmount, 2) }}</td>
                                                        <td>{{ !empty($item->description) ? $item->description : __('No description available') }}</td>
                                                        <td class="text-end">{{ $priceFmtAvail ? $user->priceFormat($linePrice) : number_format($linePrice, 2) }}</td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="11" class="text-center">{{ __('No items available') }}</td>
                                                </tr>
                                            @endif
                                            @php
                                                $accountTotal = ($bill ?? null) && method_exists($bill, 'getAccountTotal') ? (float) $bill->getAccountTotal() : 0.0;
                                                $subTotal     = ($bill ?? null) && method_exists($bill, 'getSubTotal') ? (float) $bill->getSubTotal() : 0.0;
                                                $totalDisc    = ($bill ?? null) && method_exists($bill, 'getTotalDiscount') ? (float) $bill->getTotalDiscount() : 0.0;
                                                $grandTotal   = ($bill ?? null) && method_exists($bill, 'getTotal') ? (float) $bill->getTotal() : 0.0;
                                                $dueAmount    = ($bill ?? null) && method_exists($bill, 'getDue') ? (float) $bill->getDue() : 0.0;
                                                $debitNotes   = ($bill ?? null) && method_exists($bill, 'billTotalDebitNote') ? (float) $bill->billTotalDebitNote() : 0.0;
                                                $paidAmount   = max(0, ($grandTotal - $dueAmount) - $debitNotes);
                                            @endphp
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td><b>{{ __('Total') }}</b></td>
                                                    <td><b>{{ $totalQuantity }}</b></td>
                                                    <td><b>{{ $priceFmtAvail ? $user->priceFormat($totalRate) : number_format($totalRate, 2) }}</b></td>
                                                    <td><b>{{ $priceFmtAvail ? $user->priceFormat($totalDiscount) : number_format($totalDiscount, 2) }}</b></td>
                                                    <td><b>{{ $priceFmtAvail ? $user->priceFormat($totalTaxAmount) : number_format($totalTaxAmount, 2) }}</b></td>
                                                    <td></td>
                                                    <td><b>{{ $priceFmtAvail ? $user->priceFormat($accountTotal) : number_format($accountTotal, 2) }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b>{{ __('Sub Total') }}</b></td>
                                                    <td class="text-end">{{ $priceFmtAvail ? $user->priceFormat($subTotal) : number_format($subTotal, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b>{{ __('Discount') }}</b></td>
                                                    <td class="text-end">{{ $priceFmtAvail ? $user->priceFormat($totalDisc) : number_format($totalDisc, 2) }}</td>
                                                </tr>
                                                @php
                                                    $hasTaxSummary = !empty($taxesSummary);
                                                @endphp
                                                @if($hasTaxSummary)
                                                    @foreach($taxesSummary as $taxName => $taxValue)
                                                        <tr>
                                                            <td colspan="8"></td>
                                                            <td class="text-end"><b>{{ $taxName }}</b></td>
                                                            <td class="text-end">{{ $priceFmtAvail ? $user->priceFormat($taxValue) : number_format($taxValue, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="blue-text text-end"><b>{{ __('Total') }}</b></td>
                                                    <td class="blue-text text-end">{{ $priceFmtAvail ? $user->priceFormat($grandTotal) : number_format($grandTotal, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b>{{ __('Paid') }}</b></td>
                                                    <td class="text-end">{{ $priceFmtAvail ? $user->priceFormat($paidAmount) : number_format($paidAmount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b>{{ __('Debit Note') }}</b></td>
                                                    <td class="text-end">{{ $priceFmtAvail ? $user->priceFormat($debitNotes) : number_format($debitNotes, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b>{{ __('Due') }}</b></td>
                                                    <td class="text-end">{{ $priceFmtAvail ? $user->priceFormat($dueAmount) : number_format($dueAmount, 2) }}</td>
                                                </tr>
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
                                        $paymentHeaders = [
                                            __('Payment Receipt'),
                                            __('Date'),
                                            __('Amount'),
                                            __('Account'),
                                            __('Reference'),
                                            __('Description'),
                                        ];
                                    @endphp
                                    @foreach ($paymentHeaders as $header)
                                        <th class="text-dark">{{ $header }}</th>
                                    @endforeach
                                    @can('delete payment bill')
                                        <th class="text-dark">{{ __('Action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $paymentsSource           = $bill->payments ?? null;
                                    $paymentsIsArray          = is_array($paymentsSource) && count($paymentsSource) > 0;
                                    $paymentsIsCollection     = ($paymentsSource instanceof Collection) && $paymentsSource->isNotEmpty();
                                    $paymentsIterable         = $paymentsIsArray || $paymentsIsCollection;
                                    $hasUserDateFormatter     = ($user ?? null) && method_exists($user, 'dateFormat');
                                    $hasUserPriceFormatter    = ($user ?? null) && method_exists($user, 'priceFormat');
                                @endphp
                                @if($paymentsIterable)
                                    @foreach(($paymentsIsCollection ? $paymentsSource : collect($paymentsSource)) as $payment)
                                        @php
                                            $receiptPresent   = !empty($payment->add_receipt);
                                            $dateValue        = !empty($payment->date) ? ($hasUserDateFormatter ? (string) $user->dateFormat($payment->date) : (string) $payment->date) : '';
                                            $dateOutput       = $dateValue !== '' ? $dateValue : __('No date available');
                                            $amountIsNumeric  = is_numeric($payment->amount ?? null);
                                            $amountValue      = $amountIsNumeric ? (float) $payment->amount : null;
                                            $amountOutput     = $amountIsNumeric ? ($hasUserPriceFormatter ? $user->priceFormat($amountValue) : number_format($amountValue, 2)) : __('No amount available');
                                            $bankName         = optional($payment->bankAccount)->bank_name;
                                            $holderName       = optional($payment->bankAccount)->holder_name;
                                            $accountOutput    = ($bankName || $holderName) ? trim(($bankName ?? '').' '.($holderName ?? '')) : __('No account available');
                                            $referenceOutput  = isset($payment->reference) && $payment->reference !== '' ? $payment->reference : __('No reference available');
                                            $descriptionOutput= isset($payment->description) && $payment->description !== '' ? $payment->description : __('No description available');
                                        @endphp
                                        <tr>
                                            <td>
                                                @if($receiptPresent)
                                                    @php
                                                        $receiptUrl      = asset(\Storage::url('uploads/payment').'/'.$payment->add_receipt);
                                                        $receiptLinkId   = 'payment-receipt-'.$payment->id;
                                                        $receiptGuardMsg = Utility::fetchLinkMessage($lang, VW::BIL, 'payment_receipt_unavailable') ?? 'Payment receipt is unavailable. Please contact technical support or your domain administrator.';
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
                                                                const link = document.getElementById("{{ $receiptLinkId }}");
                                                                if (!link || link.getAttribute("data-listener-active") === "true") return;
                                                                link.setAttribute("data-listener-active", "true");
                                                                link.addEventListener("click", event => {
                                                                    try {
                                                                    const url = link.getAttribute("data-url");
                                                                    if (!url || url === "#") {
                                                                        event.preventDefault();
                                                                        const msg = link.getAttribute("data-guard-msg") ?? "# ERROR";
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container = document.getElementById("toast-container");
                                                                        if (!container) {
                                                                        container = document.createElement("div");
                                                                                  container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
                                                                        document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement("div");
                                                                        toastEl.className = "toast";
                                                                        toastEl.setAttribute("role", "alert");
                                                                        toastEl.setAttribute("aria-live", "assertive");
                                                                        toastEl.setAttribute("aria-atomic", "true");
                                                                        const body = document.createElement("div");
                                                                        body.className = "toast-body";
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                        alert(msg);
                                                                        }
                                                                        link.setAttribute("data-failed-route", "true");
                                                                    }
                                                                    } catch (e) {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                @else
                                                    {{ __('No receipt available') }}
                                                @endif
                                            </td>
                                            <td>{{ $dateOutput }}</td>
                                            <td>{{ $amountOutput }}</td>
                                            <td>{{ $accountOutput }}</td>
                                            <td>{{ $referenceOutput }}</td>
                                            <td>{{ $descriptionOutput }}</td>
                                            @can('delete bill product')
                                                <td>
                                                    @php
                                                        $deletePaymentRoute    = Route::has(VW::BIL . '.payment.destroy')
                                                            ? route(VW::BIL . '.payment.destroy', [$bill->id, $payment->id])
                                                            : '#';
                                                        $deletePaymentFormId   = 'delete-form-' . $payment->id;
                                                        $deletePaymentBtnId    = 'delete-payment-btn-' . $payment->id;
                                                        $deletePaymentGuardMsg = Utility::fetchLinkMessage($lang, VW::BIL, 'payment_destroy_route_unavailable') ?? 'Payment delete route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'route'            => $deletePaymentRoute,
                                                            'method'           => 'post',
                                                            'id'               => $deletePaymentFormId,
                                                            'data-url'         => $deletePaymentRoute,
                                                            'data-guard-msg'   => $deletePaymentGuardMsg,
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
                                                                const btn = document.getElementById("{{ $deletePaymentBtnId }}");
                                                                if (!btn || btn.getAttribute("data-listener-active") === "true") return;
                                                                btn.setAttribute("data-listener-active", "true");
                                                                btn.addEventListener("click", event => {
                                                                    try {
                                                                    const href = btn.getAttribute("href");
                                                                    const url = btn.getAttribute("data-url");
                                                                    if ((href && href !== "#") || (url && url !== "#")) return;
                                                                    event.preventDefault();
                                                                    const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    let container = document.getElementById("toast-container");
                                                                    if (!container) {
                                                                        container = document.createElement("div");
                                                                                  container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement("div");
                                                                        toastEl.className = "toast";
                                                                        toastEl.setAttribute("role", "alert");
                                                                        toastEl.setAttribute("aria-live", "assertive");
                                                                        toastEl.setAttribute("aria-atomic", "true");
                                                                        const body = document.createElement("div");
                                                                        body.className = "toast-body";
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    btn.setAttribute("data-failed-route", "true");
                                                                    } catch (e) {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        @php
                                            $paymentCols = 6 + (Gate::check('delete payment bill') ? 1 : 0);
                                        @endphp
                                        <td colspan="{{ $paymentCols }}" class="text-center text-dark">{{ __('No payments found') }}</td>
                                    </tr>
                                @endif
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
                                @php
                                    $debitNotesSource          = $bill->debitNote ?? null;
                                    $debitNotesIsArray         = is_array($debitNotesSource) && count($debitNotesSource) > 0;
                                    $debitNotesIsCollection    = ($debitNotesSource instanceof Collection) && $debitNotesSource->isNotEmpty();
                                    $debitNotesIterable        = $debitNotesIsArray || $debitNotesIsCollection;
                                @endphp
                                @if($debitNotesIterable)
                                    @foreach(($debitNotesIsCollection ? $debitNotesSource : collect($debitNotesSource)) as $debitNote)
                                        @php
                                            $dnDateValue   = !empty($debitNote->date) ? ($hasUserDateFormatter ? (string) $user->dateFormat($debitNote->date) : (string) $debitNote->date) : '';
                                            $dnDateOutput  = $dnDateValue !== '' ? $dnDateValue : __('No date available');
                                            $dnAmtNumeric  = is_numeric($debitNote->amount ?? null);
                                            $dnAmtValue    = $dnAmtNumeric ? (float) $debitNote->amount : null;
                                            $dnAmtOutput   = $dnAmtNumeric ? ($hasUserPriceFormatter ? $user->priceFormat($dnAmtValue) : number_format($dnAmtValue, 2)) : __('No amount available');
                                            $dnDescOutput  = isset($debitNote->description) && $debitNote->description !== '' ? $debitNote->description : __('No description available');
                                        @endphp
                                        <tr>
                                            <td>{{ $dnDateOutput }}</td>
                                            <td>{{ $dnAmtOutput }}</td>
                                            <td>{{ $dnDescOutput }}</td>
                                            @if(Gate::check('edit debit note') || Gate::check('delete debit note'))
                                                <td>
                                                    @can('edit debit note')
                                                        @php
                                                            $billDebitNoteEditRouteBase = VW::BIL.'.edit.debit.note';
                                                            $billDebitNoteEditRouteKebab = Str::kebab($billDebitNoteEditRouteBase);
                                                            $billIdForEditDebitNote = (string) data_get($debitNote,'bill','');
                                                            $debitNoteIdForEdit = (string) data_get($debitNote,'id','');
                                                            $billDebitNoteEditRouteResolved = Route::has($billDebitNoteEditRouteBase) ? $billDebitNoteEditRouteBase : (Route::has($billDebitNoteEditRouteKebab) ? $billDebitNoteEditRouteKebab : null);
                                                            $billDebitNoteEditUrl = ($billDebitNoteEditRouteResolved && $billIdForEditDebitNote !== '' && $debitNoteIdForEdit !== '') ? route($billDebitNoteEditRouteResolved, [$billIdForEditDebitNote, $debitNoteIdForEdit]) : '#';
                                                            $activeLangForDebitNoteEdit = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $billDebitNoteEditGuardMsg = Utility::fetchLinkMessage($activeLangForDebitNoteEdit, VW::BIL, 'edit_bill_debit_note_route_unavailable') ?? 'Edit bill debit note route is unavailable. Please contact technical support or your domain administrator.';
                                                            $billDebitNoteEditAnchorId = 'bill-debit-note-edit-'.$billIdForEditDebitNote.'-'.$debitNoteIdForEdit;
                                                        @endphp
                                                        <a
                                                            id="{{ $billDebitNoteEditAnchorId }}"
                                                            href="{{ $billDebitNoteEditUrl }}"
                                                            data-url="{{ $billDebitNoteEditUrl }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Debit Note') }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-guard-msg="{{ $billDebitNoteEditGuardMsg }}"
                                                            data-sv-localized="true"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const el = document.getElementById('{{ $billDebitNoteEditAnchorId }}');
                                                                        if (!el) { return; }
                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                        el.setAttribute('data-listener-active','true');
                                                                        el.addEventListener('click',(e) => {
                                                                            try {
                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                e.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit bill debit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                                let container = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (hasBootstrap) {
                                                                                    const toast = document.createElement('div');
                                                                                    toast.className = 'toast';
                                                                                    toast.setAttribute('role','alert');
                                                                                    toast.setAttribute('aria-live','assertive');
                                                                                    toast.setAttribute('aria-atomic','true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toast.appendChild(body);
                                                                                    container.appendChild(toast);
                                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route','true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @can('delete debit note')
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            @php
                                                                $billDebitNoteDeleteRouteBase   = VW::BIL.'.delete.debit.note';
                                                                $billDebitNoteDeleteRouteKebab  = Str::kebab($billDebitNoteDeleteRouteBase);
                                                                $billIdForDeleteDebitNote       = (string) data_get($debitNote,'bill','');
                                                                $debitNoteIdForDelete           = (string) data_get($debitNote,'id','');
                                                                $billDebitNoteDeleteRouteName   = Route::has($billDebitNoteDeleteRouteBase)
                                                                    ? $billDebitNoteDeleteRouteBase
                                                                    : (Route::has($billDebitNoteDeleteRouteKebab) ? $billDebitNoteDeleteRouteKebab : null);
                                                                $billDebitNoteDeleteUrl         = ($billDebitNoteDeleteRouteName && $billIdForDeleteDebitNote !== '' && $debitNoteIdForDelete !== '')
                                                                    ? route($billDebitNoteDeleteRouteName, [$billIdForDeleteDebitNote, $debitNoteIdForDelete])
                                                                    : '#';
                                                                $activeLangForDebitNoteDelete   = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $billDebitNoteDeleteGuardMsg    = Utility::fetchLinkMessage($activeLangForDebitNoteDelete, VW::BIL, 'destroy_bill_debit_note_route_unavailable')
                                                                    ?? 'Destroy bill debit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                $billDebitNoteDeleteFormId      = 'bill-debit-note-delete-form-'.($debitNoteIdForDelete === '' ? 'x' : $debitNoteIdForDelete);
                                                                $billDebitNoteDeleteLinkId      = 'bill-debit-note-delete-link-'.($debitNoteIdForDelete === '' ? 'x' : $debitNoteIdForDelete);
                                                            @endphp
                                                            {!! Form::open([
                                                                'method'         => 'DELETE',
                                                                'url'            => $billDebitNoteDeleteUrl,
                                                                'id'             => $billDebitNoteDeleteFormId,
                                                                'data-url'       => $billDebitNoteDeleteUrl,
                                                                'data-guard-msg' => $billDebitNoteDeleteGuardMsg,
                                                                'data-sv-localized' => 'true',
                                                            ]) !!}
                                                                <a
                                                                    href="#"
                                                                    id="{{ $billDebitNoteDeleteLinkId }}"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($activeLangForDebitNoteDelete, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($activeLangForDebitNoteDelete, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $billDebitNoteDeleteFormId }}').submit();"
                                                                    data-form-id="{{ $billDebitNoteDeleteFormId }}"
                                                                    data-guard-msg="{{ $billDebitNoteDeleteGuardMsg }}"
                                                                    data-sv-localized="true"
                                                                >
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const linkEl = document.getElementById('{{ $billDebitNoteDeleteLinkId }}');
                                                                            const formEl = document.getElementById('{{ $billDebitNoteDeleteFormId }}');
                                                                            if (!linkEl || !formEl) { return; }

                                                                            if (linkEl.getAttribute('data-listener-active') !== 'true') {
                                                                                linkEl.setAttribute('data-listener-active','true');
                                                                                linkEl.addEventListener('click', (e) => {
                                                                                    try {
                                                                                        const fid   = linkEl.getAttribute('data-form-id') ?? '';
                                                                                        const fm    = fid ? document.getElementById(fid) : null;
                                                                                        const action = fm ? (fm.getAttribute('action') ?? '#') : '#';
                                                                                        const url    = fm ? (fm.getAttribute('data-url') ?? action ?? '#') : '#';

                                                                                        if (url !== '#' && action !== '#') { return; }
                                                                                        e.preventDefault();

                                                                                        const msg = linkEl.getAttribute('data-guard-msg') ?? 'Destroy bill debit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);

                                                                                        let container = document.getElementById('toast-container');
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = 'toast-container';
                                                                                            document.body.appendChild(container);
                                                                                        }

                                                                                        if (hasBootstrap) {
                                                                                            const toast = document.createElement('div');
                                                                                            toast.className = 'toast';
                                                                                            toast.setAttribute('role','alert');
                                                                                            toast.setAttribute('aria-live','assertive');
                                                                                            toast.setAttribute('aria-atomic','true');

                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;

                                                                                            toast.appendChild(body);
                                                                                            container.appendChild(toast);
                                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }

                                                                                        linkEl.setAttribute('data-failed-route','true');
                                                                                        if (fm) { fm.setAttribute('data-failed-route','true'); }
                                                                                    } catch (err) {}
                                                                                });
                                                                            }

                                                                            if (formEl.getAttribute('data-submit-guarded') !== 'true') {
                                                                                formEl.setAttribute('data-submit-guarded','true');
                                                                                formEl.addEventListener('submit', (e) => {
                                                                                    try {
                                                                                        const action = formEl.getAttribute('action') ?? '#';
                                                                                        const url    = formEl.getAttribute('data-url') ?? action ?? '#';
                                                                                        if (url !== '#' && action !== '#') { return; }
                                                                                        e.preventDefault();

                                                                                        const msg = formEl.getAttribute('data-guard-msg') ?? 'Destroy bill debit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);

                                                                                        let container = document.getElementById('toast-container');
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = 'toast-container';
                                                                                            document.body.appendChild(container);
                                                                                        }

                                                                                        if (hasBootstrap) {
                                                                                            const toast = document.createElement('div');
                                                                                            toast.className = 'toast';
                                                                                            toast.setAttribute('role','alert');
                                                                                            toast.setAttribute('aria-live','assertive');
                                                                                            toast.setAttribute('aria-atomic','true');

                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;

                                                                                            toast.appendChild(body);
                                                                                            container.appendChild(toast);
                                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }

                                                                                        formEl.setAttribute('data-failed-route','true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            }
                                                                        } catch (err) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endcan
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    @php
                                        $debitCols = 3 + ((Gate::check('edit debit note') || Gate::check('delete debit note')) ? 1 : 0);
                                    @endphp
                                    <tr>
                                        <td colspan="{{ $debitCols }}" class="text-center text-dark">{{ __('No debit notes found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="{{ VC::C12 }}">
            <div class="alert alert-danger">
                {{ __('Bill record not found.') }}
            </div>
        </div>
    @endif
@endsection
