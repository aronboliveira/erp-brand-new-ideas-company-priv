@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PermissionsConstants,
        StacksConstants as ST,
        YieldingConstants as YW,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Collection, Str};
    use Illuminate\Support\Facades\{Auth, Route};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $monthIsList = is_array($month ?? null) && count($month ?? []) > 0;
    $yearIsList  = is_array($year ?? null)  && count($year ?? [])  > 0;

    $indexBase     = VW::PY_SLP.'.index';
    $indexResolved = Route::has($indexBase) ? $indexBase : null;
    $indexUrl      = $indexResolved ? route($indexResolved) : '#';
    $indexGuard    = Utility::fetchLinkMessage($lang, VW::PY_SLP, 'payslip_index_route_unavailable')
                    ?? __('Payslip index route is unavailable. Please contact technical support or your domain administrator.');

    $storeBase     = VW::PY_SLP.'.store';
    $storeResolved = Route::has($storeBase) ? $storeBase : null;
    $storeUrl      = $storeResolved ? route($storeResolved) : '#';
    $storeGuard    = Utility::fetchLinkMessage($lang, VW::PY_SLP, 'store_route_unavailable')
                    ?? __('Store Payslip route is unavailable. Please contact technical support or your domain administrator.');

    $exportBase     = VW::PY_SLP.'.export';
    $exportResolved = Route::has($exportBase) ? $exportBase : null;
    $exportUrl      = $exportResolved ? route($exportResolved) : '#';
    $exportGuard    = Utility::fetchLinkMessage($lang, VW::PY_SLP, 'export_route_unavailable')
                     ?? __('Export Payslip route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Payslip') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a id="bc-payslip-index-link"
           href="{{ $indexUrl }}"
           data-url="{{ $indexUrl }}"
           data-guard-msg="{{ $indexGuard }}"
           data-sv-localized="true">
            {{ __('Payslip') }}
        </a>
    </li>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::C12 }} {{ VC::MT4 }}">
        <div class="card">
            <div class="card-body">
                {{ Form::open([
                    'url'               => $storeUrl,
                    'method'            => 'POST',
                    'id'                => 'payslip-generate-form',
                    'data-url'          => $storeUrl,
                    'data-guard-msg'    => $storeGuard,
                    'data-sv-localized' => 'true'
                ]) }}
                <div class="{{ VC::DFL_AIC }} {{ VC::JCE }}">
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 {{ VC::MX3 }}">
                        <div class="btn-box">
                            {{ Form::label('month', __('Select Month'), ['class' => VC::FM_LB]) }}
                            @if($monthIsList)
                                {{ Form::select('month', $month, date('m'), ['class' => VC::FM_CT_SL, 'id' => 'month']) }}
                            @else
                                <select id="month" name="month" class="{{ VC::FM_CT_SL }}">
                                    <option value="">{{ __('No months available') }}</option>
                                </select>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 {{ VC::MX3 }}">
                        <div class="btn-box">
                            {{ Form::label('year', __('Select Year'), ['class' => VC::FM_LB]) }}
                            @if($yearIsList)
                                {{ Form::select('year', $year, null, ['class' => VC::FM_CT_SL, 'id' => 'year']) }}
                            @else
                                <select id="year" name="year" class="{{ VC::FM_CT_SL }}">
                                    <option value="">{{ __('No years available') }}</option>
                                </select>
                            @endif
                        </div>
                    </div>
                    <div class="{{ VC::C_AT_FEND }}">
                        <a href="#" id="payslip-generate-btn" class="{{ VC::BT_PRM }}" data-bs-toggle="tooltip" title="{{ __('Payslip') }}">
                            {{ __('Generate Payslip') }}
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="card-header">
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::CM4 }}">
                        <div class="{{ VC::DFL_AIC }}">
                            <h5 class="{{ VC::MB0 }}">{{ __('Find Employee Payslip') }}</h5>
                        </div>
                    </div>
                    <div class="{{ VC::CM8 }}">
                        <div class="{{ VC::DFL_AIC }} {{ VC::JCE }}">
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 {{ VC::MX3 }}">
                                <div class="btn-box">
                                    @if($monthIsList)
                                        <select class="{{ VC::FM_CT_SL }} month_date" name="filter_month" aria-hidden="true">
                                            <option value="--">--</option>
                                            @foreach($month as $k => $mon)
                                                @php $selected = (date('m') == $k) ? 'selected' : ''; @endphp
                                                <option value="{{ $k }}" {{ $selected }}>{{ $mon }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select class="{{ VC::FM_CT_SL }} month_date" name="filter_month">
                                            <option value="">{{ __('No months available') }}</option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 {{ VC::ME3 }}">
                                <div class="btn-box">
                                    @if($yearIsList)
                                        {{ Form::select('filter_year', $year, null, ['class' => VC::FM_CT_SL.' year_date']) }}
                                    @else
                                        <select class="{{ VC::FM_CT_SL }} year_date" name="filter_year">
                                            <option value="">{{ __('No years available') }}</option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} {{ VC::ME3 }}">
                                {{ Form::open([
                                    'url'               => $exportUrl,
                                    'method'            => 'POST',
                                    'id'                => 'payslip-export-form',
                                    'data-url'          => $exportUrl,
                                    'data-guard-msg'    => $exportGuard,
                                    'data-sv-localized' => 'true'
                                ]) }}
                                    <input type="hidden" name="filter_month" class="filter_month">
                                    <input type="hidden" name="filter_year" class="filter_year">
                                    <input type="submit" value="{{ __('Export') }}" class="{{ VC::BT_PRM }}">
                                {{ Form::close() }}
                            </div>
                            <div class="{{ VC::C_AT }}">
                                @can(PermissionsConstants::CR_PSL)
                                    <input type="button" value="{{ __('Bulk Payment') }}" class="{{ VC::BT_PRM }}" id="bulk_payment">
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="{{ VC::TB }}" id="pc-dt-render-column-cells">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Id') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Payroll Type') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/payslips/index.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/payslips/lang/index.js') }}"></script>
    <script defer>
        (function () {
        var $ = window.jQuery;
        var errFb = "# ERROR";
        var dataClientLocalized = "data-client-localized";
        var dataGuardMsg = "data-guard-msg";
        var dataSvLocalized = "data-sv-localized";
        var dataBound = "data-bound-payslip";
        var dataErrArmed = "data-err-armed";
        var getMsg = function (el, key) {
            var msg = errFb;
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            var lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        var hasBS = function () {
            return !!(
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap &&
            window.bootstrap.Toast
            );
        };
        var ensureToastContainer = function () {
            var c = document.querySelector("#np-toast-container");
            if (c) {
            return c;
            }
            c = document.createElement("div");
            c.id = "np-toast-container";
            c.style.position = "fixed";
            c.style.top = "1rem";
            c.style.right = "1rem";
            c.setAttribute("aria-live", "polite");
            c.setAttribute("aria-atomic", "true");
            document.body.appendChild(c);
            return c;
        };
        var showToast = function (message) {
            var container = ensureToastContainer();
            var t = document.querySelector("#np-toast");
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
            var body = t.querySelector(".toast-body");
            if (body) {
            body.textContent = message || errFb;
            }
            try {
            new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            } catch (_) {
            alert(message || errFb);
            }
        };
        var armClickError = function (key, host) {
            var el = host || document.body;
            if (!el || el.getAttribute(dataErrArmed) === "true") {
            return;
            }
            el.setAttribute(dataErrArmed, "true");
            var once = function () {
            try {
                var m = getMsg(el, key);
                if (hasBS()) {
                showToast(m);
                } else {
                alert(m);
                }
            } finally {
                el.removeAttribute(dataErrArmed);
            }
            };
            document.addEventListener("click", once, { once: true });
            var mo = new MutationObserver(function (m, o) {
            if (!document.body.contains(el)) {
                document.removeEventListener("click", once);
                o.disconnect();
            }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
        };
        var renderTable = function (rows, datePicker) {
            var tbody = document.querySelector("#pc-dt-render-column-cells tbody");
            if (!tbody) {
            return;
            }
            var html = "";
            if (Array.isArray(rows) && rows.length > 0) {
            rows.forEach(function (v) {
                var status =
                v[6] === "Paid"
                    ? '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                    v[6] +
                    "</a></div>"
                    : '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                    v[6] +
                    "</a></div>";
                var id = v[0];
                var employeeLink = v["url"];
                var payslipId = v[7];
                var payslipBtn =
                payslipId !== 0
                    ? `<a href="#" data-url="{{ url('payslip/pdf/') }}/'+id+'/'+datePicker+'" data-size="lg" data-ajax-popup="true" class="btn-sm btn btn-warning" data-title="{{ __('Employee Payslip') }}">{{ __('Payslip') }}</a> '):"";
                var clickToPaid=(v[6]==="UnPaid"&&payslipId!==0)?('<a href="{{ url('payslip/paysalary/') }}/'+id+'/'+datePicker+'" class="btn-sm btn btn-primary">{{ __('Click To Paid') }}</a> `
                    : "";
                var edit =
                payslipId !== 0 && v[6] === "UnPaid"
                    ? `<a href="#" data-url="{{ url('payslip/editemployee/') }}/'+payslipId+'" data-ajax-popup="true" class="btn-sm btn btn-info" data-title="{{ __('Edit Employee salary') }}">{{ __('Edit') }}</a> `
                    : "";
                var url =
                "{{ route(VW::PY_SLP.'.delete', ':id') }}'.replace(':id',payslipId)";
                var deleted =
                payslipId !== 0
                    ? `<a href="#" data-url="'+url+'" class="payslip_delete view-btn btn btn-danger ms-1 btn-sm">{{ __('Delete') }}</a>`
                    : "";
                html +=
                "<tr>" +
                '<td><a class="btn btn-outline-primary" href="' +
                employeeLink +
                '">' +
                v[1] +
                "</a></td>" +
                "<td>" +
                v[2] +
                "</td>" +
                "<td>" +
                v[3] +
                "</td>" +
                "<td>" +
                v[4] +
                "</td>" +
                "<td>" +
                v[5] +
                "</td>" +
                "<td>" +
                status +
                "</td>" +
                "<td>" +
                payslipBtn +
                clickToPaid +
                edit +
                deleted +
                "</td>" +
                "</tr>";
            });
            } else {
            var cols =
                (
                document.querySelectorAll("#pc-dt-render-column-cells thead tr th") ||
                []
                ).length || 1;
            var lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            var empty =
                window.translations?.[lang]?.no_entries ||
                window.translations?.en?.no_entries ||
                "No entries found";
            html =
                '<tr><td class="dataTables-empty" colspan="' +
                cols +
                '">' +
                empty +
                "</td></tr>";
            }
            tbody.innerHTML = html;
            try {
            if (window.simpleDatatables && window.simpleDatatables.DataTable) {
                new window.simpleDatatables.DataTable(
                document.querySelector("#pc-dt-render-column-cells")
                );
            }
            } catch (_) {}
        };
        var fetchPayslips = function () {
            var month = ($(".month_date").val() || "").trim();
            var year = ($(".year_date").val() || "").trim();
            if (!month) {
            month = "{{date('m', strtotime('last month'))}}";
            year = "{{date('Y')}}";
            }
            $(".filter_month").val(month);
            $(".filter_year").val(year);
            var datePicker = year + "-" + month;
            var url = "{{ route(VW::PY_SLP.'.searchJson') }}";
            if (!url || url === "#") {
            armClickError("payslip_unavailable");
            return;
            }
            try {
            $.ajax({
                url: url,
                type: "POST",
                data: { datePicker: datePicker, _token: "{{ csrf_token() }}" },
                success: function (data) {
                renderTable(data, datePicker);
                },
                error: function () {
                armClickError("payslip_unavailable");
                },
            });
            } catch (_) {
            armClickError("request_failed");
            }
        };
        var bindHandlers = function () {
            var root = document.documentElement;
            if (root.getAttribute(dataBound) === "true") {
            return;
            }
            root.setAttribute(dataBound, "true");
            $(document).on("change", ".month_date,.year_date", function () {
            fetchPayslips();
            });
            $(document).on("click", "#bulk_payment", function () {
            var month = ($(".month_date").val() || "").trim();
            var year = ($(".year_date").val() || "").trim();
            if (!month) {
                month =
                "{{date('m', strtotime('last month'))}}';year='{{date('Y')}}';}";
                var datePicker = year + "-" + month;
                var title = "Bulk Payment";
                var size = "md";
                var url = "payslip/bulk_pay_create/" + datePicker;
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass("modal-" + size);
                $.ajax({
                url: url,
                success: function (data) {
                    if (data && data.length) {
                    $("#commonModal .body").html(data);
                    $("#commonModal").modal("show");
                    } else {
                    var m = getMsg(document.body, "permission_denied");
                    if (hasBS()) {
                        showToast(m);
                    } else {
                        alert(m);
                    }
                    $("#commonModal").modal("hide");
                    }
                },
                error: function (resp) {
                    var el = document.body;
                    el.setAttribute("data-guard-msg", getMsg(el, "permission_denied"));
                    armClickError("permission_denied", el);
                },
                });
            }
            });
            $(document).on("click", ".payslip_delete", function (e) {
            e.preventDefault();
            var url = $(this).data("url") || "";
            if (!url) {
                armClickError("request_failed", this);
                return;
            }
            var ok = window.confirm(
                "{{ __('are you sure you want to delete this payslip?') }}"
            );
            if (!ok) {
                return;
            }
            $.ajax({
                type: "GET",
                url: url,
                dataType: "JSON",
                success: function () {
                var el = document.body;
                el.setAttribute("data-guard-msg", getMsg(el, "deleted_success"));
                if (hasBS()) {
                    showToast(getMsg(el, "deleted_success"));
                } else {
                    alert(getMsg(el, "deleted_success"));
                }
                setTimeout(function () {
                    window.location.reload();
                }, 800);
                },
                error: function () {
                armClickError("request_failed");
                },
            });
            });
            var mo = new MutationObserver(function () {
            if (
                !document.body.contains(
                document.querySelector("#pc-dt-render-column-cells")
                )
            ) {
                $(document).off("change", ".month_date,.year_date");
                $(document).off("click", "#bulk_payment");
                $(document).off("click", ".payslip_delete");
                root.removeAttribute(dataBound);
                mo.disconnect();
            }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        };
        var start = function () {
            if (!($ && $.ajax)) {
            try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
            } catch (_) {}
            armClickError("request_failed");
            return;
            }
            $(".filter_month").val($(".month_date").val() || "");
            $(".filter_year").val($(".year_date").val() || "");
            fetchPayslips();
            bindHandlers();
        };
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", start, { once: true });
        } else {
            start();
        }
        })();
    </script>
@endpush
