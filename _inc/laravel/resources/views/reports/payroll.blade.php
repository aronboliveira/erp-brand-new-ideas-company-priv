@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('reports/payroll — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Payroll')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Payroll Report')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pdfmake.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/buttons.html5.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/payrolls/lang/pdf.js') }}"></script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-error-guard";
            const dataListenerGuard = "data-listener-guard";

            const ensureToastContainer = () => {
            const id = "np-toast-container";
            let c = qs("#" + id);
            if (c) { return c; }
            c = document.createElement("div");
            c.id = id;
            c.setAttribute("aria-live", "polite");
            c.setAttribute("aria-atomic", "true");
            c.style.position = "fixed";
            c.style.top = "1rem";
            c.style.right = "1rem";
            document.body.appendChild(c);
            return c;
            };

            const showErrorNow = (message) => {
            const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') || qs('link[href*="bootstrap"]')) && window.bootstrap && window.bootstrap.Toast;
            if (hasBootstrap) {
                const container = ensureToastContainer();
                let t = qs("#np-toast", container);
                if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML = '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) { body.textContent = message ?? errFb; }
                try { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } catch (_) { alert(message ?? errFb); }
            } else {
                alert(message ?? errFb);
            }
            };

            const scheduleInteractiveError = (message, usePointerUp) => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
            host.setAttribute(dataErrGuard, "true");
            const evt = usePointerUp ? "pointerup" : "click";
            const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
            document.addEventListener(evt, once, { once: true });
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(host)) { document.removeEventListener(evt, once); o.disconnect(); } });
            mo.observe(document.documentElement, { childList: true, subtree: true });
            };

            const getMsg = (el, key) => {
            let msg = errFb;
            if (el?.getAttribute(dataSvLocalized) === "true" || el?.getAttribute(dataClientLocalized) === "true") { msg = el.getAttribute(dataGuardMsg) || errFb; }
            else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg = window.translations?.[lang]?.[key] || el?.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[key] || errFb;
                if (el && msg !== errFb) { el.setAttribute(dataGuardMsg, msg); el.setAttribute(dataClientLocalized, "true"); }
            }
            return msg;
            };

            const routeOk = (u) => typeof u === "string" && u.trim() !== "" && u.trim() !== "#";

            const saveAsPDF = () => {
            const area = document.getElementById("printableArea");
            if (!area) { scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"), false); return; }
            const name = (($ && $("#filename").val()) ?? "").toString().trim() || "export";
            const opt = { margin: 0.3, filename: name, image: { type: "jpeg", quality: 1 }, html2canvas: { scale: 4, dpi: 72, letterRendering: true }, jsPDF: { unit: "in", format: "A2" } };
            try {
                if (typeof window.html2pdf !== "function") { try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("html2pdf unavailable");
                } catch (_) {} scheduleInteractiveError(getMsg(area, "plugin_unavailable"), false); return; }
                window.html2pdf().set(opt).from(area).save();
            } catch (_) { scheduleInteractiveError(getMsg(area, "pdf_unavailable"), false); }
            };

            window.saveAsPDF = saveAsPDF;

            const bindWithObserver = (el, evt, handler, flag) => {
            if (!el || el.getAttribute(flag) === "true") { return; }
            el.setAttribute(flag, "true");
            $(el).on(evt, handler);
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(el)) { $(el).off(evt, handler); o.disconnect(); } });
            mo.observe(document.body, { childList: true, subtree: true });
            };

            const onTypeChange = function () {
            try {
                const v = $(this).val();
                if (v === "monthly") {
                $(".month").addClass("d-block").removeClass("d-none");
                $(".year").addClass("d-none").removeClass("d-block");
                } else {
                $(".year").addClass("d-block").removeClass("d-none");
                $(".month").addClass("d-none").removeClass("d-block");
                }
            } catch (_) { scheduleInteractiveError(getMsg(document.body, "type_toggle_unavailable"), false); }
            };

            const initTypeRadios = () => {
            const radios = document.querySelectorAll('input[name="type"][type="radio"]');
            radios.forEach((el, i) => bindWithObserver(el, "change", onTypeChange, dataListenerGuard + "-type-" + i));
            const checked = document.querySelector('input[name="type"][type="radio"]:checked');
            if (checked) { onTypeChange.call(checked); }
            };

            const initDataTable = () => {
            const $table = $("#report-dataTable");
            if (!$table.length) { return; }
            if (!$.fn || !$.fn.DataTable) { try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("DataTables unavailable");
            } catch (_) {} scheduleInteractiveError(getMsg($table.get(0), "datatable_unavailable"), false); return; }
            if ($.fn.DataTable.isDataTable($table)) { return; }
            const title = (($ && $("#filename").val()) ?? "").toString().trim() || "export";
            const hasButtons = $.fn.dataTable && $.fn.dataTable.Buttons;
            const opts = hasButtons ? { dom: "lBfrtip", buttons: [{ extend: "pdf", title }, { extend: "excel", title }, { extend: "csv", title }] } : {};
            if (!hasButtons) { try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("DataTables Buttons unavailable");
            } catch (_) {} scheduleInteractiveError(getMsg($table.get(0), "datatable_unavailable"), false); }
            try { $table.DataTable(opts); } catch (_) { scheduleInteractiveError(getMsg($table.get(0), "datatable_unavailable"), false); }
            };

            const deptUrl = '{{route(VW::RPT . ".payroll.getdepartment")}}';
            const empUrl = '{{route(VW::RPT . ".payroll.getemployee")}}';

            const renderDepartmentSelect = (data) => {
            const wrap = document.getElementById("department_div");
            if (!wrap) { return; }
            let label = wrap.querySelector('label[for="department"]');
            if (!label) { label = document.createElement("label"); label.setAttribute("for", "department"); label.className = "form-label"; label.textContent = '{{__("Department")}}'; wrap.appendChild(label); }
            let select = document.getElementById("department_id");
            if (!select) { select = document.createElement("select"); select.className = "form-control"; select.id = "department_id"; select.name = "department_id"; wrap.appendChild(select); }
            select.innerHTML = "";
            const o0 = document.createElement("option"); o0.value = ""; o0.textContent = '{{__("Select Department")}}'; select.appendChild(o0);
            if (data && typeof data === "object") { Object.keys(data).forEach(function (k) { const o = document.createElement("option"); o.value = k; o.textContent = data[k]; select.appendChild(o); }); }
            };

            const renderEmployeeSelect = (data) => {
            const wrap = document.getElementById("employee_div");
            if (!wrap) { return; }
            let label = wrap.querySelector('label[for="employee"]');
            if (!label) { label = document.createElement("label"); label.setAttribute("for", "employee"); label.className = "form-label"; label.textContent = '{{__("Employee")}}'; wrap.appendChild(label); }
            let select = document.getElementById("employee_id");
            if (!select) { select = document.createElement("select"); select.className = "form-control"; select.id = "employee_id"; select.name = "employee_id"; wrap.appendChild(select); }
            select.innerHTML = "";
            const o0 = document.createElement("option"); o0.value = ""; o0.textContent = '{{__("Select Employee")}}'; select.appendChild(o0);
            if (data && typeof data === "object") { Object.keys(data).forEach(function (k) { const o = document.createElement("option"); o.value = k; o.textContent = data[k]; select.appendChild(o); }); }
            };

            const getDepartment = (branchId) => {
            if (!routeOk(deptUrl)) { scheduleInteractiveError(getMsg(document.body, "endpoint_unavailable"), true); return; }
            $.ajax({
                url: deptUrl,
                type: "POST",
                data: { branch_id: branchId, _token: "{{ csrf_token() }}" },
                success: function (data) { try { renderDepartmentSelect(data); } catch (_) { scheduleInteractiveError(getMsg(document.body, "department_unavailable"), true); } },
                error: function () { scheduleInteractiveError(getMsg(document.body, "department_unavailable"), true); }
            });
            };

            const getEmployee = (deptId) => {
            if (!routeOk(empUrl)) { scheduleInteractiveError(getMsg(document.body, "endpoint_unavailable"), true); return; }
            $.ajax({
                url: empUrl,
                type: "POST",
                data: { department_id: deptId, _token: "{{ csrf_token() }}" },
                success: function (data) { try { renderEmployeeSelect(data); } catch (_) { scheduleInteractiveError(getMsg(document.body, "employee_unavailable"), true); } },
                error: function () { scheduleInteractiveError(getMsg(document.body, "employee_unavailable"), true); }
            });
            };

            const bindBranchChange = () => {
            const el = document.querySelector('select[name="branch_id"]');
            if (!el) { return; }
            bindWithObserver(el, "change", function () { const v = $(this).val(); getDepartment(v); }, dataListenerGuard + "-branch");
            };

            const bindDeptChange = () => {
            const el = document.getElementById("department_id");
            if (!el) { return; }
            bindWithObserver(el, "change", function () { const v = $(this).val(); getEmployee(v); }, dataListenerGuard + "-dept");
            };

            const init = () => {
            initTypeRadios();
            initDataTable();
            bindBranchChange();
            bindDeptChange();
            };

            if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init, { once: true }); }
            else { init(); }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::C_AT_FEND }}">
        @php
            try {
                $exportRouteName = VW::RPT . '.payroll.export';
                $exportUrl       = Route::has($exportRouteName) ? route($exportRouteName) : '#';
                $exportLinkId    = 'export-payroll-link';
                $exportGuardMsg  = Utility::fetchLinkMessage($lang, VW::RPT, 'payroll_export_route_unavailable') ?? 'Payroll export is unavailable. Please contact technical support or your domain administrator.';
                $downloadLabel   = __('Download');
                $exportLabel     = __('Export');
            } catch (\Throwable $e) {
                \Log::error('reports/payroll — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a href="{{ $exportUrl }}"
        id="{{ $exportLinkId }}"
        class="{{ VC::BT_SM_PM }} {{ $exportLinkId }}"
        data-url="{{ $exportUrl }}"
        data-sv-localized="true"
        data-guard-msg="{{ base64_encode($exportGuardMsg) }}"
        data-bs-toggle="tooltip"
        title="{{ $exportLabel }}"
        aria-label="{{ $exportLabel }}">
        <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_payroll_report_unavailable') ?? 'Download function for payroll report is unavailable. Please contact technical support or your domain administrator.';
@endphp
        <a href="#"
        class="{{ VC::BT_SM_PM }} download-payroll"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ base64_encode($downloadGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ $downloadLabel }}"
        aria-label="{{ $downloadLabel }}"
        data-original-title="{{ $downloadLabel }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/payrolls/download.js') }}" defer></script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $payrollBase            = VW::RPT.'.payroll';
                                $payrollKebab           = Str::kebab($payrollBase);
                                $payrollResolved        = Route::has($payrollBase) ? $payrollBase : (Route::has($payrollKebab) ? $payrollKebab : null);
                                $payrollUrl             = $payrollResolved ? route($payrollResolved) : '#';
                                $payrollFormId          = 'report_payroll';
                                $applyGuardMsg          = Utility::fetchLinkMessage($lang, VW::RPT, 'payroll_apply_report_route_unavailable') ?? 'Payroll apply route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg          = Utility::fetchLinkMessage($lang, VW::RPT, 'payroll_reset_report_route_unavailable') ?? 'Payroll reset route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('reports/payroll — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $payrollUrl,
                            'id'                => $payrollFormId,
                            'data-url'          => $payrollUrl,
                            'data-guard-msg'    => $applyGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCE }}">
                                <div class="{{ VC::C2 }} {{ VC::MT2 }}">
                                    <label class="{{ VC::FM_LB }}">{{ __('Type') }}</label><br>
                                    <div class="{{ VC::FM_CHK_IL_GP }}">
                                        <input type="radio" id="monthly" value="monthly" name="type" class="form-check-input" {{ isset($_GET['type']) && $_GET['type']=='monthly' ? 'checked' : 'checked' }}>
                                        <label class="form-check-label" for="monthly">{{ __('Monthly') }}</label>
                                    </div>
                                    <div class="{{ VC::FM_CHK_IL_GP }}">
                                        <input type="radio" id="daily" value="daily" name="type" class="form-check-input" {{ isset($_GET['type']) && $_GET['type']=='daily' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="daily">{{ __('Daily') }}</label>
                                    </div>
                                </div>
                                <div class="{{ VC::C2 }} month">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Month'), ['class'=> VC::FM_LB]) }}
                                        {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class'=>'month-btn '.VC::FM_CT]) }}
                                    </div>
                                </div>
                                <div class="{{ VC::C2 }} year d-none">
                                    <div class="btn-box">
                                        {{ Form::label('year', __('Year'), ['class'=> VC::FM_LB]) }}
                                        <select class="{{ VC::FM_CT }} select" id="year" name="year" tabindex="-1" aria-hidden="true">
                                            @for($filterYear['starting_year']; $filterYear['starting_year'] <= $filterYear['ending_year']; $filterYear['starting_year']++)
                                                <option
                                                    {{ (isset($_GET['year']) && $_GET['year'] == $filterYear['starting_year'] ? 'selected' : '') }}
                                                    {{ (!isset($_GET['year']) && date('Y') == $filterYear['starting_year'] ? 'selected' : '') }}
                                                    value="{{ $filterYear['starting_year'] }}"
                                                >{{ $filterYear['starting_year'] }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="{{ VC::C2 }}">
                                    <div class="btn-box">
                                        {{ Form::label('branch', __('Branch'), ['class'=> VC::FM_LB]) }}
                                        <select class="{{ VC::FM_CT }} select" name="branch_id" id="branch_id" placeholder="{{ __('Select Branch') }}" required>
                                            <option value="">{{ __('Select Branch') }}</option>
                                            @foreach(($branch ?? []) as $branchItem)
                                                <option value="{{ $branchItem->id }}">{{ $branchItem->name ?? __('No branch name available') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="{{ VC::C2 }}">
                                    <div class="btn-box" id="department_div">
                                        {{ Form::label('department', __('Department'), ['class'=> VC::FM_LB]) }}
                                        <select class="{{ VC::FM_CT }} select" name="department_id" id="department_id" required="required">
                                            <option value="">{{ __('Select Department') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="{{ VC::C3 }}">
                                    <div class="btn-box" id="employee_div">
                                        {{ Form::label('employee', __('Employee'), ['class'=> VC::FM_LB]) }}
                                        <select class="{{ VC::FM_CT }} select" name="employee_id" id="employee_id">
                                            <option value="">{{ __('Select Employee') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <a href="#"
                                    class="{{ VC::BT_SM_PM }} apply-payroll-report"
                                    data-form-id="{{ $payrollFormId }}"
                                    data-guard-msg="{{ base64_encode($applyGuardMsg) }}"
                                    data-sv-localized="true"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Apply') }}"
                                    data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                    </a>
                                    <a href="{{ $payrollUrl }}"
                                    class="{{ VC::BT_SM_DG }} reset-payroll-report"
                                    data-url="{{ $payrollUrl }}"
                                    data-guard-msg="{{ base64_encode($resetGuardMsg) }}"
                                    data-sv-localized="true"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Reset') }}"
                                    data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                    </a>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/reports/payrolls/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/payrolls/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea" class="{{ VC::MT2 }}">
        <div class="row {{ VC::MT3 }}">
            <div class="col">
                <input type="hidden" value="{{ $filterYear['branch'].' '.__('Branch').' '.$filterYear['dateYearRange'].' '.$filterYear['type'].' '.__('Payroll Report of').' '.$filterYear['department'].' '.__('Department') }}" id="filename">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Report') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ ($filterYear['type'] ?? __('No report type available')).' '.__('Payroll Summary') }}</h7>
                </div>
            </div>
            @if(($filterYear['branch'] ?? 'All') != 'All')
                <div class="col">
                    <div class="{{ VC::CD }} p-4 mb-4">
                        <h6 class="{{ VC::MB0 }}">{{ __('Branch') }} :</h6>
                        <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $filterYear['branch'] ?? __('No branch selected') }}</h7>
                    </div>
                </div>
            @endif
            @if(($filterYear['department'] ?? 'All') != 'All')
                <div class="col">
                    <div class="{{ VC::CD }} p-4 mb-4">
                        <h6 class="{{ VC::MB0 }}">{{ __('Department') }} :</h6>
                        <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $filterYear['department'] ?? __('No department selected') }}</h7>
                    </div>
                </div>
            @endif

            <div class="col">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Duration') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $filterYear['dateYearRange'] ?? __('No duration available') }}</h7>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Basic Salary') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalBasicSalary'] ?? 0) }}</h7>
                </div>
            </div>
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Net Salary') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalNetSalary'] ?? 0) }}</h7>
                </div>
            </div>
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Allowance') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalAllowance'] ?? 0) }}</h7>
                </div>
            </div>
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Commission') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalCommision'] ?? 0) }}</h7>
                </div>
            </div>
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Loan') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalLoan'] ?? 0) }}</h7>
                </div>
            </div>
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Saturation Deduction') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalSaturationDeduction'] ?? 0) }}</h7>
                </div>
            </div>
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Other Payment') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalOtherPayment'] ?? 0) }}</h7>
                </div>
            </div>
            <div class="{{ VW::CL_XS12 }}">
                <div class="{{ VC::CD }} p-4 mb-4">
                    <h6 class="{{ VC::MB0 }}">{{ __('Total Overtime') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $user?->priceFormat($filterData['totalOverTime'] ?? 0) }}</h7>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="col">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }} {{ VC::PY4 }}">
                        <table class="{{ VC::TB }} datatable {{ VC::MB0 }}" id="report-dataTable">
                            <thead>
                            <tr>
                                <th>{{ __('Employee ID') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('Month') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($payslips ?? []) as $payslip)
                                <tr>
                                    <td>
                                        @if(!empty($payslip->employees))
                                            @php
                                                try {
                                                    $empShowBase = VW::EMP.'.show';
                                                    $empShowKebab = Str::kebab($empShowBase);
                                                    $empShowResolved = Route::has($empShowBase) ? $empShowBase : (Route::has($empShowKebab) ? $empShowKebab : null);
                                                    $empIdValue = isset($payslip) && !empty($payslip->employee_id) ? $payslip->employee_id : null;
                                                    $encryptedEmpId = $empIdValue ? Crypt::encrypt($empIdValue) : null;
                                                    $empShowUrl = ($empShowResolved && $encryptedEmpId) ? route($empShowResolved, $encryptedEmpId) : '#';
                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                    $showEmpGuardMsg = Utility::fetchLinkMessage($langValue, VW::EMP, 'show_employee_route_unavailable') ?? 'Show employee route is unavailable. Please contact technical support or your domain administrator.';
                                                    $anchorId = 'employee-show-link-'.($empIdValue ?? 'x');
                                                    $empLabel = $user?->employeeIdFormat($payslip->employees->employee_id) ?? __('No employee ID available');
                                                } catch (\Throwable $e) {
                                                    \Log::error('reports/payroll — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <a id="{{ $anchorId }}"
                                            href="{{ $empShowUrl }}"
                                            class="{{ VC::BT_SM }} btn-outline-primary"
                                            data-guard-msg="{{ base64_encode($showEmpGuardMsg) }}"
                                            data-sv-localized="true">
                                                {{ $empLabel }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const el = document.getElementById('{{ $anchorId }}');
                                                            if (!el) { return; }
                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                            el.setAttribute('data-listener-active', 'true');
                                                            el.addEventListener('click', (e) => {
                                                                try {
                                                                    const href = el.getAttribute('href') ?? '#';
                                                                    if (href !== '#') { return; }
                                                                    e.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? 'Show employee route is unavailable. Please contact technical support or your domain administrator.';
                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                } catch (err) {}
                                                            });
                                                        } catch (err) {}
                                                    })();
                                                </script>
                                            @endpush
                                        @else
                                            <span class="{{ VC::TXT_MT }}">{{ __('No employee found') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($payslip->employees)->name ?? __('No employee name available') }}</td>
                                    <td>{{ $user?->priceFormat($payslip->gross_salary ?? 0) }}</td>
                                    <td>{{ $user?->priceFormat($payslip->net_payable ?? 0) }}</td>
                                    <td>{{ $payslip->salary_month ?? __('No salary month available') }}</td>
                                    <td>
                                        @if(isset($payslip->status) && $payslip->status == 0)
                                            <div class="badge bg-danger p-2 {{ VC::PX3 }} rounded">
                                                <a href="#" class="{{ VC::TXT_WT }}">{{ __('UnPaid') }}</a>
                                            </div>
                                        @elseif(isset($payslip->status) && $payslip->status == 1)
                                            <div class="badge bg-success p-2 {{ VC::PX3 }} rounded">
                                                <a href="#" class="{{ VC::TXT_WT }}">{{ __('Paid') }}</a>
                                            </div>
                                        @else
                                            <span class="{{ VC::TXT_MT }}">{{ __('No status available') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">{{ __('No payslips available') }}</td>
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
