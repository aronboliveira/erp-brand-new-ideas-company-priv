@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('attendances/bulk — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bulk Attendance')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/attendances/bulk/lang/toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/attendances/bulk/toggle.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Attendance')}}</li>
@endsection
{{--@section('action-btn')--}}
{{--    <div class="{{ VC::FEND }}">--}}
{{--        <a class="{{ VC::BT_SM_PM }}" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
{{--    </div>--}}
{{--@endsection--}}
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $bulkBase = VW::EMP_ATD.'.'.EAC::BK_ATD;
                                $bulkKebab = Str::kebab($bulkBase);
                                $bulkResolved = Route::has($bulkBase) ? $bulkBase : (Route::has($bulkKebab) ? $bulkKebab : null);
                                $bulkUrl = $bulkResolved ? route($bulkResolved) : '#';
                                $formId = 'bulkattendance_filter';
                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                $applyGuardMsg = Utility::fetchLinkMessage($langValue, VW::EMP_ATD, 'apply_bulk_attendance_route_unavailable') ?? 'Apply bulk attendance route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('attendances/bulk — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'method' => 'GET',
                            'url' => $bulkUrl,
                            'id' => $formId,
                            'data-url' => $bulkUrl,
                            'data-guard-msg' => $applyGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CLMS3 }}">
                                            <div class="btn-box"></div>
                                        </div>
                                        <div class="{{ VC::CLMS3 }}">
                                            {{ Form::label('date', __('Date'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::date('date', request('date',''), ['class'=>VC::FM_CT]) }}
                                        </div>
                                        <div class="{{ VC::CLMS3 }}">
                                            {{ Form::label('branch', __('Branch'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::select('branch', $branch, request('branch',''), ['class'=>VC::FM_CT.' select','required']) }}
                                        </div>
                                        <div class="{{ VC::CLMS3 }}">
                                            {{ Form::label('department', __('Department'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::select('department', $department, request('department',''), ['class'=>VC::FM_CT.' select','required']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::FEND }} {{ VC::MS2 }} {{ VC::MT4 }}">
                                    <a href="#"
                                    class="{{ VC::BT_SM_PM }} apply-bulkattendance"
                                    data-form-id="{{ $formId }}"
                                    data-guard-msg="{{ base64_encode($applyGuardMsg) }}"
                                    data-sv-localized="true"
                                    title="{{__('Apply')}}">
                                        <i class="{{ VC::TI_SRC }}"></i>
                                    </a>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/attendances/bulk/apply.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
   <div class="{{ VC::RW }}">
        <div class="{{ VC::CXL12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-header {{ VC::CD_MT }}">
                    @php
                        try {
                            $bulkPostBase = VW::EMP_ATD.'.'.EAC::BK_ATD;
                            $bulkPostKebab = Str::kebab($bulkPostBase);
                            $bulkPostResolved = Route::has($bulkPostBase) ? $bulkPostBase : (Route::has($bulkPostKebab) ? $bulkPostKebab : null);
                            $bulkPostUrl = $bulkPostResolved ? route($bulkPostResolved) : '#';
                            $bulkPostFormId = 'bulkattendance_post';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $submitGuardMsg = Utility::fetchLinkMessage($langValue, VW::EMP_ATD, 'submit_bulk_attendance_route_unavailable') ?? 'Submit bulk attendance route is unavailable. Please contact technical support or your domain administrator.';
                        } catch (\Throwable $e) {
                            \Log::error('attendances/bulk — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    {{ Form::open([
                        'method' => 'POST',
                        'url' => $bulkPostUrl,
                        'id' => $bulkPostFormId,
                        'data-url' => $bulkPostUrl,
                        'data-guard-msg' => $submitGuardMsg,
                        'data-sv-localized' => 'true',
                    ]) }}
                        <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB_AL }}" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th width="10%">{{ __('Employee Id') }}</th>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Department') }}</th>
                                        <th>
                                            <div class="{{ VC::FM_G }} my-auto">
                                                <div class="{{ VC::CST_CTL }}">
                                                    <input class="form-check-input" type="checkbox" name="present_all" id="present_all" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="{{ VC::CST_LB }}" for="present_all">{{ __('Attendance') }}</label>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!empty($employees) && ((is_array($employees) && count($employees) > 0) || ($employees instanceof Collection && $employees->isNotEmpty())))
                                        @foreach($employees as $employee)
                                            @php
                                                try {
                                                    $attendance = $employee->presentStatus($employee->id, request('date', date('Y-m-d')));
                                                    $empShowBase = ViewsConstants::EMP.'.show';
                                                    $empShowKebab = Str::kebab($empShowBase);
                                                    $empShowResolved = Route::has($empShowBase) ? $empShowBase : (Route::has($empShowKebab) ? $empShowKebab : null);
                                                    $empIdVal = isset($employee->id) ? (int)$employee->id : 0;
                                                    $empEncryptedId = $empIdVal ? encrypt($empIdVal) : null;
                                                    $empShowUrl = ($empShowResolved && $empEncryptedId) ? route($empShowResolved, $empEncryptedId) : '#';
                                                    $langLocal = $langValue;
                                                    $empShowGuardMsg = Utility::fetchLinkMessage($langLocal, ViewsConstants::EMP, 'show_employee_route_unavailable') ?? 'Show employee route is unavailable. Please contact technical support or your domain administrator.';
                                                    $empAnchorId = 'employee-show-link-'.$empIdVal;
                                                } catch (\Throwable $e) {
                                                    \Log::error('attendances/bulk — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="employee_id[]" value="{{ $employee->id }}">
                                                    <a id="{{ $empAnchorId }}"
                                                    href="{{ $empShowUrl }}"
                                                    class="{{ VC::BT_OUTPM }}"
                                                    data-url="{{ $empShowUrl }}"
                                                    data-guard-msg="{{ base64_encode($empShowGuardMsg) }}"
                                                    data-sv-localized="true">
                                                        {{ !empty($employee->employee_id) ? $user->employeeIdFormat($employee->employee_id) : __('Failed to get employee id') }}
                                                    </a>
                                                </td>
                                                <td>{{ $employee->name ?? __('Failed to get employee name') }}</td>
                                                <td>{{ $employee->branch->name ?? __('Failed to get branch name') }}</td>
                                                <td>{{ $employee->department->name ?? __('Failed to get department name') }}</td>
                                                <td>
                                                    <div class="{{ VC::RW }}">
                                                        <div class="{{ VC::CM3 }}">
                                                            <div class="{{ VC::CST_CTL }} {{ VC::CST_CB }}">
                                                                <input type="checkbox"
                                                                    class="form-check-input present"
                                                                    name="present-{{ $employee->id }}"
                                                                    id="present{{ $employee->id }}"
                                                                    {{ optional($attendance)->status=='Present'?'checked':'' }}>
                                                                <label class="{{ VC::CST_LB }}" for="present{{ $employee->id }}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="{{ VC::CM8 }} {{ $attendance?'':'d-none' }}">
                                                            <div class="{{ VC::RW }}">
                                                                <label class="{{ VC::CM3 }} {{ VC::FM_LB }}">{{ __('In') }}</label>
                                                                <div class="{{ VC::CM4 }}">
                                                                    <input type="time"
                                                                        class="{{ VC::FM_CT }}"
                                                                        name="in-{{ $employee->id }}"
                                                                        value="{{ (($attendance->clock_in ?? '00:00:00') !== '00:00:00') ? $attendance->clock_in : (Utility::getValByName('company_start_time') ?? '00:00:00') }}">
                                                                </div>
                                                                <label class="{{ VC::CM2 }} {{ VC::FM_LB }}">{{ __('Out') }}</label>
                                                                <div class="{{ VC::CM4 }}">
                                                                    <input type="time"
                                                                        class="{{ VC::FM_CT }}"
                                                                        name="out-{{ $employee->id }}"
                                                                        value="{{ (($attendance->clock_out ?? '00:00:00') !== '00:00:00') ? $attendance->clock_out : (Utility::getValByName('company_end_time') ?? '00:00:00') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const el = document.getElementById('{{ $empAnchorId }}');
                                                            if (!el) { return; }
                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                            el.setAttribute('data-listener-active', 'true');
                                                            el.addEventListener('click', (e) => {
                                                                try {
                                                                    const href = el.getAttribute('href') ?? '#';
                                                                    const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                    if (url !== '#' && href !== '#') { return; }
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
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5">
                                                <div class="{{ VC::TXCT }}">
                                                    {{ __('No employees found for the selected criteria.') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="{{ VC::FEND }} pt-4">
                            <input type="hidden" name="date" value="{{ request('date', date('Y-m-d')) }}">
                            <input type="hidden" name="branch" value="{{ request('branch','') }}">
                            <input type="hidden" name="department" value="{{ request('department','') }}">
                            {{ Form::submit(__('Update'), ['class'=>VC::BT_SM_PM]) }}
                        </div>
                    {{ Form::close() }}
                    @push(StacksConstants::ADM_SCR_PG)
                        <script src="{{ asset('assets/js/routes/attendances/bulk/submit.js') }}" defer></script>
                    @endpush
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    {{--    <script>--}}
    {{--        $(document).ready(function () {--}}
    {{--            $('.daterangepicker').daterangepicker({--}}
    {{--                format: 'yyyy-mm-dd',--}}
    {{--                locale: {format: 'YYYY-MM-DD'},--}}
    {{--            });--}}
    {{--        });--}}
    {{--    </script>--}}
@endpush
