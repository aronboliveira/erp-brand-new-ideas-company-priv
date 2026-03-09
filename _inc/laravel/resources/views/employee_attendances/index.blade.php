@php
    use App\Config\Constants\{ViewsConstants as VW, UsersConstants, ExtendingLayoutsConstants, YieldingConstants, StacksConstants};
    use App\Models\Utility;
    try {
        $user = Auth::user();
        $langValue = $lang ?? (class_exists(Utility::class) ? Utility::fetchUserLang(user: $user) : null);
        $dashboardUrl = Route::has('dashboard') ? route('dashboard') : '#';
        $list = $attendances ?? collect();
        $branchList = $branches ?? collect();
        $departmentList = $departments ?? collect();
    } catch (\Throwable $e) {
        \Log::error('employee_attendances/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $list ??= collect();
    $branchList ??= collect();
    $departmentList ??= collect();
    $dashboardUrl ??= '#';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Employee Attendance') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashboardUrl }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Employee Attendance') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CS12 }}">
            @if (session('status'))
                <div class="{{ VC::ALT_DNG }} alert-dismissible fade show" role="alert">
                    {!! session('status') !!}
                    <button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="{{ VC::MT2 }}">
                <div class="card">
                    @php
                        try {
                            $indexRoute = Route::has(VW::EMP_ATD . '.index') ? [VW::EMP_ATD . '.index'] : ['#'];
                            $indexUrl   = Route::has(VW::EMP_ATD . '.index') ? route(VW::EMP_ATD . '.index') : '#';
                            $formId     = 'employeeAttendance_filter';
                        } catch (\Throwable $e) {
                            \Log::error('employee_attendances/index — ' . get_class($e) . ': ' . $e->getMessage());
                        }
                        $indexRoute ??= ['#'];
                        $indexUrl ??= '#';
                        $formId ??= 'employeeAttendance_filter';
                    @endphp
                    <div class="{{ VC::CD_BD }}">
                        {!! Form::open(['route' => $indexRoute, 'method' => 'get', 'id' => $formId]) !!}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="row">
                                        <div class="{{ VC::C3 }}">
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
                                        <div class="{{ VC::CL_XL3 }} month">
                                            <div class="btn-box">
                                                {{ Form::label('month', __('Month'), ['class'=>'form-label']) }}
                                                {{ Form::month('month', isset($_GET['month'])?$_GET['month']:date('Y-m'), ['class'=>'month-btn form-control month-btn']) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }} date">
                                            <div class="btn-box">
                                                {{ Form::label('date', __('Date'), ['class'=>'form-label']) }}
                                                {{ Form::date('date', isset($_GET['date'])?$_GET['date']:'', ['class'=>'form-control month-btn']) }}
                                            </div>
                                        </div>
                                        @if(isset($user) && strtolower($user[UsersConstants::COL_TP] ?? '') !== 'employee')
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('branch', __('Branch'), ['class'=>'form-label']) }}
                                                    {{ Form::select('branch', $branchList, isset($_GET['branch'])?$_GET['branch']:'', ['class'=>'form-control select']) }}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('department', __('Department'), ['class'=>'form-label']) }}
                                                    {{ Form::select('department', $departmentList, isset($_GET['department'])?$_GET['department']:'', ['class'=>'form-control select']) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="row">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('{{ $formId }}').submit();return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $indexUrl }}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="{{ VC::CM12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    @if(isset($user) && strtolower($user[UsersConstants::COL_TP] ?? '') !== 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Clock In') }}</th>
                                    <th>{{ __('Clock Out') }}</th>
                                    <th>{{ __('Late') }}</th>
                                    <th>{{ __('Early Leaving') }}</th>
                                    <th>{{ __('Overtime') }}</th>
                                    @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $attendance)
                                    <tr>
                                        @if(isset($user) && strtolower($user[UsersConstants::COL_TP] ?? '') !== 'employee')
                                            <td>{{ !empty($attendance->employee) ? ($attendance->employee->name ?? '') : '' }}</td>
                                        @endif
                                        <td>{{ $user?->dateFormat($attendance->date ?? null) ?? '-' }}</td>
                                        <td>{{ $attendance->status ?? '-' }}</td>
                                        <td>{{ (($attendance->clock_in ?? '00:00:00') !== '00:00:00') ? ($user?->timeFormat($attendance->clock_in) ?? '00:00') : '00:00' }}</td>
                                        <td>{{ (($attendance->clock_out ?? '00:00:00') !== '00:00:00') ? ($user?->timeFormat($attendance->clock_out) ?? '00:00') : '00:00' }}</td>
                                        <td>{{ $attendance->late ?? '-' }}</td>
                                        <td>{{ $attendance->early_leaving ?? '-' }}</td>
                                        <td>{{ $attendance->overtime ?? '-' }}</td>
                                        @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                            <td>
                                                @can('edit attendance')
                                                    @php
                                                        try {
                                                            $editRoute = VW::EMP_ATD . '.edit';
                                                            $editUrl = Route::has($editRoute) ? route($editRoute, $attendance->id) : '#';
                                                        } catch (\Throwable $e) { $editUrl = '#'; }
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="{{ $editUrl }}"
                                                           class="{{ VC::BT_SM_CT }}"
                                                           data-url="{{ $editUrl }}"
                                                           data-ajax-popup="true"
                                                           data-size="lg"
                                                           data-title="{{ __('Edit Attendance') }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete attendance')
                                                    @php
                                                        try {
                                                            $destroyRoute = VW::EMP_ATD . '.destroy';
                                                            $destroyUrl = Route::has($destroyRoute) ? route($destroyRoute, $attendance->id) : '#';
                                                            $delFormId = 'emp-atd-delete-form-' . $attendance->id;
                                                        } catch (\Throwable $e) { $destroyUrl = '#'; $delFormId = 'emp-atd-delete-form-x'; }
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['method'=>'DELETE', 'url'=>$destroyUrl, 'id'=>$delFormId]) !!}
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="{{ VC::TXCT_MT }} {{ VC::PY4 }}">
                                            {{ __('No attendance records found.') }}
                                        </td>
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
