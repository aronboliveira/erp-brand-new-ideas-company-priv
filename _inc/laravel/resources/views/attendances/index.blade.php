@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Gate, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Attendance List')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Attendance')}}</li>
@endsection
{{--@section('action-btn')--}}
{{--    <div class="float-end">--}}
{{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
{{--    </div>--}}
{{--@endsection--}}
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            @if (session('status'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {!! session('status') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    @php
                        $indexRoute    = Route::has(VW::EMP_ATD.'.index') ? [VW::EMP_ATD.'.index'] : ['#'];
                        $indexUrl      = Route::has(VW::EMP_ATD.'.index') ? route(VW::EMP_ATD.'.index') : '#';
                        $formId        = 'employeeAttendance_filter';
                        $resetClass    = 'reset-employee-attendance-link';
                        $importRoute   = Route::has(VW::ATD.'.file.import') ? route(VW::ATD.'.file.import') : '#';
                        $importClass   = 'import-attendance-link';
                    @endphp
                    <div class="card-body">
                        {!! Form::open([
                            'route'          => $indexRoute,
                            'method'         => 'get',
                            'id'             => $formId,
                            'data-url' => $indexUrl,
                            'data-sv-localized' => 'true',
                            'data-guard-msg' => Utility::fetchLinkMessage($lang,VW::EMP_ATD,'index_attendance_unavailable') ?? 'Employee attendance index route is unavailable. Please contact technical support or your domain administrator.'
                        ]) !!}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label">{{ __('Type') }}</label><br>
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
                                        @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('branch', __('Branch'), ['class'=>'form-label']) }}
                                                    {{ Form::select('branch', $branch, isset($_GET['branch'])?$_GET['branch']:'', ['class'=>'form-control select']) }}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('department', __('Department'), ['class'=>'form-label']) }}
                                                    {{ Form::select('department', $department, isset($_GET['department'])?$_GET['department']:'', ['class'=>'form-control select']) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto mt-4">
                                    <div class="row">
                                        <div class="col-auto">
                                            <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('{{ $formId }}').submit();return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $indexUrl }}" id="{{ $resetClass }}" class="{{ VC::BT_SM_DG }} {{ $resetClass }}" data-url="{{ $indexUrl }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ Utility::fetchLinkMessage($lang,VW::EMP_ATD,'index_attendance_unavailable') ?? 'Employee attendance index route is unavailable. Please contact technical support or your domain administrator.' }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}" data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                            <a href="#" id="{{ $importClass }}" class="{{ VC::BT_SM_PM }} {{ $importClass }}" data-url="{{ $importRoute }}" 
                                            data-sv-localized="true"
                                            data-guard-msg="{{ Utility::fetchLinkMessage($lang,VW::ATD,'csv_attendance_unavailable') ?? 'Import employee CSV file route is unavailable. Please contact technical support or your domain administrator.' }}" data-size="md" data-ajax-popup="true" data-title="{{ __('Import employee CSV file') }}" data-url="{{ $importRoute }}" data-bs-toggle="tooltip" title="{{ __('Import') }}" data-original-title="{{ __('Import') }}">
                                                <i class="{{ VC::TI_IMP }}"></i>
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                @if(strtolower($user[UsersConstants::COL_TP]) !== 'employee')
                                    <th>{{__('Employee')}}</th>
                                @endif
                                <th>{{__('Date')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Clock In')}}</th>
                                <th>{{__('Clock Out')}}</th>
                                <th>{{__('Late')}}</th>
                                <th>{{__('Early Leaving')}}</th>
                                <th>{{__('Overtime')}}</th>
                                @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($EmployeeAttendance as $attendance)
                                <tr>
                                    <td>{{!empty($attendance->employee)?$attendance->employee->name:'' }}</td>
                                    <td>{{ $user?->dateFormat($attendance->date) }}</td>
                                    <td>{{ $attendance->status }}</td>
                                    <td>{{ ($attendance->clock_in !='00:00:00') ?$user?->timeFormat( $attendance->clock_in):'00:00' }} </td>
                                    <td>{{ ($attendance->clock_out !='00:00:00') ?$user?->timeFormat( $attendance->clock_out):'00:00' }}</td>
                                    <td>{{ $attendance->late }}</td>
                                    <td>{{ $attendance->early_leaving }}</td>
                                    <td>{{ $attendance->overtime }}</td>
                                    @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                        <td class="">
                                            @can('edit attendance')
                                                @php
                                                    $editRouteName = VW::EMP_ATD . '.edit';
                                                    $editUrl = Route::has($editRouteName) ? route($editRouteName, $attendance->id) : '#';
                                                    $editClass = 'edit-attendance-link-' . $attendance->id;
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a
                                                        href="{{ $editUrl }}"
                                                        id="{{ $editClass }}"
                                                        class="{{ VC::BT_SM_CT }} {{ $editClass }}"
                                                        data-url="{{ $editUrl }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ Utility::fetchLinkMessage($lang, VW::EMP_ATD, 'edit_attendance_unavailable') ?? 'Edit attendance route is unavailable. Please contact technical support or your domain administrator.' }}"
                                                        data-ajax-popup="true"
                                                        data-size="lg"
                                                        data-title="{{ __('Edit Attendance') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-original-title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete attendance')
                                                @php
                                                    $deleteRouteName = VW::EMP_ATD . '.destroy';
                                                    $deleteRoute = Route::has($deleteRouteName) ? [$deleteRouteName, $attendance->id] : ['#'];
                                                    $deleteUrl = Route::has($deleteRouteName) ? route($deleteRouteName, $attendance->id) : '#';
                                                    $deleteClass = 'delete-attendance-link-' . $attendance->id;
                                                    $formId = 'delete-form-' . $attendance->id;
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open(['method'=>'DELETE','route'=>$deleteRoute,'id'=>$formId]) !!}
                                                        <a
                                                            href="{{ $deleteUrl }}"
                                                            id="{{ $deleteClass }}"
                                                            class="{{ VC::TRS_PARA }} {{ $deleteClass }}"
                                                            data-url="{{ $deleteUrl }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ Utility::fetchLinkMessage($lang, VW::EMP_ATD, 'delete_attendance_unavailable') ?? 'Delete attendance route is unavailable. Please contact technical support or your domain administrator.' }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>         
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/attendances/lang/date.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/attendances/date.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/attendances/page.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/attendances/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/attendances/import.js') }}"></script>
    @can('edit attendance')
        <script defer src="{{ asset('assets/js/routes/attendances/edit.js') }}"></script>
    @endcan
    @can('delete attendance')
        <script defer src="{{ asset('assets/js/routes/attendances/delete.js') }}"></script>
    @endcan
@endpush
