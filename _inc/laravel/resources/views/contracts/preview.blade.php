@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $siteRtl = !empty($settings[SettingsConstants::RTL] ) ? $settings[SettingsConstants::RTL]  : 'off';
    } catch (\Throwable $e) {
        \Log::error('contracts/preview — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::CTC)
@if(!empty($contract) && isset($contract->id))
    @push(StacksConstants::CTC_SCR_PG)
        <script src="{{ asset('assets/js/routes/contracts/shared/helpers.js') }}"></script>
        <script src="{{ asset('assets/js/routes/contracts/lang/preview.js') }}"></script>
        <script defer>
            (function () {
            const H = window.ContractHelpers || {};
            const qs = H.qs || ((s, r = document) => r.querySelector(s));
            const showError = H.showError || (m => alert(m));
            const getMsg = H.getMsg || ((el, key) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
            const scheduleErrorOnEvent = H.scheduleErrorOnEvent || showError;
            const isLocalhost = H.isLocalhost || (() => window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');
            const dataBindGuard = "data-contract-export-bound";

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
                    scheduleErrorOnEvent(getMsg(document.body, "contract_export_unavailable"), "pointerup");
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
                        if (isLocalhost()) console.error("html2pdf unavailable");
                    } catch (_) {}
                    scheduleErrorOnEvent(getMsg(document.body, "contract_export_unavailable"), "pointerup");
                    return;
                }
                window
                    .html2pdf()
                    .set(opt)
                    .from(element)
                    .save()
                    .then(closeScript)
                    .catch(function () {
                    scheduleErrorOnEvent(getMsg(document.body, "contract_export_unavailable"), "pointerup");
                    });
                const mo = new MutationObserver(function () {
                    if (!document.body.contains(element)) {
                    host.removeAttribute(dataBindGuard);
                    }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
                } catch (_) {
                scheduleErrorOnEvent(getMsg(document.body, "contract_export_unavailable"), "pointerup");
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
                            try {
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
                            } catch (\Throwable $e) {
                                \Log::error('contracts/preview — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        @if($canSign)
                            @php
                                try {
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
                                } catch (\Throwable $e) {
                                    \Log::error('contracts/preview — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <a href="{{ $contractsSignatureUrl }}"
                            id="{{ $contractsSignatureOpenModalLinkId }}"
                            class="{{ VC::BT_SM_PM }} btn-icon"
                            data-bs-toggle="modal"
                            data-bs-target="#exampleModal"
                            data-size="md"
                            data-url="{{ $contractsSignatureUrl }}"
                            data-bs-whatever="{{ __('signature') }}"
                            data-guard-msg="{{ base64_encode($contractsSignatureGuardMessage) }}"
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
                                        const H = window.ContractHelpers;
                                        if (H?.guardAnchor) {
                                            H.guardAnchor(document.getElementById('{{ $contractsSignatureOpenModalLinkId }}'), true);
                                        }
                                    })();
                                </script>
                            @endpush
                        @endif
                        @php
                            try {
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
                            } catch (\Throwable $e) {
                                \Log::error('contracts/preview — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <a id="{{ $contractsDownloadPdfLinkId }}"
                        href="{{ $contractsDownloadPdfUrl }}"
                        class="{{ VC::BT_SM_PM }} btn-icon"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Download') }}"
                        target="_blank"
                        data-url="{{ $contractsDownloadPdfUrl }}"
                        data-guard-msg="{{ base64_encode($contractsDownloadPdfGuardMessage) }}"
                        data-sv-localized="true">
                            <i class="{{ VC::TI_DWN }}"></i>
                        </a>
                        @push(StacksConstants::CTC_SCR_PG)
                            <script defer>
                                (() => {
                                    const H = window.ContractHelpers;
                                    if (H?.guardAnchor) {
                                        H.guardAnchor(document.getElementById('{{ $contractsDownloadPdfLinkId }}'), true);
                                    }
                                })();
                            </script>
                        @endpush
                    </div>
                </div>
            </div>
            <div class="{{ VC::RW }} justify-content-center">
                <div class="{{ VC::RW }} {{ VC::CS9 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::RW }} invoice-title mt-2">
                                <div class="{{ VC::CL6 }} {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::C12 }}">
                                    @if($logo)
                                        <img src="{{ $logo }}" style="max-width: 150px;"/>
                                    @else
                                        <span class="{{ VC::TXT_MT }}">{{ __('No company logo available') }}</span>
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
                                        <span class="{{ VC::CM8 }}"><span class="text-md">{{ data_get($contract,'types.name') ?: __('No contract type available') }}</span></span>
                                    </div>
                                    <div class="{{ VC::CL6 }} {{ VC::CM8 }}">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Value   :') }}</h6>
                                        @php
 $val = isset($contract->value) ? $contract->value : null;
@endphp
                                        <span class="{{ VC::CM8 }}"><span class="text-md">{{ $val !== null ? ($isPriceFmt ? $user?->priceFormat($val) : $val) : __('No contract value available') }}</span></span>
                                    </div>
                                </div>
                                <div class="{{ VC::CS6 }} text-sm-end">
                                    <div>
                                        <div class="{{ VC::FEND }}">
                                            <div>
                                                <h6 class="d-inline-block m-0 d-print-none">{{ __('Start Date  :') }}</h6>
                                                @php
 $sd = isset($contract->start_date) ? $contract->start_date : null;
@endphp
                                                <span class="{{ VC::CM8 }}"><span class="text-md">{{ $sd ? ($isDateFmt ? $user?->dateFormat($sd) : (string)$sd) : __('No start date available') }}</span></span>
                                            </div>
                                            <div class="{{ VC::MT3 }}">
                                                <h6 class="d-inline-block m-0 d-print-none">{{ __('End Date   :') }}</h6>
                                                @php
 $ed = isset($contract->end_date) ? $contract->end_date : null;
@endphp
                                                <span class="{{ VC::CM8 }}"><span class="text-md">{{ $ed ? ($isDateFmt ? $user?->dateFormat($ed) : (string)$ed) : __('No end date available') }}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $desc = isset($contract->description) ? $contract->description : null;
                                $cdesc = isset($contract->contract_description) ? $contract->contract_description : null;
@endphp
                            {{-- purify_html: contract descriptions are rich-text editor content stored in DB --}}
                            <div class="text-md">{!! !empty($desc) ? purify_html($desc) : e(__('No description available')) !!}</div>
                            <br>
                            <div class="text-md">{!! !empty($cdesc) ? purify_html($cdesc) : e(__('No contract description available')) !!}</div>
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::C6 }}">
                                    <div>
                                        @if(!empty($companySig))
                                            <img width="200px" src="{{ $companySig }}">
                                        @else
                                            <span class="{{ VC::TXT_MT }}">{{ __('No company signature available') }}</span>
                                        @endif
                                    </div>
                                    <div><h5 class="mt-auto">{{ __('Company Signature') }}</h5></div>
                                </div>
                                <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                                    <div>
                                        @if(!empty($clientSig))
                                            <img width="200px" src="{{ $clientSig }}">
                                        @else
                                            <span class="{{ VC::TXT_MT }}">{{ __('No client signature available') }}</span>
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
    <div class="{{ VC::ALT_DNG }}">{{ __('Contract data not found.') }}</div>
@endif
