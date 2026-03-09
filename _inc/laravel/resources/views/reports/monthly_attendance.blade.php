@php
    $data ??= [];
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('reports/monthly_attendance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Monthly Attendance')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Monthly Attendance')}}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/attendances/monthly/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/attendances/monthly/pdf.js') }}"></script>
@endpush
{{--        <a href="{{route(VW::RPT . '.attendance',[isset($_GET['month'])?$_GET['month']:date('Y-m'),isset($_GET['branch'])?$_GET['branch']:0,isset($_GET['department'])?$_GET['department']:0])}}" class="{{ VC::BT_SM_PM }}" onclick="saveAsPDF()"data-bs-toggle="tooltip" title="{{__('Download')}}" data-original-title="{{__('Download')}}">--}}
{{--            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_monthly_attendance_unavailable') ?? 'Download function for monthly attendance reports is unavailable. Please contact technical support or your domain administrator.';
@endphp
        <a href="#"
        id="download-monthly-attendance-link"
        class="{{ VC::BT_SM_PM }} download-monthly-attendance"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ base64_encode($downloadGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/attendances/monthly/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $monthlyAttBase             = VW::RPT.'.monthly.attendance';
                                $monthlyAttKebab            = Str::kebab($monthlyAttBase);
                                $monthlyAttResolved         = Route::has($monthlyAttBase) ? $monthlyAttBase : (Route::has($monthlyAttKebab) ? $monthlyAttKebab : null);
                                $monthlyAttUrl              = $monthlyAttResolved ? route($monthlyAttResolved) : '#';
                                $monthlyAttFormId           = 'report_monthly_attendance';
                                $monthlyApplyGuardMsg       = Utility::fetchLinkMessage($lang, VW::RPT, 'monthly_apply_attendance_route_unavailable') ?? 'Monthly attendance apply route is unavailable. Please contact technical support or your domain administrator.';
                                $monthlyResetGuardMsg       = Utility::fetchLinkMessage($lang, VW::RPT, 'monthly_reset_attendance_route_unavailable') ?? 'Monthly attendance reset route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('reports/monthly_attendance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $monthlyAttUrl,
                            'id'                => $monthlyAttFormId,
                            'data-url'          => $monthlyAttUrl,
                            'data-guard-msg'    => $monthlyApplyGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('month', __('Month'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class'=>'month-btn ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('branch', __('Branch'), ['class'=> VC::FM_LB]) }}
                                                <select class="{{ VC::FM_CT_SL }}" name="branch_id" id="branch_id" placeholder="{{ __('Select Branch') }}" required>
                                                    <option value="">{{ __('Select Branch') }}</option>
                                                    <option value="0">{{ __('All Branch') }}</option>
                                                    @foreach($branch as $br)
                                                        <option value="{{ data_get($br,'id') }}">{{ data_get($br,'name',__('No branch name available')) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box" id="department_div">
                                                {{ Form::label('department', __('Department'), ['class'=> VC::FM_LB]) }}
                                                <select class="{{ VC::FM_CT_SL }}" name="department_id[]" id="department_id" required="required" placeholder="{{ __('Select Department') }}">
                                                    <option value="">{{ __('Please select a branch first') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box" id="employee_div">
                                                {{ Form::label('employee', __('Employee'), ['class'=> VC::FM_LB]) }}
                                                <select class="{{ VC::FM_CT_SL }}" name="employee_id[]" id="employee_id" placeholder="{{ __('Select Employee') }}">
                                                    <option value="">{{ __('Please select a department first') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-monthly-attendance"
                                            data-form-id="{{ $monthlyAttFormId }}"
                                            data-guard-msg="{{ base64_encode($monthlyApplyGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $monthlyAttUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-monthly-attendance"
                                            data-url="{{ $monthlyAttUrl }}"
                                            data-guard-msg="{{ base64_encode($monthlyResetGuardMsg) }}"
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
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/reports/attendances/monthly/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/attendances/monthly/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        @php
            try {
                $branchLabel     = data_get($data,'branch',__('No branch selected'));
                $deptLabel       = data_get($data,'department',__('No department selected'));
                $monthLabel      = data_get($data,'curMonth',__('No month selected'));
                $totalPresent    = data_get($data,'totalPresent',__('No total present available'));
                $totalLeave      = data_get($data,'totalLeave',__('No total leave available'));
                $totalOvertime   = data_get($data,'totalOvertime',0);
                $totalEarlyLeave = data_get($data,'totalEarlyLeave',0);
                $totalLate       = data_get($data,'totalLate',0);
            } catch (\Throwable $e) {
                \Log::error('reports/monthly_attendance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <div class="{{ VC::RW }}">
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <input type="hidden" value="{{ $branchLabel .' '. __('Branch') .' '. $monthLabel .' '. __('Attendance Report of') .' '. $deptLabel .' '. __('Department') }}" id="filename">
                    <h6 class="{{ VC::MB0 }}">{{ __('Report') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Attendance Summary') }}</h7>
                </div>
            </div>
            @if($branchLabel!='All')
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h6 class="{{ VC::MB0 }}">{{ __('Branch') }} :</h6>
                        <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $branchLabel }}</h7>
                    </div>
                </div>
            @endif
            @if($deptLabel!='All')
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h6 class="{{ VC::MB0 }}">{{ __('Department') }} :</h6>
                        <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $deptLabel }}</h7>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Duration') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $monthLabel }}</h7>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::CXL3 }} {{ VC::CM6 }} {{ VC::CL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <div class="float-left">
                        <h6 class="{{ VC::MB0 }}">{{ __('Attendance') }}</h6>
                        <h7 class="{{ VC::TXSM }} {{ VC::MB0 }} float-start">{{ __('Total present') }}: {{ $totalPresent }}</h7>
                        <h7 class="{{ VC::TXSM }} {{ VC::MB0 }} float-end">{{ __('Total leave') }} : {{ $totalLeave }}</h7>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CXL3 }} {{ VC::CM6 }} {{ VC::CL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Overtime') }}</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Total overtime in hours') }} : {{ number_format($totalOvertime,2) }}</h7>
                </div>
            </div>
            <div class="{{ VC::CXL3 }} {{ VC::CM6 }} {{ VC::CL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Early leave') }}</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Total early leave in hours') }} : {{ number_format($totalEarlyLeave,2) }}</h7>
                </div>
            </div>
            <div class="{{ VC::CXL3 }} {{ VC::CM6 }} {{ VC::CL3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Employee late') }}</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Total late in hours') }} : {{ number_format($totalLate,2) }}</h7>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="col">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD_TB_BD }}">
                        <div class="{{ VC::TB_RSP }} {{ VC::PY4 }} attendance-table-responsive">
                            <table class="{{ VC::TB }}">
                                <thead>
                                <tr>
                                    <th class="active">{{ __('Name') }}</th>
                                    @forelse($dates as $date)
                                        <th>{{ $date }}</th>
                                    @empty
                                        <th>{{ __('No dates available') }}</th>
                                    @endforelse
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($employeesAttendance as $attendance)
                                    <tr>
                                        <td>{{ data_get($attendance,'name',__('No employee name available')) }}</td>
                                        @php
 $statuses = data_get($attendance,'status',[]);
@endphp
                                        @if(empty($statuses))
                                            <td colspan="{{ max(count($dates),1) }}">{{ __('No attendance status available') }}</td>
                                        @else
                                            @foreach($statuses as $status)
                                                <td>
                                                    @if($status=='P')
                                                        <i class="badge bg-success p-2 rounded">{{ __('P') }}</i>
                                                    @elseif($status=='A')
                                                        <i class="badge bg-danger p-2 rounded">{{ __('A') }}</i>
                                                    @else
                                                        <i class="badge bg-secondary p-2 rounded">{{ __('No status available') }}</i>
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ max(count($dates),1) + 1 }}" class="{{ VC::TXCT_MT }}">{{ __('No attendance records available for the selected filters') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
