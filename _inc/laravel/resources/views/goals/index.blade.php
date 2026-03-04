@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\{Goal, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $hasUser = (bool) $user;
    $hasUserPriceFormat = $hasUser && method_exists($user, 'priceFormat');
    $hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
    $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : (string) app()->getLocale();
    $hasGoalTypeMap = property_exists(Goal::class, 'goalType') && is_array(Goal::$goalType);
@endphp
@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Goals') }}
@endsection
@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Goal') }}</li>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/goals/index.js') }}"></script>
@endpush

@section(YW::ADM_ACT_BTN)
<div class="{{ VC::FEND }}">
    @can('create goal')
        @php
            $createBase     = VW::GL . '.create';
            $createKebab    = Str::kebab($createBase);
            $createResolved = Route::has($createBase) ? $createBase : (($createKebab !== $createBase && Route::has($createKebab)) ? $createKebab : null);
            $createUrl      = $createResolved ? route($createResolved) : '#';
            $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL, 'create_goal_route_unavailable') : 'Create Goal route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Goal route is unavailable. Please contact technical support or your domain administrator.');
        @endphp
        <a href="#"
           data-url="{{ $createUrl }}"
           data-bs-toggle="tooltip"
           data-size="lg"
           title="{{ __('Create') }}"
           data-ajax-popup="true"
           data-title="{{ __('Create New Goal') }}"
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
        <div class="alert alert-warning mb-0" role="alert">{{ __('Failed to load Goals for the current user.') }}</div>
    @else
        <div class="{{ VC::RW }}">
            <div class="col-xl-12">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            @php
                                $list = $golas ?? [];
                                $hasList = ($list instanceof Collection) ? $list->isNotEmpty() : (is_array($list) && count($list) > 0);
                                $hasActions = $user->can('edit goal') || $user->can('delete goal');
                            @endphp
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('From') }}</th>
                                        <th>{{ __('To') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Is Dashboard Display') }}</th>
                                        @if($hasActions)
                                            <th width="10%">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($hasList)
                                        @foreach($list as $gola)
                                            @php
                                                $gid      = isset($gola->id) ? (string) $gola->id : '';
                                                $name     = isset($gola->name) && $gola->name !== '' ? $gola->name : __('Unnamed goal');
                                                $typeKey  = $gola->type ?? null;
                                                $typeTxt  = ($hasGoalTypeMap && isset(Goal::$goalType[$typeKey])) ? __(Goal::$goalType[$typeKey]) : __('Unknown');
                                                $from     = isset($gola->from) ? $gola->from : __('No start available');
                                                $to       = isset($gola->to) ? $gola->to : __('No end available');
                                                $amountV  = $gola->amount ?? null;
                                                $amount   = $hasUserPriceFormat && $amountV !== null ? $user->priceFormat($amountV) : ($amountV !== null ? $amountV : __('No amount available'));
                                                $isDisp   = isset($gola->is_display) && (int)$gola->is_display === 1 ? __('Yes') : __('No');
                                            @endphp
                                            <tr>
                                                <td class="font-style">{{ $name }}</td>
                                                <td class="font-style">{{ $typeTxt }}</td>
                                                <td class="font-style">{{ $from }}</td>
                                                <td class="font-style">{{ $to }}</td>
                                                <td class="font-style">{{ $amount }}</td>
                                                <td class="font-style">{{ $isDisp }}</td>
                                                @if($hasActions)
                                                    <td class="Action">
                                                        <span>
                                                            @can('edit goal')
                                                                @php
                                                                    $editBase     = VW::GL . '.edit';
                                                                    $editKebab    = Str::kebab($editBase);
                                                                    $editResolved = Route::has($editBase) ? $editBase : (($editKebab !== $editBase && Route::has($editKebab)) ? $editKebab : null);
                                                                    $editUrl      = ($editResolved && $gid !== '') ? route($editResolved, $gid) : '#';
                                                                    $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL, 'edit_goal_route_unavailable') : 'Edit Goal route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Goal route is unavailable. Please contact technical support or your domain administrator.');
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_FL_CT }}"
                                                                       data-url="{{ $editUrl }}"
                                                                       data-ajax-popup="true"
                                                                       data-title="{{ __('Edit Goal') }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Edit') }}"
                                                                       data-guard-msg="{{ $editGuardMsg }}"
                                                                       data-sv-localized="true">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('delete goal')
                                                                @php
                                                                    $destroyBase     = VW::GL . '.destroy';
                                                                    $destroyKebab    = Str::kebab($destroyBase);
                                                                    $destroyResolved = Route::has($destroyBase) ? $destroyBase : (($destroyKebab !== $destroyBase && Route::has($destroyKebab)) ? $destroyKebab : null);
                                                                    $destroyUrl      = ($destroyResolved && $gid !== '') ? route($destroyResolved, $gid) : '#';
                                                                    $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL, 'destroy_goal_route_unavailable') : 'Delete Goal route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Goal route is unavailable. Please contact technical support or your domain administrator.');
                                                                    $delFormId       = 'goal-delete-form-' . ($gid === '' ? 'x' : $gid);
                                                                    $confirmTitle    = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                                    $confirmBody     = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Form::open([
                                                                        'method'            => 'DELETE',
                                                                        'url'               => $destroyUrl,
                                                                        'id'                => $delFormId,
                                                                        'data-url'          => $destroyUrl,
                                                                        'data-guard-msg'    => $destroyGuardMsg,
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
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="{{ $hasActions ? 7 : 6 }}" class="text-center">{{ __('No goals found.') }}</td>
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
