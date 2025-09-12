@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route, Gate};

    $user = Auth::user();
    $hasUser = (bool) $user;
    $hasUserDateFormat = $hasUser && method_exists($user, 'dateFormat');
    $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Holiday') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Holiday') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    @can('create holiday')
        @php
            $calendarRouteName = VW::HLD.'.calendar';
            $calendarUrl = Route::has($calendarRouteName) ? route($calendarRouteName) : '#';
            $calendarMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'calendar_holiday_route_unavailable') : null) ?? __('Calendar view route is unavailable. Please contact technical support or your domain administrator.');

            $createRouteName = VW::HLD.'.create';
            $createUrl = Route::has($createRouteName) ? route($createRouteName) : '#';
            $createMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'create_holiday_route_unavailable') : null) ?? __('Create holiday route is unavailable. Please contact technical support or your domain administrator.');
        @endphp
        <div class="{{ VC::FEND }}">
            <a href="{{ $calendarUrl }}"
               class="{{ VC::BT_SM_PM }}"
               data-url="{{ $calendarUrl }}"
               data-sv-localized="true"
               data-guard-msg="{{ $calendarMsg }}"
               data-bs-toggle="tooltip"
               title="{{ __('Calendar View') }}">
                <i class="{{ VC::TI_CLD }}"></i>
            </a>
            <a href="{{ $createUrl }}"
               class="{{ VC::BT_SM_PM }}"
               data-url="{{ $createUrl }}"
               data-sv-localized="true"
               data-guard-msg="{{ $createMsg }}"
               data-size="lg"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Holiday') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        </div>
    @endcan
@endsection

@section(YW::ADM_CTT)
    @if(!$hasUser)
        <div class="alert alert-warning mb-0" role="alert">{{ __('Failed to load Holiday data for the current user.') }}</div>
    @else
        @can('create holiday')
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CS12 }}">
                    <div class="mt-2" id="multiCollapseExample1">
                        <div class="{{ VC::CD }}">
                            <div class="card-body">
                                {!! Form::open(['route' => [VW::HLD.'.calendar'], 'method' => 'get', 'id' => 'holiday_filter']) !!}
                                <div class="{{ VC::R_ALC_JCE }}">
                                    <div class="col-xl-10">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CL_POS3 }}"><div class="btn-box"></div></div>
                                            <div class="{{ VC::CL_POS3 }}"><div class="btn-box"></div></div>
                                            <div class="{{ VC::CL_POS3 }}">
                                                <div class="btn-box">
                                                    {!! Form::label('start_date', __('Start Date'), ['class' => 'form-label']) !!}
                                                    {!! Form::date('start_date', request('start_date'), ['class' => 'month-btn form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_POS3 }}">
                                                <div class="btn-box">
                                                    {!! Form::label('end_date', __('End Date'), ['class' => 'form-label']) !!}
                                                    {!! Form::date('end_date', request('end_date'), ['class' => 'month-btn form-control']) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                                <a href="#"
                                                   class="{{ VC::BT_SM_PM }}"
                                                   onclick="document.getElementById('holiday_filter').submit(); return false;"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Apply') }}">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                                </a>
                                                @php
                                                    $calUrl = Route::has(VW::HLD.'.calendar') ? route(VW::HLD.'.calendar') : '#';
                                                    $calGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'calendar_holiday_route_unavailable') : null) ?? __('Reset route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                <a href="{{ $calUrl }}"
                                                   class="{{ VC::BT_SM_DG }}"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Reset') }}"
                                                   data-url="{{ $calUrl }}"
                                                   data-guard-msg="{{ $calGuardMsg }}"
                                                   data-sv-localized="true">
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
        @endcan

        <div class="{{ VC::RW }} {{ VC::MT1 }}">
            <div class="col-md-12">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Occasion') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        @if(Gate::check('edit holiday') || Gate::check('delete holiday'))
                                            <th>{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="font-style">
                                    @forelse ($holidays as $holiday)
                                        @php
                                            $hid = isset($holiday->id) ? (string) $holiday->id : '';
                                            $occasionTxt = isset($holiday->occasion) && $holiday->occasion !== ''
                                                ? $holiday->occasion
                                                : __('Occasion not available or failed to be fetched.');
                                            $rawStart = $holiday->date ?? null;
                                            $rawEnd = $holiday->end_date ?? null;
                                            $startTxt = $rawStart
                                                ? ($hasUserDateFormat ? $user->dateFormat($rawStart) : __('Failed to format start date.'))
                                                : __('Start date not available or failed to be fetched.');
                                            $endTxt = $rawEnd
                                                ? ($hasUserDateFormat ? $user->dateFormat($rawEnd) : __('Failed to format end date.'))
                                                : __('End date not available or failed to be fetched.');
                                        @endphp
                                        <tr>
                                            <td>{{ $occasionTxt }}</td>
                                            <td>{{ $startTxt }}</td>
                                            <td>{{ $endTxt }}</td>
                                            @if(Gate::check('edit holiday') || Gate::check('delete holiday'))
                                                <td class="Action">
                                                    <span>
                                                        @can('edit holiday')
                                                            @php
                                                                $editRouteName = VW::HLD.'.edit';
                                                                $editUrl = (Route::has($editRouteName) && $hid !== '') ? route($editRouteName, $hid) : '#';
                                                                $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'edit_holiday_route_unavailable') : null) ?? __('Edit Holiday route is unavailable. Please contact technical support or your domain administrator.');
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN }} {{ VC::BG_P }} {{ VC::MS2 }}">
                                                                <a href="{{ $editUrl }}"
                                                                   class="{{ VC::BT_SM_CT }}"
                                                                   data-url="{{ $editUrl }}"
                                                                   data-ajax-popup="true"
                                                                   data-title="{{ __('Edit Holiday') }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Edit') }}"
                                                                   data-guard-msg="{{ $editGuardMsg }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete holiday')
                                                            @php
                                                                $destroyRouteName = VW::HLD.'.destroy';
                                                                $destroyUrl = (Route::has($destroyRouteName) && $hid !== '') ? route($destroyRouteName, $hid) : '#';
                                                                $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'destroy_holiday_route_unavailable') : null) ?? __('Delete Holiday route is unavailable. Please contact technical support or your domain administrator.');
                                                                $delFormId = 'delete-form-'.$hid;
                                                                $confirmTitle = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                                $confirmBody = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'url'    => $destroyUrl,
                                                                    'id'     => $delFormId,
                                                                    'data-url' => $destroyUrl,
                                                                    'data-guard-msg' => $destroyGuardMsg,
                                                                    'data-sv-localized' => 'true',
                                                                ]) !!}
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_CT_PR }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                                       data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ (Gate::check('edit holiday') || Gate::check('delete holiday')) ? 4 : 3 }}" class="text-center">
                                                {{ __('No holidays found or the holiday data failed to load.') }}
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
    @endif
@endsection
