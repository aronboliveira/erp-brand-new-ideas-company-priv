@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user         = Auth::user();
    $lang         = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg  = is_callable([Utility::class,'fetchLinkMessage']);

    $dashUrl      = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard    = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : null) ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $list = [];
    if (is_array($permissions ?? null) && count($permissions)) {
        $list = $permissions;
    } elseif (($permissions ?? null) instanceof Collection && $permissions->isNotEmpty()) {
        $list = $permissions;
    }
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Permissions') }}
@endsection

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Permissions') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create permission')
            @php
                $createUrl   = Route::has(VW::PMS.'.create') ? route(VW::PMS.'.create') : '#';
                $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PMS, 'create_permission_unavailable') : 'Create Permission route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Permission route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-title="{{ __('Create New Permission') }}"
               data-sv-localized="true"
               data-guard-msg="{{ $createGuard }}"
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
                <div class="card-header">
                    <div class="{{ VC::DFL_JCB }} w-100">
                        <h4 class="{{ VC::MB0 }}">{{ __('Manage Permissions') }}</h4>
                        @can('create permission')
                            @php
                                $createUrl   = Route::has(VW::PMS.'.create') ? route(VW::PMS.'.create') : '#';
                                $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PMS, 'create_permission_unavailable') : 'Create Permission route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Permission route is unavailable. Please contact technical support or your domain administrator.');
                            @endphp
                            <a href="{{ $createUrl }}"
                               data-url="{{ $createUrl }}"
                               data-ajax-popup="true"
                               data-size="lg"
                               data-title="{{ __('Create New Permission') }}"
                               data-sv-localized="true"
                               data-guard-msg="{{ $createGuard }}"
                               class="{{ VC::BT_PRM }}">
                                <i class="fa fa-plus"></i> {{ __('Create') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Permissions') }}</th>
                                    @canany(['edit permission','delete permission'])
                                        <th class="text-end" width="200px">{{ __('Action') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $permission)
                                    @php
                                        $permName = isset($permission->name) && $permission->name !== '' ? $permission->name : __('(unnamed permission)');
                                    @endphp
                                    <tr>
                                        <td>{{ $permName }}</td>
                                        @canany(['edit permission','delete permission'])
                                            <td class="action text-end">
                                                @can('edit permission')
                                                    @php
                                                        $editUrl   = Route::has(VW::PMS.'.edit') ? route(VW::PMS.'.edit', $permission->id) : '#';
                                                        $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PMS, 'edit_permission_unavailable') : 'Edit Permission route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Permission route is unavailable. Please contact technical support or your domain administrator.');
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="{{ $editUrl }}"
                                                           class="{{ VC::BT_SM_CT }}"
                                                           data-url="{{ $editUrl }}"
                                                           data-ajax-popup="true"
                                                           data-size="lg"
                                                           data-title="{{ __('Update permission') }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ $editGuard }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('delete permission')
                                                    @php
                                                        $delUrl   = Route::has(VW::PMS.'.destroy') ? route(VW::PMS.'.destroy', $permission->id) : '#';
                                                        $delGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PMS, 'delete_permission_unavailable') : 'Delete Permission route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Permission route is unavailable. Please contact technical support or your domain administrator.');
                                                        $formId   = 'delete-form-'.$permission->id;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $formId, 'data-url'=>$delUrl, 'data-sv-localized'=>'true', 'data-guard-msg'=>$delGuard]) !!}
                                                            <a href="{{ $delUrl }}"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-url="{{ $delUrl }}"
                                                               data-sv-localized="true"
                                                               data-guard-msg="{{ $delGuard }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">{{ __('No permissions found.') }}</td>
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

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/permissions/index.js') }}"></script>
@endpush
