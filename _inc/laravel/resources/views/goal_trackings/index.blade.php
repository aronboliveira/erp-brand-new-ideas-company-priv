@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $hasUser = (bool) $user;
    $hasUserDateFormat = $hasUser && method_exists($user, 'dateFormat');
    $hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
    $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
    $hasGetProgressColor = is_callable([Utility::class, 'getProgressColor']);
    $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : (string) app()->getLocale();
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Goal Tracking') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Goal Tracking') }}</li>
@endsection

@push(ST::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/goals/trackings/toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/goals/trackings/index.js') }}"></script>
@endpush

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create goal tracking')
            @php
                $createBase     = VW::GL_TRC . '.create';
                $createKebab    = Str::kebab($createBase);
                $createResolved = Route::has($createBase) ? $createBase : (($createKebab !== $createBase && Route::has($createKebab)) ? $createKebab : null);
                $createUrl      = $createResolved ? route($createResolved) : '#';
                $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL_TRC, 'create_goal_tracking_route_unavailable') : 'Create Goal Tracking route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Goal Tracking route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a href="#"
               data-size="lg"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-title="{{ __('Create New Goal Tracking') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ $createGuardMsg }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    @if(!$hasUser)
        <div class="alert alert-warning mb-0" role="alert">{{ __('Failed to load Goal Tracking for the current user.') }}</div>
    @else
        @php
            $list = $goalTrackings ?? [];
            $hasList = ($list instanceof Collection) ? $list->isNotEmpty() : (is_array($list) && count($list) > 0);
            $hasActions = $user->can('edit goal tracking') || $user->can('delete goal tracking');
        @endphp
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                <tr>
                                    <th>{{ __('Goal Type') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Target Achievement') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Rating') }}</th>
                                    <th width="20%">{{ __('Progress') }}</th>
                                    @if($hasActions)
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="font-style">
                                @if($hasList)
                                    @foreach ($list as $goalTracking)
                                        @php
                                            $gtId     = (string) ($goalTracking->id ?? '');
                                            $gtType   = data_get($goalTracking, 'goalType.name') ?: __('No goal type available');
                                            $subject  = $goalTracking->subject ?? __('No subject available');
                                            $branch   = data_get($goalTracking, 'branches.name') ?: __('No branch available');
                                            $target   = $goalTracking->target_achievement ?? 0;
                                            $startAt  = $goalTracking->start_date ?? null;
                                            $endAt    = $goalTracking->end_date ?? null;
                                            $rating   = (int) ($goalTracking->rating ?? 0);
                                            $progress = (int) ($goalTracking->progress ?? 0);
                                            $startFmt = $hasUserDateFormat ? $user->dateFormat($startAt) : ($startAt ?? __('No start date available'));
                                            $endFmt   = $hasUserDateFormat ? $user->dateFormat($endAt)   : ($endAt ?? __('No end date available'));
                                            $progClr  = $hasGetProgressColor ? Utility::getProgressColor($progress) : 'primary';
                                        @endphp
                                        <tr>
                                            <td>{{ $gtType }}</td>
                                            <td>{{ $subject }}</td>
                                            <td>{{ $branch }}</td>
                                            <td>{{ $target }}</td>
                                            <td>{{ $startFmt }}</td>
                                            <td>{{ $endFmt }}</td>
                                            <td>
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($rating < $i)
                                                        <i class="fas fa-star"></i>
                                                    @else
                                                        <i class="text-warning fas fa-star"></i>
                                                    @endif
                                                @endfor
                                            </td>
                                            <td>
                                                <div class="progress-wrapper">
                                                    <span class="progress-percentage"><small class="font-weight-bold"></small>{{ $progress }}%</span>
                                                    <div class="{{ VC::PG_XS }} mt-2 w-100">
                                                        <div class="progress-bar bg-{{ $progClr }}" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $progress }}%;"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            @if($hasActions)
                                                <td>
                                                    @can('edit goal tracking')
                                                        @php
                                                            $editBase     = VW::GL_TRC . '.edit';
                                                            $editKebab    = Str::kebab($editBase);
                                                            $editResolved = Route::has($editBase) ? $editBase : (($editKebab !== $editBase && Route::has($editKebab)) ? $editKebab : null);
                                                            $editUrl      = ($editResolved && $gtId !== '') ? route($editResolved, $gtId) : '#';
                                                            $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL_TRC, 'edit_goal_tracking_route_unavailable') : 'Edit Goal Tracking route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Goal Tracking route is unavailable. Please contact technical support or your domain administrator.');
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_FL_CT }}"
                                                               data-url="{{ $editUrl }}"
                                                               data-size="lg"
                                                               data-ajax-popup="true"
                                                               data-title="{{ __('Edit Goal Tracking') }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Edit') }}"
                                                               data-guard-msg="{{ $editGuardMsg }}"
                                                               data-sv-localized="true">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('delete goal tracking')
                                                        @php
                                                            $destroyBase     = VW::GL_TRC . '.destroy';
                                                            $destroyKebab    = Str::kebab($destroyBase);
                                                            $destroyResolved = Route::has($destroyBase) ? $destroyBase : (($destroyKebab !== $destroyBase && Route::has($destroyKebab)) ? $destroyKebab : null);
                                                            $destroyUrl      = ($destroyResolved && $gtId !== '') ? route($destroyResolved, $gtId) : '#';
                                                            $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL_TRC, 'destroy_goal_tracking_route_unavailable') : 'Delete Goal Tracking route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Goal Tracking route is unavailable. Please contact technical support or your domain administrator.');
                                                            $deleteFormId    = 'goal-tracking-delete-form-' . ($gtId === '' ? 'x' : $gtId);
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Collective\Html\FormFacade::open([
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
                                                                   data-confirm="{{ __(($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?') }}|{{ __(($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                   data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </div>
                                                    @endcan
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ $hasActions ? 9 : 8 }}" class="text-center">{{ __('No goal tracking records found.') }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
