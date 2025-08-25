@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Bill, BillPayment, ChartOfAccount, Invoice, Utility, Vendor};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(auth: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Leave Report')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Leave Report')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pdfmake.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/buttons.html5.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/leaves/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/leaves/pdf.js') }}"></script>
@endpush
{{--        <a href="{{ route('leave.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}"--}}
{{--           class="btn btn-sm btn-primary">--}}
{{--            <i class="ti ti-file-export"></i>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_leave_reports_unavailable') ?? 'Download function for leave reports is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        id="download-leave-reports-link"
        class="{{ VC::BT_SM_PM }} download-leave-reports"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCRP_PG)
            <script src="{{ asset('assets/js/routes/reports/leaves/download.js') }}" defer></script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $leaveReportBase    = VW::RPT.'.leave';
                            $leaveReportKebab   = Str::kebab($leaveReportBase);
                            $leaveReportResolved= Route::has($leaveReportBase) ? $leaveReportBase : (Route::has($leaveReportKebab) ? $leaveReportKebab : null);
                            $leaveReportUrl     = $leaveReportResolved ? route($leaveReportResolved) : '#';
                            $leaveReportGuardMsg= Utility::fetchLinkMessage($lang, VW::RPT, 'leave_report_route_unavailable') ?? 'Leave report route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $leaveReportUrl,
                            'id'                => 'report_leave',
                            'data-url'          => $leaveReportUrl,
                            'data-guard-msg'    => $leaveReportGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ VC::RW }}">
                                        <div class="col-3 {{ VC::MT2 }}">
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
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                            <div class="btn-box">
                                                {{ Form::label('month', __('Month'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class'=>'month-btn ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 year d-none">
                                            <div class="btn-box">
                                                {{ Form::label('year', __('Year'), ['class'=> VC::FM_LB]) }}
                                                <select class="{{ VC::FM_CT_SL }}" id="year" name="year" tabindex="-1" aria-hidden="true">
                                                    @for($filterYear['starting_year']; $filterYear['starting_year'] <= $filterYear['ending_year']; $filterYear['starting_year']++)
                                                        <option
                                                            {{ (isset($_GET['year']) && $_GET['year'] == $filterYear['starting_year'] ? 'selected' : '') }}
                                                            {{ (!isset($_GET['year']) && date('Y') == $filterYear['starting_year'] ? 'selected' : '') }}
                                                            value="{{ $filterYear['starting_year'] }}">
                                                            {{ $filterYear['starting_year'] }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('branch', __('Branch'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('department', __('Department'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-leave-report"
                                            data-form-id="report_leave"
                                            data-guard-msg="{{ $leaveReportGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $leaveReportUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-leave-report"
                                            data-url="{{ $leaveReportUrl }}"
                                            data-guard-msg="{{ $leaveReportGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                            data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCRP_PG)
                            <script src="{{ asset('assets/js/routes/reports/leaves/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/leaves/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea" class="">
        <div class="{{ VC::RW }}">
            <div class="col">
                <div class="{{ VC::CD }}">
                    <div class="card-body p-3">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-report"></i>
                                </div>
                                <div class="{{ VC::MS2 }}">
                                    <input type="hidden"
                                           value="{{ (data_get($filterYear,'branch',__('No branch selected'))) . ' ' . __('Branch') . ' ' . (data_get($filterYear,'dateYearRange',__('No date range available'))) . ' ' . (data_get($filterYear,'type',__('No type selected'))) . ' ' . __('Leave Report of') . ' ' . (data_get($filterYear,'department',__('No department selected'))) . ' ' . __('Department') }}"
                                           id="filename">
                                    <h5 class="{{ VC::MB0 }}">{{ __('Report') }}</h5>
                                    <div>
                                        <p class="text-muted text-sm {{ VC::MB0 }}">
                                            {{ (data_get($filterYear,'type',__('No type selected'))) . ' ' . __('Leave Summary') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (data_get($filterYear,'branch','All') != 'All')
                <div class="col">
                    <div class="{{ VC::CD }}">
                        <div class="card-body p-3">
                            <div class="{{ VC::DFL_AIC_JCB }}">
                                <div class="{{ VC::DFL_AIC }}">
                                    <div class="theme-avatar bg-primary">
                                        <i class="ti ti-sitemap"></i>
                                    </div>
                                    <div class="{{ VC::MS2 }}">
                                        <h5 class="{{ VC::MB0 }}">{{ __('Branch') }}</h5>
                                        <p class="text-muted text-sm {{ VC::MB0 }}">{{ data_get($filterYear,'branch',__('No branch selected')) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (data_get($filterYear,'department','All') != 'All')
                <div class="col">
                    <div class="{{ VC::CD }}">
                        <div class="card-body p-3">
                            <div class="{{ VC::DFL_AIC_JCB }}">
                                <div class="{{ VC::DFL_AIC }}">
                                    <div class="theme-avatar bg-primary">
                                        <i class="ti ti-template"></i>
                                    </div>
                                    <div class="{{ VC::MS2 }}">
                                        <h5 class="{{ VC::MB0 }}">{{ __('Department') }}</h5>
                                        <p class="text-muted text-sm {{ VC::MB0 }}">{{ data_get($filterYear,'department',__('No department selected')) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col">
                <div class="{{ VC::CD }}">
                    <div class="card-body p-3">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-calendar"></i>
                                </div>
                                <div class="{{ VC::MS2 }}">
                                    <h5 class="{{ VC::MB0 }}">{{ __('Duration') }}</h5>
                                    <p class="text-muted text-sm {{ VC::MB0 }}">{{ data_get($filterYear,'dateYearRange',__('No date range available')) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body p-3">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-circle-check"></i>
                                </div>
                                <div class="{{ VC::MS2 }}">
                                    <h5 class="{{ VC::MB0 }}">{{ __('Approved Leaves') }}</h5>
                                    <p class="text-muted text-sm {{ VC::MB0 }}">{{ data_get($filter,'totalApproved',__('No approved leaves available')) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body p-3">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-circle-x"></i>
                                </div>
                                <div class="{{ VC::MS2 }}">
                                    <h5 class="{{ VC::MB0 }}">{{ __('Rejected Leave') }}</h5>
                                    <p class="text-muted text-sm {{ VC::MB0 }}">{{ data_get($filter,'totalReject',__('No rejected leaves available')) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body p-3">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-circle-minus"></i>
                                </div>
                                <div class="{{ VC::MS2 }}">
                                    <h5 class="{{ VC::MB0 }}">{{ __('Pending Leaves') }}</h5>
                                    <p class="text-muted text-sm {{ VC::MB0 }}">{{ data_get($filter,'totalPending',__('No pending leaves available')) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @php
            $typeParam  = isset($_GET['type'])  ? $_GET['type']  : 'no';
            $monthParam = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
            $yearParam  = isset($_GET['year'])  ? $_GET['year']  : date('Y');
        @endphp
        <div class="{{ VC::RW }}">
            <div class="col">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive py-4">
                            <table class="{{ VC::TB }} {{ VC::MB0 }}" id="report-dataTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee ID') }}</th>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Approved Leaves') }}</th>
                                        <th>{{ __('Rejected Leaves') }}</th>
                                        <th>{{ __('Pending Leaves') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leaves as $leave)
                                        @php
                                            $empId       = data_get($leave,'employee_id');
                                            $empName     = data_get($leave,'employee',__('No employee name available'));
                                            $leaveId     = data_get($leave,'id');
                                            $approved    = data_get($leave,'approved',__('No approved leaves available'));
                                            $rejected    = data_get($leave,'reject',__('No rejected leaves available'));
                                            $pending     = data_get($leave,'pending',__('No pending leaves available'));
                                            $empLeaveBase     = VW::RPT.'.employee.leave';
                                            $empLeaveKebab    = Str::kebab($empLeaveBase);
                                            $empLeaveResolved = Route::has($empLeaveBase) ? $empLeaveBase : (Route::has($empLeaveKebab) ? $empLeaveKebab : null);
                                            $approvedParams   = ($leaveId ?? false) ? [$leaveId, 'Approved', $typeParam ?? null, $monthParam ?? null, $yearParam ?? null] : ['#'];
                                            $rejectedParams   = ($leaveId ?? false) ? [$leaveId, 'Reject',   $typeParam ?? null, $monthParam ?? null, $yearParam ?? null] : ['#'];
                                            $pendingParams    = ($leaveId ?? false) ? [$leaveId, 'Pending',  $typeParam ?? null, $monthParam ?? null, $yearParam ?? null] : ['#'];
                                            $approvedUrl      = ($empLeaveResolved && ($leaveId ?? false)) ? route($empLeaveResolved, $approvedParams) : '#';
                                            $rejectedUrl      = ($empLeaveResolved && ($leaveId ?? false)) ? route($empLeaveResolved, $rejectedParams) : '#';
                                            $pendingUrl       = ($empLeaveResolved && ($leaveId ?? false)) ? route($empLeaveResolved, $pendingParams)  : '#';
                                            $approvedGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'view_approved_leave_detail_unavailable') ?? 'Approved leave detail route is unavailable. Please contact technical support or your domain administrator.';
                                            $rejectedGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'view_rejected_leave_detail_unavailable') ?? 'Rejected leave detail route is unavailable. Please contact technical support or your domain administrator.';
                                            $pendingGuardMsg  = Utility::fetchLinkMessage($lang, VW::RPT, 'view_pending_leave_detail_unavailable')  ?? 'Pending leave detail route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="#" class="{{ VC::BT_SM_PM }}">
                                                    {{ $empId ? $user?->employeeIdFormat($empId) : __('Could not find employee ID') }}
                                                </a>
                                            </td>
                                            <td>{{ $empName }}</td>
                                            <td>
                                                <div class="m-view-btn badge bg-info p-2 px-3 rounded">
                                                    {{ $approved }}
                                                    <a href="{{ $approvedUrl }}"
                                                    class="text-white view-employee-leave"
                                                    data-status="approved"
                                                    data-url="{{ $approvedUrl }}"
                                                    data-ajax-popup="{{ $leaveId ? 'true' : 'false' }}"
                                                    data-title="{{ __('Approved Leave Detail') }}"
                                                    data-guard-msg="{{ $approvedGuardMsg }}"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $leaveId ? __('View') : __('Could not open approved leave details') }}"
                                                    data-original-title="{{ $leaveId ? __('View') : __('Could not open approved leave details') }}">
                                                        {{ $leaveId ? __('View') : __('Unavailable') }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="m-view-btn badge bg-danger p-2 px-3 rounded">
                                                    {{ $rejected }}
                                                    <a href="{{ $rejectedUrl }}"
                                                    class="text-white view-employee-leave"
                                                    data-status="rejected"
                                                    data-url="{{ $rejectedUrl }}"
                                                    data-ajax-popup="{{ $leaveId ? 'true' : 'false' }}"
                                                    data-title="{{ __('Rejected Leave Detail') }}"
                                                    data-guard-msg="{{ $rejectedGuardMsg }}"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $leaveId ? __('View') : __('Could not open rejected leave details') }}"
                                                    data-original-title="{{ $leaveId ? __('View') : __('Could not open rejected leave details') }}">
                                                        {{ $leaveId ? __('View') : __('Unavailable') }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="m-view-btn badge bg-warning p-2 px-3 rounded">
                                                    {{ $pending }}
                                                    <a href="{{ $pendingUrl }}"
                                                    class="text-white view-employee-leave"
                                                    data-status="pending"
                                                    data-url="{{ $pendingUrl }}"
                                                    data-ajax-popup="{{ $leaveId ? 'true' : 'false' }}"
                                                    data-title="{{ __('Pending Leave Detail') }}"
                                                    data-guard-msg="{{ $pendingGuardMsg }}"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $leaveId ? __('View') : __('Could not open pending leave details') }}"
                                                    data-original-title="{{ $leaveId ? __('View') : __('Could not open pending leave details') }}">
                                                        {{ $leaveId ? __('View') : __('Unavailable') }}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">{{ __('No leave records available for the selected filters') }}</td>
                                        </tr>
                                    @endforelse
                                    @push(StacksConstants::ADM_SCRP_PG)
                                        <script src="{{ asset('assets/js/routes/reports/leaves/view.js') }}" defer></script>
                                    @endpush
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

