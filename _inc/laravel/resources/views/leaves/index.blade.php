@php
    use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants, ViewsConstants as VW, ViewClassNamesConstants as VC, YieldingConstants as YD};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Crypt, Route, URL};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $dashBase       = 'dashboard';
    $dashKebab      = Str::kebab($dashBase);
    $dashResolved   = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
    $dashUrl        = $dashResolved ? route($dashResolved) : '#';
    $dashLinkId     = 'dashboard-breadcrumb-link';
    $dashGuardMsg   = Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
    $createBase     = VW::LV . '.create';
    $createKebab    = Str::kebab($createBase);
    $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
    $createUrl      = $createResolved ? route($createResolved) : '#';
    $createLinkId   = 'leave-create-link';
    $createGuardMsg = Utility::fetchLinkMessage($lang, VW::LV, 'create_leave_unavailable') ?? 'Create leave route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Leave') }}
@endsection

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           id="{{ $dashLinkId }}"
           data-url="{{ $dashUrl }}"
           data-guard-msg="{{ $dashGuardMsg }}">
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Manage Leave') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create leave')
            <a href="{{ $createUrl }}"
               id="{{ $createLinkId }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-title="{{ __('Create Leave') }}"
               data-sv-localized="true"
               data-guard-msg="{{ $createGuardMsg }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        @php
                            $isEmployee = strtolower($user?->type ?? '') === 'employee';
                            $leavesSafe = (isset($leaves) && (is_array($leaves) || $leaves instanceof Collection)) ? $leaves : [];
                            $fmtDate = function($v,$fb) use($user){ return ($v && $user && method_exists($user,'dateFormat')) ? ($user->dateFormat($v) ?? $fb) : $fb; };
                        @endphp
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    @if(!$isEmployee)
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Applied On') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Total Days') }}</th>
                                    <th>{{ __('Leave Reason') }}</th>
                                    <th>{{ __('status') }}</th>
                                    @can('edit leave')
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leavesSafe as $leave)
                                    @php
                                        $lid = data_get($leave,'id');
                                        $empName = data_get($leave,'employees.name') ?? __('No employee name available');
                                        $typeTitle = data_get($leave,'leaveType.title') ?? __('No leave type available');
                                        $appliedOn = $fmtDate(data_get($leave,'applied_on'), __('No applied date available'));
                                        $startDate = $fmtDate(data_get($leave,'start_date'), __('No start date available'));
                                        $endDate = $fmtDate(data_get($leave,'end_date'), __('No end date available'));
                                        $totalDays = data_get($leave,'total_leave_days') ?? '-';
                                        $reason = data_get($leave,'leave_reason') ?? __('No leave reason available');
                                        $stRaw = strtolower((string)(data_get($leave,'status') ?? ''));
                                        $stText = data_get($leave,'status') ?? __('Unknown status');
                                        $stClass = match($stRaw){ 'pending'=>'bg-warning','approved'=>'bg-success','reject','rejected'=>'bg-danger', default=>'bg-secondary' };
                                    @endphp
                                    <tr>
                                        @if(!$isEmployee)
                                            <td>{{ $empName }}</td>
                                        @endif
                                        <td>{{ $typeTitle }}</td>
                                        <td>{{ $appliedOn }}</td>
                                        <td>{{ $startDate }}</td>
                                        <td>{{ $endDate }}</td>
                                        <td>{{ $totalDays }}</td>
                                        <td>{{ $reason }}</td>
                                        <td><div class="status_badge badge {{ $stClass }} p-2 px-3 rounded">{{ $stText }}</div></td>
                                        @can('edit leave')
                                            <td>
                                                @if(!$isEmployee)
                                                    @if($stRaw === 'pending')
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a href="{{ route(VW::LV.'.edit', [$lid]) }}" class="{{ VC::BT_SM_CT }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Leave') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="{{ VC::BT_SM_MX3 }} {{ VC::DFL_IL_VC }}">
                                                        <div class="action-btn bg-warning ms-2">
                                                            <a href="{{ route(VW::LV.'.action', [$lid]) }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Leave Action') }}" class="{{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{ __('Leave Action') }}"><i class="{{ VC::TI_CRT_WT }}"></i></a>
                                                        </div>
                                                        @can('edit leave')
                                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                <a href="{{ route(VW::LV.'.edit', [$lid]) }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Leave') }}" class="{{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                            </div>
                                                        @endcan
                                                    </div>
                                                @endif
                                                @can('delete leave')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['leave.destroy', $lid], 'id' => 'delete-form-'.$lid]) !!}
                                                            <a href="#" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __(Utility::fetchLinkMessage($lang ?? app()->getLocale(), 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang ?? app()->getLocale(), 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$lid}}').submit();"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCRP_PG)
    <script defer src="{{ asset('assets/js/routes/generics/dashboard.js') }}"></script>
    @can('create leave')
        <script defer src="{{ asset('assets/js/routes/leaves/create.js') }}"></script>
    @endcan
    <script>
        $(document).on('change', '#employee_id', function () {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{route('leave.jsoncount')}}',
                type: 'POST',
                data: {
                    "employee_id": employee_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {

                    $('#leave_type_id').empty();
                    $('#leave_type_id').append('<option value="">{{__('Select Leave Type')}}</option>');

                    $.each(data, function (key, value) {

                        if (value.total_leave >= value.days) {
                            $('#leave_type_id').append('<option value="' + value.id + '" disabled>' + value.title + '&nbsp(' + value.total_leave + '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' + value.title + '&nbsp(' + value.total_leave + '/' + value.days + ')</option>');
                        }
                    });

                }
            });
        });

    </script>
@endpush
