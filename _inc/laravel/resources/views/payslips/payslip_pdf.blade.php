@php
#payslip_pdf
    use App\Models\Utility;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        SettingsConstants,
        StacksConstants
    };
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
    $canGetFile = is_callable([Utility::class,'getFile']);
    $canGetValByName = is_callable([Utility::class,'getValByName']);
    $canPriceFormat = is_callable([$user,'priceFormat']);
    $canDateFormat = is_callable([$user,'dateFormat']);

    $logo = $canGetFile ? Utility::getFile('uploads/logo') : '';
    $company_logo = $canGetValByName ? Utility::getValByName(SettingsConstants::CPN_LG) : '';

    $savePdfGuard = ($canFetchMsg ? Utility::fetchLinkMessage(app()->getLocale(), 'generics', 'savepdf_unavailable') : 'Save as PDF is unavailable. Please contact technical support or your domain administrator.') ?? __('Save as PDF is unavailable. Please contact technical support or your domain administrator.');

    $empName = data_get($employee,'name',__('No employee name available'));
    $empCreatedAt = data_get($employee,'created_at');
    $salaryMonth = data_get($payslip,'salary_month');
    $basicSalary = data_get($payslip,'basic_salary',0);
    $netPayable = data_get($payslip,'net_payble',0);

    $allowances = (array) data_get($payslipDetail,'earning.allowance',[]);
    $commissions = (array) data_get($payslipDetail,'earning.commission',[]);
    $otherPayments = (array) data_get($payslipDetail,'earning.otherPayment',[]);
    $overTimes = (array) data_get($payslipDetail,'earning.overTime',[]);
    $loans = (array) data_get($payslipDetail,'deduction.loan',[]);
    $deductions = (array) data_get($payslipDetail,'deduction.deduction',[]);

    $totalEarning = (float) data_get($payslipDetail,'totalEarning',0);
    $totalDeduction = (float) data_get($payslipDetail,'totalDeduction',0);

    $companyName = $canGetValByName ? (Utility::getValByName('company_name') ?? '') : '';
    $companyAddr = $canGetValByName ? (Utility::getValByName('company_address') ?? '') : '';
    $companyCity = $canGetValByName ? (Utility::getValByName('company_city') ?? '') : '';
    $companyState = $canGetValByName ? (Utility::getValByName('company_state') ?? '') : '';
    $companyZip = $canGetValByName ? (Utility::getValByName('company_zipcode') ?? '') : '';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Payslip') }}
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="main-content">
        <div class="text-md-right mb-2">
            <a href="#" class="btn btn-warning" data-action="save-pdf" data-guard-msg="{{ $savePdfGuard }}" data-sv-localized="true"><span class="fa fa-download"></span></a>
        </div>
        <div class="col-8">
            <div class="invoice" id="printableArea">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h4>{{ __('Payslip') }}</h4>
                                <div class="invoice-number">
                                    <img src="{{ rtrim((string)$logo,'/').'/'.(!empty($company_logo)?$company_logo:SettingsConstants::CPN_LG_DK_DEF) }}" width="170" alt="">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>{{ __('Name') }} :</strong> {{ $empName }}<br>
                                        <strong>{{ __('Position') }} :</strong> {{ __('Employee') }}<br>
                                        <strong>{{ __('Salary Date') }} :</strong> {{ $canDateFormat ? $user?->dateFormat($empCreatedAt) : ($empCreatedAt ?? __('No date available')) }}<br>
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>{{ $companyName !== '' ? $companyName : __('No company name available') }}</strong><br>
                                        {{ $companyAddr !== '' ? $companyAddr : __('No address available') }}{{ $companyCity !== '' ? ' , '.$companyCity : '' }},<br>
                                        {{ $companyState !== '' ? $companyState : __('No state available') }}-{{ $companyZip !== '' ? $companyZip : __('No ZIP available') }}<br>
                                        <strong>{{ __('Salary Slip') }} :</strong> {{ $canDateFormat ? $user?->dateFormat($salaryMonth) : ($salaryMonth ?? __('No salary month available')) }}<br>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tbody>
                                    <tr>
                                        <th>{{ __('Earning') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Basic Salary') }}</td>
                                        <td>-</td>
                                        <td class="text-end">{{ $canPriceFormat ? $user?->priceFormat($basicSalary) : $basicSalary }}</td>
                                    </tr>
                                    @if(Utility::isFilled($allowances))
                                        @foreach($allowances as $allowance)
                                            <tr>
                                                <td>{{ __('Allowance') }}</td>
                                                <td>{{ data_get($allowance,'title',__('No title available')) }}</td>
                                                <td class="text-end">{{ $canPriceFormat ? $user?->priceFormat((float) data_get($allowance,'amount',0)) : (float) data_get($allowance,'amount',0) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td>{{ __('Allowance') }}</td><td colspan="2" class="text-end">{{ __('No allowance available') }}</td></tr>
                                    @endif
                                    @if(Utility::isFilled($commissions))
                                        @foreach($commissions as $commission)
                                            <tr>
                                                <td>{{ __('Commission') }}</td>
                                                <td>{{ data_get($commission,'title',__('No title available')) }}</td>
                                                <td class="text-end">{{ $canPriceFormat ? $user?->priceFormat((float) data_get($commission,'amount',0)) : (float) data_get($commission,'amount',0) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td>{{ __('Commission') }}</td><td colspan="2" class="text-end">{{ __('No commission available') }}</td></tr>
                                    @endif
                                    @if(Utility::isFilled($otherPayments))
                                        @foreach($otherPayments as $otherPayment)
                                            <tr>
                                                <td>{{ __('Other Payment') }}</td>
                                                <td>{{ data_get($otherPayment,'title',__('No title available')) }}</td>
                                                <td class="text-end">{{ $canPriceFormat ? $user?->priceFormat((float) data_get($otherPayment,'amount',0)) : (float) data_get($otherPayment,'amount',0) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td>{{ __('Other Payment') }}</td><td colspan="2" class="text-end">{{ __('No other payment available') }}</td></tr>
                                    @endif
                                    @if(Utility::isFilled($overTimes))
                                        @foreach($overTimes as $overTime)
                                            <tr>
                                                <td>{{ __('OverTime') }}</td>
                                                <td>{{ data_get($overTime,'title',__('No title available')) }}</td>
                                                <td class="text-end">{{ $canPriceFormat ? $user?->priceFormat((float) data_get($overTime,'amount',0)) : (float) data_get($overTime,'amount',0) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td>{{ __('OverTime') }}</td><td colspan="2" class="text-end">{{ __('No overtime available') }}</td></tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tbody>
                                    <tr>
                                        <th>{{ __('Deduction') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                    @if(Utility::isFilled($loans))
                                        @foreach($loans as $loan)
                                            <tr>
                                                <td>{{ __('Loan') }}</td>
                                                <td>{{ data_get($loan,'title',__('No title available')) }}</td>
                                                <td class="text-end">{{ $canPriceFormat ? $user?->priceFormat((float) data_get($loan,'amount',0)) : (float) data_get($loan,'amount',0) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td>{{ __('Loan') }}</td><td colspan="2" class="text-end">{{ __('No loan deduction available') }}</td></tr>
                                    @endif
                                    @if(Utility::isFilled($deductions))
                                        @foreach($deductions as $deduction)
                                            <tr>
                                                <td>{{ __('Saturation Deduction') }}</td>
                                                <td>{{ data_get($deduction,'title',__('No title available')) }}</td>
                                                <td class="text-end">{{ $canPriceFormat ? $user?->priceFormat((float) data_get($deduction,'amount',0)) : (float) data_get($deduction,'amount',0) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td>{{ __('Saturation Deduction') }}</td><td colspan="2" class="text-end">{{ __('No saturation deduction available') }}</td></tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-8"></div>
                                <div class="col-lg-4 text-end">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">{{ __('Total Earning') }}</div>
                                        <div class="invoice-detail-value">{{ $canPriceFormat ? $user?->priceFormat($totalEarning) : $totalEarning }}</div>
                                    </div>
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">{{ __('Total Deduction') }}</div>
                                        <div class="invoice-detail-value">{{ $canPriceFormat ? $user?->priceFormat($totalDeduction) : $totalDeduction }}</div>
                                    </div>
                                    <hr class="mt-2 mb-2">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">{{ __('Net Salary') }}</div>
                                        <div class="invoice-detail-value invoice-detail-value-lg">{{ $canPriceFormat ? $user?->priceFormat($netPayable) : $netPayable }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-md-right">
                    <div class="float-lg-left mb-lg-0 mb-3">
                        <p class="mt-2">{{ __('Employee Signature') }}</p>
                    </div>
                    <p class="mt-2">{{ __('Paid By') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('theme-script')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/payslips/payPdf.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/payslips/lang/pdf.js') }}"></script>
    <script defer>
        (() => {
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const onceAttr = "data-guard-once";
            const errFb = "# ERROR";

            const localizeMsg = el => {
            let msg = errFb;
            if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalized) === "true") {
                msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = "savepdf_unavailable";
                msg = window.translations?.[lang]?.[msgKey] || el.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[msgKey] || errFb;
                if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
                }
            }
            return msg;
            };

            const hasBootstrapCss = () => !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');

            const showErrorUI = (anchorEl, message) => {
            const msg = message ?? localizeMsg(anchorEl);
            if (hasBootstrapCss() && window.bootstrap) {
                const toastId = "toast-savepdf-error";
                if (!document.getElementById(toastId)) {
                const wrapId = "toast-wrap-guard";
                if (!document.getElementById(wrapId)) {
                    const wrap = document.createElement("div");
                    wrap.id = wrapId;
                    wrap.className = "position-fixed top-0 end-0 p-3";
                    wrap.style.zIndex = "1080";
                    document.body.appendChild(wrap);
                }
                const container = document.getElementById("toast-wrap-guard");
                const node = document.createElement("div");
                node.id = toastId;
                node.className = "toast align-items-center text-bg-danger border-0";
                node.setAttribute("role", "alert");
                node.setAttribute("aria-live", "assertive");
                node.setAttribute("aria-atomic", "true");
                node.innerHTML = `
                    <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
                container.appendChild(node);
                new window.bootstrap.Toast(node, { autohide: true, delay: 4000 }).show();
                }
            } else {
                alert(msg);
            }
            };

            const saveAsPDF = el => {
            try {
                const element = document.getElementById("printableArea");
                if (!element || typeof html2pdf === "undefined" || !html2pdf?.().set) throw new Error("missing");
                const filename = "{{$employee->name}}" ?? "document";
                const opt = {
                margin: 0.3,
                filename,
                image: { type: "jpeg", quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: "in", format: "A4" }
                };
                html2pdf().set(opt).from(element).save().catch(() => showErrorUI(el));
            } catch {
                showErrorUI(el);
                if (typeof html2pdf === "undefined") {
                try { 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("html2pdf library failed to load");
                 } catch {_}
                }
            }
            };

            const clickSelector = '[data-action="save-pdf"]';
            const attach = root => {
            const btn = root?.matches?.(clickSelector) ? root : root?.querySelector?.(clickSelector);
            if (!btn || btn.getAttribute(onceAttr) === "true") return;
            btn.setAttribute(onceAttr, "true");
            btn.addEventListener("click", ev => {
                ev.preventDefault();
                saveAsPDF(btn);
            }, { passive: true });
            };

            document.querySelectorAll(clickSelector).forEach(attach);

            const mo = new MutationObserver(records => {
            records.forEach(r => {
                r.addedNodes && r.addedNodes.forEach(n => attach(n));
                r.removedNodes && r.removedNodes.forEach(n => {
                if (n?.matches?.(clickSelector)) n.replaceWith(n.cloneNode(true));
                });
            });
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });

            window.saveAsPDF = () => saveAsPDF(document.querySelector(clickSelector) ?? document.body);
        })();
    </script>
@endpush
