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
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $siteRtl = !empty($settings[SettingsConstants::RTL] ) ? $settings[SettingsConstants::RTL]  : 'off';
    $downloadRoute         = Route::has(ViewsConstants::CTC.'.download.pdf')
        ? route(ViewsConstants::CTC.'.download.pdf', Crypt::encrypt($contract->id))
        : '#';
    $downloadLinkId        = 'contract-download-btn-' . $contract->id;
    $downloadGuardMsg      = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CTC,
        'contract_download_route_unavailable'
    ) ?? 'Contract download route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@push(StacksConstants::CTC_SCR_PG)
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langSessionKey = 'erp-np-lang';
        
        const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (
            el.getAttribute('data-sv-localized') === 'true' ||
            el.getAttribute(dataClientLocalized) === 'true'
            ) {
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem(langSessionKey) ??
                document.documentElement.lang ??
                'en'
            ).toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ??
                el.getAttribute(dataGuardMsg) ??
                window.translations?.['en']?.[msgKey] ??
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
            let container = document.querySelector('#bootstrap-toast-container');
            if (!container) {
                const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                    .some(l => /bootstrap/i.test(l.href)) &&
                window.bootstrap?.Toast;
                if (hasBs) {
                container = document.createElement('div');
                container.id = 'bootstrap-toast-container';
                container.setAttribute('aria-live', 'polite');
                container.setAttribute('aria-atomic', 'true');
                document.body.appendChild(container);
                }
            }
            if (container && window.bootstrap.Toast) {
                let toast = container.querySelector('.toast');
                if (!toast) {
                toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                toast.appendChild(body);
                container.appendChild(toast);
                if (toast.getAttribute('data-click-listener') !== 'true') {
                    toast.addEventListener('click', () => (body.textContent = message));
                    toast.setAttribute('data-click-listener', 'true');
                }
                }
                toast.querySelector('.toast-body').textContent = message;
                new bootstrap.Toast(toast).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };
        
        let errorMessage = '';
        const onErrorPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m =>
            Array.from(m.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
                }
            })
            );
        }).observe(document.body, { childList: true, subtree: true });
        
        const closeScript = () => {
            setTimeout(() => {
            try {
                window.open(window.location, '_self').close();
            } catch {
                errorMessage = getLocalizedMessage('window_close_failed', document.body);
            }
            }, 1000);
        };
        
        window.addEventListener('load', () => {
            try {
            if (typeof html2pdf !== 'function') {
                console.log('html2pdf library not loaded');
                throw new Error('pdf_generation_failed');
            }
            const element = document.getElementById('boxes');
            if (!element) throw new Error('pdf_generation_failed');
            const opt = {
                filename: '{{ App\Models\Utility::contractNumberFormat($contract->id) }}',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: 'in', format: 'A4' }
            };
            html2pdf().set(opt).from(element).save()
                .then(closeScript)
                .catch(() => {
                errorMessage = getLocalizedMessage('pdf_generation_failed', document.body);
                });
            } catch (e) {
            if (e.message === 'pdf_generation_failed') {
                errorMessage = getLocalizedMessage('pdf_generation_failed', document.body);
            }
            }
        });
        })();
    </script>
@endpush
@section(YieldingConstants::CTC_PG_TTL)
    {{__('Contract')}}
@endsection
@section(YieldingConstants::CTC_CTT)
    <div class="{{ VC::MT4 }}">
        <div class="{{ VC::RW }} justify-content-center {{ VC::MB3 }}">
            <div class="{{ VC::CS9 }} text-end me-2">
                <div class="all-button-box">
                    @if((($user?->type =='company' && $contract->company_signature=='') || ($user?->type=='client' && $contract->client_signature=='')) && $contract->status=='Start')
                        @php
                            $signatureRoute      = Route::has(ViewsConstants::CTC.'signature')
                                ? route(ViewsConstants::CTC.'signature', $contract->id)
                                : '#';
                            $signatureBtnId      = 'contract-signature-btn-' . $contract->id;
                            $signatureGuardMsg   = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::CTC,
                                'contract_signature_route_unavailable'
                            ) ?? 'Signature route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a href="#" id="{{ $signatureBtnId }}" class="{{ VC::BT_SM_PM }} btn-icon" data-bs-toggle="modal" data-bs-target="#exampleModal" data-size="md" data-url="{{ $signatureRoute }}" data-guard-msg="{{ $signatureGuardMsg }}" data-bs-whatever="{{ __('Signature') }}">
                            <span class="text-white">
                                <i class="{{ VC::TI_PC_WT }}" data-bs-toggle="tooltip" title="{{ __('Signature') }}"></i>
                            </span>
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const btn = document.getElementById('{{ $signatureBtnId }}');
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
                    @endif
                    <a
                        id="{{ $downloadLinkId }}"
                        href="{{ $downloadRoute }}"
                        data-url="{{ $downloadRoute }}"
                        data-guard-msg="{{ $downloadGuardMsg }}"
                        class="{{ VC::BT_SM_PM }} btn-icon"
                        data-bs-toggle="tooltip"
                        title="{{ __('Download') }}"
                        target="_blank"
                    >
                        <i class="{{ VC::TI_DWN }}"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }} justify-content-center">
            <div class="{{ VC::CS9 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="{{ VC::RW }} invoice-title mt-2">
                            <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                <img src="{{ $img }}" style="max-width: 150px;" />
                            </div>
                            <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                <h3 class="invoice-number">{{ $user?->contractNumberFormat($contract->id) }}</h3>
                            </div>
                            <div class="col-12"><hr></div>
                        </div>
                        <div class="{{ VC::R_ALC_M4 }}">
                            <div class="col-sm-6 mb-3 mb-sm-0 mt-3">
                                <h6 class="d-inline-block m-0">{{ __('Contract Type :') }}</h6>
                                <span class="text-md">{{ $contract->types->name }}</span>
                                <br>
                                <h6 class="d-inline-block m-0">{{ __('Contract Value :') }}</h6>
                                <span class="text-md">{{ $user?->priceFormat($contract->value) }}</span>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <h6 class="d-inline-block m-0">{{ __('Start Date :') }}</h6>
                                <span class="text-md">{{ $user?->dateFormat($contract->start_date) }}</span>
                                <br>
                                <h6 class="d-inline-block m-0">{{ __('End Date :') }}</h6>
                                <span class="text-md">{{ $user?->dateFormat($contract->end_date) }}</span>
                            </div>
                        </div>

                        <div class="text-md">{!! $contract->description !!}</div>
                        <br>
                        <div class="text-md">{!! $contract->contract_description !!}</div>

                        <div class="{{ VC::RW }}">
                            <div class="col-6">
                                <img width="200" src="{{ $contract->company_signature }}" alt="{{ __('Company Signature') }}">
                                <h5 class="mt-2">{{ __('Company Signature') }}</h5>
                            </div>
                            <div class="col-6 text-end">
                                <img width="200" src="{{ $contract->client_signature }}" alt="{{ __('Client Signature') }}">
                                <h5 class="mt-2">{{ __('Client Signature') }}</h5>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
