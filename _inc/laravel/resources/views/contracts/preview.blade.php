@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        SettingsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Crypt};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $siteRtl = !empty($settings[SettingsConstants::RTL] ) ? $settings[SettingsConstants::RTL]  : 'off';
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@if(!empty($contract) && isset($contract->id))
    @push(StacksConstants::CTC_SCR_PG)
        <script src="{{ asset('assets/js/routes/contracts/lang/preview.js') }}"></script>
        <script defer>
            (function () {
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-contract-export-error";
            const dataBindGuard = "data-contract-export-bound";
            const qs = (s, r = document) => r.querySelector(s);
            const hasBS = () =>
                !!(
                qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                qs('link[href*="bootstrap"]')
                ) && !!(window.bootstrap && window.bootstrap.Toast);
            const ensureToastContainer = () => {
                let c = qs("#np-toast-container");
                if (c) return c;
                c = document.createElement("div");
                c.id = "np-toast-container";
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                c.style.position = "fixed";
                c.style.top = "1rem";
                c.style.right = "1rem";
                document.body.appendChild(c);
                return c;
            };
            const showErrorNow = message => {
                if (hasBS()) {
                const container = ensureToastContainer();
                let t = qs("#np-toast", container);
                if (!t) {
                    t = document.createElement("div");
                    t.id = "np-toast";
                    t.className = "toast";
                    t.setAttribute("role", "alert");
                    t.setAttribute("aria-live", "assertive");
                    t.setAttribute("aria-atomic", "true");
                    t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                    container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) body.textContent = message ?? errFb;
                try {
                    new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                } catch (_) {
                    alert(message ?? errFb);
                }
                } else {
                alert(message ?? errFb);
                }
            };
            const schedulePointerupError = msg => {
                const host = document.body;
                if (!host || host.getAttribute(dataErrGuard) === "true") return;
                host.setAttribute(dataErrGuard, "true");
                const once = () => {
                try {
                    showErrorNow(msg);
                } finally {
                    host.removeAttribute(dataErrGuard);
                }
                };
                document.addEventListener("pointerup", once, { once: true });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                    document.removeEventListener("pointerup", once);
                    o.disconnect();
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            const getMsg = (el, key) => {
                let msg = errFb;
                if (
                el?.getAttribute?.(dataSvLocalized) === "true" ||
                el?.getAttribute?.(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = key;
                msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el?.getAttribute?.(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
                if (msg !== errFb && el) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
                }
                return msg;
            };
            const safeClose = () => {
                try {
                window.open(window.location, "_self").close();
                } catch (_) {
                try {
                    window.close();
                } catch (__) {}
                }
            };
            const closeScript = () => {
                setTimeout(function () {
                safeClose();
                }, 1000);
            };
            const run = () => {
                try {
                const host = document.body;
                if (host.getAttribute(dataBindGuard) === "true") return;
                host.setAttribute(dataBindGuard, "true");
                const element = qs("#boxes");
                if (!element) {
                    schedulePointerupError(
                    getMsg(document.body, "contract_export_unavailable")
                    );
                    return;
                }
                const opt = {
                    filename: "{{Utility::contractNumberFormat($contract->id)}}",
                    image: { type: "jpeg", quality: 1 },
                    html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                    jsPDF: { unit: "in", format: "A4" },
                };
                if (typeof window.html2pdf !== "function") {
                    try {
                    console.error("html2pdf unavailable");
                    } catch (_) {}
                    schedulePointerupError(
                    getMsg(document.body, "contract_export_unavailable")
                    );
                    return;
                }
                window
                    .html2pdf()
                    .set(opt)
                    .from(element)
                    .save()
                    .then(closeScript)
                    .catch(function () {
                    schedulePointerupError(
                        getMsg(document.body, "contract_export_unavailable")
                    );
                    });
                const mo = new MutationObserver(function () {
                    if (!document.body.contains(element)) {
                    host.removeAttribute(dataBindGuard);
                    }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
                } catch (_) {
                schedulePointerupError(
                    getMsg(document.body, "contract_export_unavailable")
                );
                }
            };
            if (document.readyState === "complete") {
                run();
            } else {
                window.addEventListener("load", run, { once: true });
            }
            })();
        </script>
    @endpush
    @section(YieldingConstants::CTC_PG_TTL)
        {{__('Contract')}}
    @endsection
    @section(YieldingConstants::CTC_CTT)
        <div class="{{ VC::MT3 }}">
            <div class="{{ VC::RW }} justify-content-center {{ VC::MB3 }}">
                <div class="{{ VC::CS9 }} text-end me-2">
                    <div class="all-button-box">
                        @php
                            $uType = (isset($user) && isset($user->{UsersConstants::COL_TP})) ? $user->{UsersConstants::COL_TP} : null;
                            $status = isset($contract->status) ? (string)$contract->status : '';
                            $companySig = isset($contract->company_signature) ? (string)$contract->company_signature : '';
                            $clientSig = isset($contract->client_signature) ? (string)$contract->client_signature : '';
                            $cid = isset($contract->id) ? $contract->id : null;
                            $canSign = $status === 'Start' && (($uType === PermissionsConstants::CPN && $companySig === '') || ($uType === PermissionsConstants::CL && $clientSig === ''));
                            $isPriceFmt = isset($user) && method_exists($user,'priceFormat');
                            $isDateFmt = isset($user) && method_exists($user,'dateFormat');
                            $isContractFmt = isset($user) && method_exists($user,'contractNumberFormat');
                            $logo = !empty($img ?? null) ? $img : null;
                        @endphp
                        @if($canSign)
                            @php
                                $contractsSignatureBaseRouteName   = VW::CTC.'.signature';
                                $contractsSignatureKebabRouteName  = Str::kebab($contractsSignatureBaseRouteName);
                                $contractsSignatureResolvedName    = Route::has($contractsSignatureBaseRouteName)
                                    ? $contractsSignatureBaseRouteName
                                    : (Route::has($contractsSignatureKebabRouteName) ? $contractsSignatureKebabRouteName : null);

                                $contractsIdValue                  = (string) ($cid ?? '');
                                $contractsSignatureUrl             = ($contractsSignatureResolvedName && $contractsIdValue !== '')
                                    ? route($contractsSignatureResolvedName, $contractsIdValue)
                                    : '#';

                                $contractsLangValue                = isset($lang) ? $lang : Utility::fetchUserLang();
                                $contractsSignatureGuardMessage    = Utility::fetchLinkMessage($contractsLangValue, VW::CTC, 'signature_contracts_route_unavailable')
                                    ?? 'Contracts signature route is unavailable. Please contact technical support or your domain administrator.';
                                $contractsSignatureOpenModalLinkId = 'contracts-signature-open-modal-link-'.($contractsIdValue === '' ? 'x' : $contractsIdValue);
                            @endphp
                            <a href="{{ $contractsSignatureUrl }}"
                            id="{{ $contractsSignatureOpenModalLinkId }}"
                            class="{{ VC::BT_SM_PM }} btn-icon"
                            data-bs-toggle="modal"
                            data-bs-target="#exampleModal"
                            data-size="md"
                            data-url="{{ $contractsSignatureUrl }}"
                            data-bs-whatever="{{ __('signature') }}"
                            data-guard-msg="{{ $contractsSignatureGuardMessage }}"
                            data-sv-localized="true">
                                <span class="{{ VC::TXT_WT }}">
                                    <i class="{{ VC::TI_PC_WT }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('signature') }}"></i>
                                </span>
                            </a>
                            @push(StacksConstants::CTC_SCR_PG)
                                <script defer>
                                    (() => {
                                        try {
                                            const linkEl = document.getElementById('{{ $contractsSignatureOpenModalLinkId }}');
                                            if (!linkEl) { return; }
                                            if (linkEl.getAttribute('data-listener-active') === 'true') { return; }
                                            linkEl.setAttribute('data-listener-active', 'true');

                                            linkEl.addEventListener('click', (e) => {
                                                try {
                                                    const href = linkEl.getAttribute('href') ?? '#';
                                                    const url  = linkEl.getAttribute('data-url') ?? href ?? '#';
                                                    if (url !== '#' && href !== '#') { return; }
                                                    e.preventDefault();

                                                    const msg = linkEl.getAttribute('data-guard-msg')
                                                        ?? 'Contracts signature route is unavailable. Please contact technical support or your domain administrator.';

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
                                                        toast.setAttribute('role', 'alert');
                                                        toast.setAttribute('aria-live', 'assertive');
                                                        toast.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toast.appendChild(body);
                                                        container.appendChild(toast);
                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                    } else {
                                                        alert(msg);
                                                    }

                                                    linkEl.setAttribute('data-failed-route', 'true');
                                                } catch (err) {}
                                            });
                                        } catch (err) {}
                                    })();
                                </script>
                            @endpush
                        @endif
                        @php
                            $contractsDownloadPdfBaseRouteName = VW::CTC.'.download.pdf';
                            $contractsDownloadPdfKebabRouteName = Str::kebab($contractsDownloadPdfBaseRouteName);
                            $contractsDownloadPdfResolvedName = Route::has($contractsDownloadPdfBaseRouteName)
                                ? $contractsDownloadPdfBaseRouteName
                                : (Route::has($contractsDownloadPdfKebabRouteName) ? $contractsDownloadPdfKebabRouteName : null);
                            $contractsIdValue = (string) ($cid ?? '');
                            $contractsEncryptedId = $contractsIdValue !== '' ? Crypt::encrypt($contractsIdValue) : null;
                            $contractsDownloadPdfUrl = ($contractsDownloadPdfResolvedName && $contractsEncryptedId) ? route($contractsDownloadPdfResolvedName, $contractsEncryptedId) : '#';
                            $contractsLangValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $contractsDownloadPdfGuardMessage = Utility::fetchLinkMessage($contractsLangValue, VW::CTC, 'download_pdf_contracts_route_unavailable') ?? 'Contracts PDF download route is unavailable. Please contact technical support or your domain administrator.';
                            $contractsDownloadPdfLinkId = 'contracts-download-pdf-link-'.($contractsIdValue === '' ? 'x' : $contractsIdValue);
                        @endphp
                        <a id="{{ $contractsDownloadPdfLinkId }}"
                        href="{{ $contractsDownloadPdfUrl }}"
                        class="{{ VC::BT_SM_PM }} btn-icon"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Download') }}"
                        target="_blank"
                        data-url="{{ $contractsDownloadPdfUrl }}"
                        data-guard-msg="{{ $contractsDownloadPdfGuardMessage }}"
                        data-sv-localized="true">
                            <i class="{{ VC::TI_DWN }}"></i>
                        </a>
                        @push(StacksConstants::CTC_SCR_PG)
                            <script defer>
                                (() => {
                                    try {
                                        const linkEl = document.getElementById('{{ $contractsDownloadPdfLinkId }}');
                                        if (!linkEl) { return; }
                                        if (linkEl.getAttribute('data-listener-active') === 'true') { return; }
                                        linkEl.setAttribute('data-listener-active','true');
                                        linkEl.addEventListener('click',(e) => {
                                            try {
                                                const href = linkEl.getAttribute('href') ?? '#';
                                                const url = linkEl.getAttribute('data-url') ?? href ?? '#';
                                                if (url !== '#' && href !== '#') { return; }
                                                e.preventDefault();
                                                const msg = linkEl.getAttribute('data-guard-msg') ?? 'Contracts PDF download route is unavailable. Please contact technical support or your domain administrator.';
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
                                            } catch (err) {}
                                        });
                                    } catch (err) {}
                                })();
                            </script>
                        @endpush
                    </div>
                </div>
            </div>
            <div class="{{ VC::RW }} justify-content-center">
                <div class="{{ VC::RW }} {{ VC::CS9 }}">
                    <div class="{{ VC::CD }}">
                        <div class="card-body">
                            <div class="{{ VC::RW }} invoice-title mt-2">
                                <div class="{{ VC::CL6 }} {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::C12 }}">
                                    @if($logo)
                                        <img src="{{ $logo }}" style="max-width: 150px;"/>
                                    @else
                                        <span class="text-muted">{{ __('No company logo available') }}</span>
                                    @endif
                                </div>
                                <div class="{{ VC::CL6 }} {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::C12 }} text-end">
                                    <h3 class="invoice-number">{{ $cid ? ($isContractFmt ? $user?->contractNumberFormat($cid) : $cid) : __('No contract number available') }}</h3>
                                </div>
                            </div>
                            <div class="{{ VC::R_ALC_M4 }}">
                                <div class="{{ VC::CS6 }} {{ VC::MB3 }} {{ VC::MT3 }} mb-sm-0">
                                    <div class="col-lg-12 col-md-8 {{ VC::MB3 }}">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Type  :') }}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ data_get($contract,'types.name') ?: __('No contract type available') }}</span></span>
                                    </div>
                                    <div class="col-lg-6 col-md-8">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Value   :') }}</h6>
                                        @php $val = isset($contract->value) ? $contract->value : null; @endphp
                                        <span class="col-md-8"><span class="text-md">{{ $val !== null ? ($isPriceFmt ? $user?->priceFormat($val) : $val) : __('No contract value available') }}</span></span>
                                    </div>
                                </div>
                                <div class="{{ VC::CS6 }} text-sm-end">
                                    <div>
                                        <div class="{{ VC::FEND }}">
                                            <div>
                                                <h6 class="d-inline-block m-0 d-print-none">{{ __('Start Date  :') }}</h6>
                                                @php $sd = isset($contract->start_date) ? $contract->start_date : null; @endphp
                                                <span class="col-md-8"><span class="text-md">{{ $sd ? ($isDateFmt ? $user?->dateFormat($sd) : (string)$sd) : __('No start date available') }}</span></span>
                                            </div>
                                            <div class="{{ VC::MT3 }}">
                                                <h6 class="d-inline-block m-0 d-print-none">{{ __('End Date   :') }}</h6>
                                                @php $ed = isset($contract->end_date) ? $contract->end_date : null; @endphp
                                                <span class="col-md-8"><span class="text-md">{{ $ed ? ($isDateFmt ? $user?->dateFormat($ed) : (string)$ed) : __('No end date available') }}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $desc = isset($contract->description) ? $contract->description : null;
                                $cdesc = isset($contract->contract_description) ? $contract->contract_description : null;
                            @endphp
                            <div class="text-md">{!! $desc ?: e(__('No description available')) !!}</div>
                            <br>
                            <div class="text-md">{!! $cdesc ?: e(__('No contract description available')) !!}</div>
                            <div class="{{ VC::RW }}">
                                <div class="col-6">
                                    <div>
                                        @if(!empty($companySig))
                                            <img width="200px" src="{{ $companySig }}">
                                        @else
                                            <span class="text-muted">{{ __('No company signature available') }}</span>
                                        @endif
                                    </div>
                                    <div><h5 class="mt-auto">{{ __('Company Signature') }}</h5></div>
                                </div>
                                <div class="col-6 text-end">
                                    <div>
                                        @if(!empty($clientSig))
                                            <img width="200px" src="{{ $clientSig }}">
                                        @else
                                            <span class="text-muted">{{ __('No client signature available') }}</span>
                                        @endif
                                    </div>
                                    <div><h5 class="mt-auto">{{ __('Client Signature') }}</h5></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    <div class="alert alert-danger">{{ __('Contract data not found.') }}</div>
@endif