@php
    $goaltypes ??= [];
    try {
$user = Auth::user();
        $hasUser = (bool) $user;
        $hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
        $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : (string) app()->getLocale();
    } catch (\Throwable $e) {
        \Log::error('goal_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Goal Type') }}
@endsection
@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Goal Type') }}</li>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/goals/types/index.js') }}"></script>
@endpush

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create goal type')
            @php
                try {
                    $createBase     = VW::GL_TP . '.create';
                    $createKebab    = Str::kebab($createBase);
                    $createResolved = Route::has($createBase) ? $createBase : (($createKebab !== $createBase && Route::has($createKebab)) ? $createKebab : null);
                    $createUrl      = $createResolved ? route($createResolved) : '#';
                    $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL_TP, 'create_goal_type_route_unavailable') : 'Create Goal Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Goal Type route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('goal_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="#"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Create New Goal Type') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ base64_encode($createGuardMsg) }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="{{ VC::C3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Goal Type') }}</th>
                                @php
                                    $hasActions = $hasUser && ($user->can('edit goal type') || $user->can('delete goal type'));
@endphp
                                @if($hasActions)
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($goaltypes as $goaltype)
                                @php
                                    $gtId = (string) ($goaltype->id ?? '');
                                    $gtName = $goaltype->name ?? __('No goal type available');
@endphp
                                <tr>
                                    <td>{{ $gtName }}</td>
                                    @if($hasActions)
                                        <td>
                                            @can('edit goal type')
                                                @php
                                                    try {
                                                        $editBase     = VW::GL_TP . '.edit';
                                                        $editKebab    = Str::kebab($editBase);
                                                        $editResolved = Route::has($editBase) ? $editBase : (($editKebab !== $editBase && Route::has($editKebab)) ? $editKebab : null);
                                                        $editUrl      = ($editResolved && $gtId !== '') ? route($editResolved, $gtId) : '#';
                                                        $editGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL_TP, 'edit_goal_type_route_unavailable') : 'Edit Goal Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Goal Type route is unavailable. Please contact technical support or your domain administrator.');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('goal_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="#"
                                                       class="{{ VC::BT_SM_FL_CT }}"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Goal Type') }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}"
                                                       data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                                       data-sv-localized="true">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete goal type')
                                                @php
                                                    try {
                                                        $destroyBase     = VW::GL_TP . '.destroy';
                                                        $destroyKebab    = Str::kebab($destroyBase);
                                                        $destroyResolved = Route::has($destroyBase) ? $destroyBase : (($destroyKebab !== $destroyBase && Route::has($destroyKebab)) ? $destroyKebab : null);
                                                        $destroyUrl      = ($destroyResolved && $gtId !== '') ? route($destroyResolved, $gtId) : '#';
                                                        $destroyGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::GL_TP, 'destroy_goal_type_route_unavailable') : 'Delete Goal Type route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Goal Type route is unavailable. Please contact technical support or your domain administrator.');
                                                        $deleteFormId    = 'goal-type-delete-form-' . ($gtId === '' ? 'x' : $gtId);
                                                        $confirmTitle    = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                        $confirmBody     = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('goal_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
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
                                                           data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
