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
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;

    $user = Auth::user();
    $hasUser = (bool) $user;
    $hasUserDateFormat = $hasUser && method_exists($user, 'dateFormat');
    $hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
    $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang = $hasFetchUserLang ? Utility::fetchUserLang(user:$user) : (string) app()->getLocale();
    $settings = Utility::settings();
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Holiday') }}
@endsection

@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/holidays/index.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Holiday') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    @can('create holiday')
        <div class="{{ VC::FEND }}">
            @php
                $indexBase     = VW::HLD . '.index';
                $indexKebab    = Str::kebab($indexBase);
                $indexResolved = Route::has($indexBase) ? $indexBase : (($indexKebab !== $indexBase && Route::has($indexKebab)) ? $indexKebab : null);
                $indexUrl      = $indexResolved ? route($indexResolved) : '#';
                $indexGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'index_holiday_route_unavailable') : null) ?? __('List view route is unavailable. Please contact technical support or your domain administrator.');

                $createBase     = VW::HLD . '.create';
                $createKebab    = Str::kebab($createBase);
                $createResolved = Route::has($createBase) ? $createBase : (($createKebab !== $createBase && Route::has($createKebab)) ? $createKebab : null);
                $createUrl      = $createResolved ? route($createResolved) : '#';
                $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'holiday_create_route_unavailable') : 'Create Holiday route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Holiday route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a href="#"
               class="{{ VC::BT_SM_PM }}"
               data-bs-toggle="tooltip"
               title="{{ __('List View') }}"
               data-url="{{ $indexUrl }}"
               data-guard-msg="{{ $indexGuardMsg }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_LT }}"></i>
            </a>
            <a href="#"
               data-size="lg"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Holiday') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ $createGuardMsg }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        </div>
    @endcan
@endsection

