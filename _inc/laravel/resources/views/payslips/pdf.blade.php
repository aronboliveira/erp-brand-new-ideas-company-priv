@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);

        $logo = Utility::getFile('uploads/logo');
        $company_logo = Utility::GetLogo();

        $sendBase     = VW::PY_SLP.'.send';
        $sendResolved = Route::has($sendBase) ? $sendBase : null;
        $sendUrl      = ($sendResolved && isset($employee->id, $payslip->salary_month)) ? route($sendResolved, [$employee->id, $payslip->salary_month]) : '#';
        $sendGuard    = Utility::fetchLinkMessage($lang, VW::PY_SLP, 'send_route_unavailable') ?? __('Send Payslip route is unavailable. Please contact technical support or your domain administrator.');

        $canPrice = is_callable([$user, 'priceFormat']);
        $canDate  = is_callable([$user, 'dateFormat']);

        $earnAllowance = $payslipDetail['earning']['allowance']   ?? [];
        $earnCommission= $payslipDetail['earning']['commission']  ?? [];
        $earnOther     = $payslipDetail['earning']['otherPayment']?? [];
        $earnOver      = $payslipDetail['earning']['overTime']    ?? [];
        $dedLoan       = $payslipDetail['deduction']['loan']      ?? [];
        $dedDeduc      = $payslipDetail['deduction']['deduction'] ?? [];

        $earnAllowanceHas = Utility::isFilled($earnAllowance ?? []);
        $earnCommissionHas= Utility::isFilled($earnCommission ?? []);
        $earnOtherHas     = Utility::isFilled($earnOther ?? []);
        $earnOverHas      = Utility::isFilled($earnOver ?? []);
        $dedLoanHas       = Utility::isFilled($dedLoan ?? []);
        $dedDeducHas      = Utility::isFilled($dedDeduc ?? []);

        $totalEarning   = $payslipDetail['totalEarning']   ?? 0;
        $totalDeduction = $payslipDetail['totalDeduction'] ?? 0;
    } catch (\Throwable $e) {
        \Log::error('payslips/pdf — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

<div class="{{ VC::CD_BGN_BX }}">
    <div class="{{ VC::CD_BD }}">
        <div class="{{ VC::TX_END }}">
            <a href="#" id="payslip-download" class="{{ VC::BT_SM_PM }}"><i class="{{ VC::TI_DWN }}"></i></a>
            <a title="{{ __('Mail Send') }}"
               id="payslip-mail-send"
               href="{{ $sendUrl }}"
               data-url="{{ $sendUrl }}"
               data-guard-msg="{{ base64_encode($sendGuard) }}"
               data-sv-localized="true"
               class="{{ VC::BT_SM }} btn-warning"><span class="ti ti-send"></span></a>
        </div>

        <div class="invoice" id="printableArea">
            <div class="invoice-number">
                <img src="{{ $logo.'/'.(!empty($company_logo) ? $company_logo : SettingsConstants::CPN_LG_DK_DEF) }}" width="120px;">
            </div>

            <div class="invoice-print">
                <div class="row">
                    <div class="{{ VC::CL12 }}">
                        <div class="invoice-title"></div>
                        <hr>
                        <div class="row {{ VC::TXSM }}">
                            <div class="{{ VC::CM6 }}">
                                <address>
                                    <strong>{{ __('Name') }} :</strong> {{ $employee->name ?? __('Unknown') }}<br>
                                    <strong>{{ __('Position') }} :</strong> {{ __('Employee') }}<br>
                                    <strong>{{ __('Salary Date') }} :</strong> {{ $canDate ? $user->dateFormat($payslip->created_at ?? now()) : ($payslip->created_at ?? now()) }}<br>
                                </address>
                            </div>
                            <div class="{{ VC::CM6 }} {{ VC::TX_END }}">
                                <address>
                                    <strong>{{ Utility::getValByName('company_name') }} </strong><br>
                                    {{ Utility::getValByName('company_address') }} , {{ Utility::getValByName('company_city') }},<br>
                                    {{ Utility::getValByName('company_state') }}-{{ Utility::getValByName('company_zipcode') }}<br>
                                    <strong>{{ __('Salary Slip') }} :</strong> {{ $payslip->salary_month ?? '-' }}<br>
                                </address>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row {{ VC::MT2 }}">
                    <div class="{{ VC::CM12 }}">
                        <div class="{{ VC::CD_BD_TB_BD }}">
                            <div class="{{ VC::TB_RSP }}">
                                <table class="table table-md">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>{{ __('Earning') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="{{ VC::TX_END }}">{{ __('Amount') }}</th>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Basic Salary') }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td class="{{ VC::TX_END }}">
                                                {{ $canPrice ? $user->priceFormat($payslip->gross_salary ?? 0) : number_format((float)($payslip->gross_salary ?? 0),2) }}
                                            </td>
                                        </tr>

                                        @if($earnAllowanceHas)
                                            @foreach ($earnAllowance as $allowanceRow)
                                                @php
 $allowanceItems = json_decode($allowanceRow->allowance ?? '[]');
@endphp
                                                @foreach ($allowanceItems as $all)
                                                    <tr>
                                                        <td>{{ __('Allowance') }}</td>
                                                        <td>{{ $all->title ?? '-' }}</td>
                                                        <td>{{ isset($all->type) ? ucfirst($all->type) : '-' }}</td>
                                                        @if (($all->type ?? '') !== 'percentage')
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ $canPrice ? $user->priceFormat($all->amount ?? 0) : number_format((float)($all->amount ?? 0),2) }}
                                                            </td>
                                                        @else
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ ($all->amount ?? 0) }}% ({{ $canPrice ? $user->priceFormat((($all->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100) : number_format((float)((($all->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100),2) }})
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <tr><td colspan="4">{{ __('No allowances found') }}</td></tr>
                                        @endif

                                        @if($earnCommissionHas)
                                            @foreach ($earnCommission as $commissionRow)
                                                @php
 $commissionItems = json_decode($commissionRow->commission ?? '[]');
@endphp
                                                @foreach ($commissionItems as $empcom)
                                                    <tr>
                                                        <td>{{ __('Commission') }}</td>
                                                        <td>{{ $empcom->title ?? '-' }}</td>
                                                        <td>{{ isset($empcom->type) ? ucfirst($empcom->type) : '-' }}</td>
                                                        @if (($empcom->type ?? '') !== 'percentage')
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ $canPrice ? $user->priceFormat($empcom->amount ?? 0) : number_format((float)($empcom->amount ?? 0),2) }}
                                                            </td>
                                                        @else
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ ($empcom->amount ?? 0) }}% ({{ $canPrice ? $user->priceFormat((($empcom->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100) : number_format((float)((($empcom->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100),2) }})
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <tr><td colspan="4">{{ __('No commissions found') }}</td></tr>
                                        @endif

                                        @if($earnOtherHas)
                                            @foreach ($earnOther as $otherRow)
                                                @php
 $otherItems = json_decode($otherRow->other_payment ?? '[]');
@endphp
                                                @foreach ($otherItems as $op)
                                                    <tr>
                                                        <td>{{ __('Other Payment') }}</td>
                                                        <td>{{ $op->title ?? '-' }}</td>
                                                        <td>{{ isset($op->type) ? ucfirst($op->type) : '-' }}</td>
                                                        @if (($op->type ?? '') !== 'percentage')
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ $canPrice ? $user->priceFormat($op->amount ?? 0) : number_format((float)($op->amount ?? 0),2) }}
                                                            </td>
                                                        @else
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ ($op->amount ?? 0) }}% ({{ $canPrice ? $user->priceFormat((($op->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100) : number_format((float)((($op->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100),2) }})
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <tr><td colspan="4">{{ __('No other payments found') }}</td></tr>
                                        @endif

                                        @if($earnOverHas)
                                            @foreach ($earnOver as $overRow)
                                                @php
 $otItems = json_decode($overRow->overtime ?? '[]');
@endphp
                                                @foreach ($otItems as $ot)
                                                    @php
                                                        $otTotal = (float)($ot->number_of_days ?? 0) * (float)($ot->hours ?? 0) * (float)($ot->rate ?? 0);
@endphp
                                                    <tr>
                                                        <td>{{ __('OverTime') }}</td>
                                                        <td>{{ $ot->title ?? '-' }}</td>
                                                        <td>-</td>
                                                        <td class="{{ VC::TX_END }}">
                                                            {{ $canPrice ? $user->priceFormat($otTotal) : number_format($otTotal,2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <tr><td colspan="4">{{ __('No overtime found') }}</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="{{ VC::CD_BD_TB_BD }}">
                            <div class="{{ VC::TB_RSP }}">
                                <table class="table table-striped table-hover table-md">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>{{ __('Deduction') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="{{ VC::TX_END }}">{{ __('Amount') }}</th>
                                        </tr>

                                        @if($dedLoanHas)
                                            @foreach ($dedLoan as $loanRow)
                                                @php
 $loanItems = json_decode($loanRow->loan ?? '[]');
@endphp
                                                @foreach ($loanItems as $l)
                                                    <tr>
                                                        <td>{{ __('Loan') }}</td>
                                                        <td>{{ $l->title ?? '-' }}</td>
                                                        <td>{{ isset($l->type) ? ucfirst($l->type) : '-' }}</td>
                                                        @if (($l->type ?? '') !== 'percentage')
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ $canPrice ? $user->priceFormat($l->amount ?? 0) : number_format((float)($l->amount ?? 0),2) }}
                                                            </td>
                                                        @else
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ ($l->amount ?? 0) }}% ({{ $canPrice ? $user->priceFormat((($l->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100) : number_format((float)((($l->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100),2) }})
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <tr><td colspan="4">{{ __('No loans found') }}</td></tr>
                                        @endif

                                        @if($dedDeducHas)
                                            @foreach ($dedDeduc as $dedRow)
                                                @php
 $dedItems = json_decode($dedRow->saturation_deduction ?? '[]');
@endphp
                                                @foreach ($dedItems as $d)
                                                    <tr>
                                                        <td>{{ __('Saturation Deduction') }}</td>
                                                        <td>{{ $d->title ?? '-' }}</td>
                                                        <td>{{ isset($d->type) ? ucfirst($d->type) : '-' }}</td>
                                                        @if (($d->type ?? '') !== 'percentage')
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ $canPrice ? $user->priceFormat($d->amount ?? 0) : number_format((float)($d->amount ?? 0),2) }}
                                                            </td>
                                                        @else
                                                            <td class="{{ VC::TX_END }}">
                                                                {{ ($d->amount ?? 0) }}% ({{ $canPrice ? $user->priceFormat((($d->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100) : number_format((float)((($d->amount ?? 0) * ($payslip->gross_salary ?? 0)) / 100),2) }})
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <tr><td colspan="4">{{ __('No deductions found') }}</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row {{ VC::MT4 }}">
                            <div class="{{ VC::CL8 }}"></div>
                            <div class="{{ VC::CL4 }} {{ VC::TX_END }} {{ VC::TXSM }}">
                                <div class="invoice-detail-item pb-2">
                                    <div class="invoice-detail-name font-bold">{{ __('Total Earning') }}</div>
                                    <div class="invoice-detail-value">
                                        {{ $canPrice ? $user->priceFormat($totalEarning) : number_format((float)$totalEarning,2) }}
                                    </div>
                                </div>
                                <div class="invoice-detail-item">
                                    <div class="invoice-detail-name font-bold">{{ __('Total Deduction') }}</div>
                                    <div class="invoice-detail-value">
                                        {{ $canPrice ? $user->priceFormat($totalDeduction) : number_format((float)$totalDeduction,2) }}
                                    </div>
                                </div>
                                <hr class="{{ VC::MT2 }} {{ VC::MB2 }}">
                                <div class="invoice-detail-item">
                                    <div class="invoice-detail-name font-bold">{{ __('Net Salary') }}</div>
                                    <div class="invoice-detail-value invoice-detail-value-lg">
                                        {{ $canPrice ? $user->priceFormat($payslip->net_payable ?? 0) : number_format((float)($payslip->net_payable ?? 0),2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="text-md-right pb-2 {{ VC::TXSM }}">
                            <div class="float-lg-left mb-lg-0 {{ VC::MB3 }}">
                                <p class="{{ VC::MT2 }}">{{ __('Employee Signature') }}</p>
                            </div>
                            <p class="{{ VC::MT2 }}">{{ __('Paid By') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script defer src="{{ asset('assets/js/routes/payslips/pdf.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/payslips/lang/pdf.js') }}"></script>
    <script defer>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const guardOnce = "data-guard-once";
        const getMsg = el => {
            let msg = errFb;
            if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalized) === "true") {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = "savepdf_unavailable";
            msg = window.translations?.[lang]?.[msgKey] || el.getAttribute(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        const hasBootstrapCss = () => !!document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]');
        const showError = (el, text) => {
            const message = text ?? getMsg(el);
            if (hasBootstrapCss() && window.bootstrap) {
            const wrapId = "toast-wrap-guard";
            if (!document.getElementById(wrapId)) {
                const wrap = document.createElement("div");
                wrap.id = wrapId;
                wrap.className = "position-fixed top-0 end-0 p-3";
                wrap.style.zIndex = "1080";
                document.body.appendChild(wrap);
            }
            const toastId = "toast-savepdf-error";
            if (!document.getElementById(toastId)) {
                const t = document.createElement("div");
                t.id = toastId;
                t.className = "toast align-items-center text-bg-danger border-0";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML = '<div class="{{ VC::DFL }}"><div class="toast-body">' + message + '</div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                document.getElementById(wrapId).appendChild(t);
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            }
            } else {
            alert(message);
            }
        };
        const saveAsPDF = el => {
            try {
            const inputName = document.getElementById("filename");
            const nameVal = inputName?.value?.trim() || "document";
            const element = document.getElementById("printableArea");
            if (!element || typeof html2pdf === "undefined" || !html2pdf?.().set) throw new Error("deps_missing");
            const opt = {
                margin: 0.3,
                filename: nameVal,
                image: { type: "jpeg", quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: "in", format: "A2" }
            };
            html2pdf().set(opt).from(element).save().catch(() => showError(el));
            } catch {
            showError(el);
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
        const selector = '[data-action="save-pdf"]';
        const attach = node => {
            const el = node?.matches?.(selector) ? node : node?.querySelector?.(selector);
            if (!el || el.getAttribute(guardOnce) === "true") return;
            el.setAttribute(guardOnce, "true");
            el.addEventListener("click", e => {
            e.preventDefault();
            saveAsPDF(el);
            }, { passive: true });
        };
        document.querySelectorAll(selector).forEach(attach);
        const mo = new MutationObserver(muts => {
            muts.forEach(m => {
            m.addedNodes && m.addedNodes.forEach(n => attach(n));
            });
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
        window.saveAsPDF = () => saveAsPDF(document.querySelector(selector) ?? document.body);
        })();
    </script>
</div>