@section(YW::ADM_CTT)
    @if(!$hasUser)
        <div class="alert alert-warning mb-0" role="alert">{{ __('Failed to load Holidays for the current user.') }}</div>
    @else
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {!! Form::open(['route' => [VW::HLD . '.calendar'], 'method' => 'get', 'id' => 'holiday_filter']) !!}
                                <div class="row align-items-center justify-content-end">
                                    <div class="col-xl-10">
                                        <div class="row">
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box"></div>
                                            </div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box"></div>
                                            </div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box">
                                                    {!! Form::label('start_date', __('Start Date'), ['class' => 'form-label']) !!}
                                                    {!! Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'month-btn form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box">
                                                    {!! Form::label('end_date', __('End Date'), ['class' => 'form-label']) !!}
                                                    {!! Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'month-btn form-control']) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="row">
                                            <div class="col-auto mt-4">
                                                <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('holiday_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                                </a>
                                                @php
                                                    $calBase     = VW::HLD . '.calendar';
                                                    $calKebab    = Str::kebab($calBase);
                                                    $calResolved = Route::has($calBase) ? $calBase : (($calKebab !== $calBase && Route::has($calKebab)) ? $calKebab : null);
                                                    $calUrl      = $calResolved ? route($calResolved) : '#';
                                                    $calGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'calendar_holiday_route_unavailable') : null) ?? __('Reset route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                <a href="#"
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

        <div class="row">
            <div class="col-lg-8">
                <div class="{{ VC::CD }}">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-6">
                                <h5>{{ __('Calendar') }}</h5>
                            </div>
                            <div class="col-lg-6">
                                @if (!empty($settings) && !empty($settings['google_calendar_enable']) && $settings['google_calendar_enable'] === 'on')
                                    <select class="form-control" name="calendar_type" id="calendar_type" style="float: right;width: 150px;" onchange="get_data()">
                                        <option value="google_calendar">{{ __('Google calendar') }}</option>
                                        <option value="local_calendar" selected="true">{{ __('Local calendar') }}</option>
                                    </select>
                                @endif
                                <input type="hidden" id="holiday_calendar" value="{{ url('/') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar" class="calendar"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <h4 class="{{ VC::MB4 }}">{{ __('Holiday List') }}</h4>
                        <ul class="{{ VC::LG_FLSH_W }}">
                            <li class="{{ VC::LGI }} {{ VC::CD }} {{ VC::MB3 }}">
                                <div class="row {{ VC::ALC }} {{ VC::JCB }}">
                                    <div class="{{ VC::ALC }}">
                                        @if(!$holidays->isEmpty())
                                            @foreach ($holidays as $holiday)
                                                @php
                                                    $hid       = isset($holiday->id) ? (string) $holiday->id : '';
                                                    $titleTxt  = isset($holiday->occasion) && $holiday->occasion !== '' ? $holiday->occasion : __('Untitled Occasion');
                                                    $startDate = $hasUserDateFormat ? $user->dateFormat($holiday->date ?? null) : ($holiday->date ?? __('No start date'));
                                                    $endDate   = $hasUserDateFormat ? $user->dateFormat($holiday->end_date ?? null) : ($holiday->end_date ?? __('No end date'));
                                                @endphp
                                                <div class="{{ VC::CD }} {{ VC::MB3 }} {{ VC::SNN }} {{ VC::BD }}">
                                                    <div class="{{ VC::PX3 }}">
                                                        <div class="row {{ VC::ALC }}">
                                                            <div class="col ml-n2">
                                                                <h5 class="text-sm {{ VC::MB0 }}"></h5>
                                                                <p class="card-text small text-primary">{{ $titleTxt }}</p>
                                                                <p class="card-text small text-dark">
                                                                    {{ __('Start Date :') }} {{ $startDate }}<br>
                                                                    {{ __('End Date :') }} {{ $endDate }}
                                                                </p>
                                                            </div>
                                                            <div class="col-auto text-right {{ VC::DFL }}">
                                                                @can('edit holiday')
                                                                    @php
                                                                        $editBase     = VW::HLD . '.edit';
                                                                        $editKebab    = Str::kebab($editBase);
                                                                        $editResolved = Route::has($editBase) ? $editBase : (($editKebab !== $editBase && Route::has($editKebab)) ? $editKebab : null);
                                                                        $editUrl      = ($editResolved && $hid !== '') ? route($editResolved, $hid) : '#';
                                                                        $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'edit_holiday_route_unavailable') : 'Edit Holiday route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Holiday route is unavailable. Please contact technical support or your domain administrator.');
                                                                    @endphp
                                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                        <a href="#"
                                                                           data-url="{{ $editUrl }}"
                                                                           data-title="{{ __('Edit Holiday') }}"
                                                                           data-ajax-popup="true"
                                                                           class="{{ VC::BT_SM_CT }}"
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
                                                                        $destroyBase     = VW::HLD . '.destroy';
                                                                        $destroyKebab    = Str::kebab($destroyBase);
                                                                        $destroyResolved = Route::has($destroyBase) ? $destroyBase : (($destroyKebab !== $destroyBase && Route::has($destroyKebab)) ? $destroyKebab : null);
                                                                        $destroyUrl      = ($destroyResolved && $hid !== '') ? route($destroyResolved, $hid) : '#';
                                                                        $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::HLD, 'destroy_holiday_route_unavailable') : 'Delete Holiday route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Holiday route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $deleteFormId    = 'holiday-delete-form-' . ($hid === '' ? 'x' : $hid);
                                                                        $confirmTitle    = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                                        $confirmBody     = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                                    @endphp
                                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                        {!! Form::open([
                                                                            'method'            => 'DELETE',
                                                                            'url'               => $destroyUrl,
                                                                            'id'                => $deleteFormId,
                                                                            'data-url'          => $destroyUrl,
                                                                            'data-guard-msg'    => $destroyGuardMsg,
                                                                            'data-sv-localized' => 'true',
                                                                        ]) !!}
                                                                            <a href="#"
                                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                                               data-bs-toggle="tooltip"
                                                                               title="{{ __('Delete') }}"
                                                                               data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                                               data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center">{{ __('No holidays found!') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
