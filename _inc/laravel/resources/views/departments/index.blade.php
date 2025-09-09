@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang = isset($user) ? Utility::fetchUserLang(user: $user) : Utility::fetchUserLang();
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Department') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Department') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create department')
            @php
                $dptCreateBase  = VW::DPT.'.create';
                $dptCreateKebab = Str::kebab($dptCreateBase);
                $dptCreateName  = Route::has($dptCreateBase) ? $dptCreateBase : (Route::has($dptCreateKebab) ? $dptCreateKebab : null);
                $dptCreateUrl   = $dptCreateName ? route($dptCreateName) : '#';
                $dptCreateMsg   = Utility::fetchLinkMessage($lang, VW::DPT, 'create_department_route_unavailable') ?? 'Create department route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a id="department-create-btn"
               href="{{ $dptCreateUrl }}"
               data-url="{{ $dptCreateUrl }}"
               data-guard-msg="{{ $dptCreateMsg }}"
               data-sv-localized="true"
               data-ajax-popup="true"
               data-title="{{ __('Create New Department') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-3">@include('layouts.hrm_setup')</div>
        <div class="col-9">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($departments as $department)
                                    @php
                                        $depId     = (string) ($department->id ?? '');
                                        $depName   = !empty($department->name) ? $department->name : __('No name available for department');
                                        $brName    = optional($department->branch)->name ?? __('No name available for branch');
                                    @endphp
                                    <tr>
                                        <td>{{ $brName }}</td>
                                        <td>{{ $depName }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('edit department')
                                                    @php
                                                        $dptEditBase  = VW::DPT.'.edit';
                                                        $dptEditKebab = Str::kebab($dptEditBase);
                                                        $dptEditName  = Route::has($dptEditBase) ? $dptEditBase : (Route::has($dptEditKebab) ? $dptEditKebab : null);
                                                        $dptEditUrl   = ($dptEditName && $depId !== '') ? route($dptEditName, [$depId]) : '#';
                                                        $dptEditMsg   = Utility::fetchLinkMessage($lang, VW::DPT, 'edit_department_route_unavailable') ?? 'Edit department route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a id="department-edit-btn-{{ $depId }}"
                                                           href="{{ $dptEditUrl }}"
                                                           data-url="{{ $dptEditUrl }}"
                                                           data-guard-msg="{{ $dptEditMsg }}"
                                                           data-sv-localized="true"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Department') }}"
                                                           class="{{ VC::BT_SM_FL_CT }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete department')
                                                    @php
                                                        $dptDestroyBase  = VW::DPT.'.destroy';
                                                        $dptDestroyKebab = Str::kebab($dptDestroyBase);
                                                        $dptDestroyName  = Route::has($dptDestroyBase) ? $dptDestroyBase : (Route::has($dptDestroyKebab) ? $dptDestroyKebab : null);
                                                        $dptDestroyUrl   = ($dptDestroyName && $depId !== '') ? route($dptDestroyName, [$depId]) : '#';
                                                        $dptDestroyMsg   = Utility::fetchLinkMessage($lang, VW::DPT, 'destroy_department_route_unavailable') ?? 'Delete department route is unavailable. Please contact technical support or your domain administrator.';
                                                        $confirmTitle = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                        $confirmBody  = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {{ Form::open([
                                                            'method' => 'DELETE',
                                                            'url'    => $dptDestroyUrl,
                                                            'id'     => 'delete-form-'.$depId
                                                        ]) }}
                                                            <a id="delete-department-btn-{{ $depId }}"
                                                               href="{{ $dptDestroyUrl }}"
                                                               data-url="{{ $dptDestroyUrl }}"
                                                               data-guard-msg="{{ $dptDestroyMsg }}"
                                                               data-sv-localized="true"
                                                               class="{{ VC::BT_SM_CT_PR }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-confirm="{{ __($confirmTitle) }}|{{ __($confirmBody) }}"
                                                               data-confirm-yes="document.getElementById('delete-form-{{ $depId }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {{ Form::close() }}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">{{ __('No departments found for your query.') }}</td>
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
    @can('create department')
        <script defer src="{{ asset('assets/js/routes/departments/create.js') }}"></script>
    @endcan
    @can('edit department')
        <script defer src="{{ asset('assets/js/routes/departments/edit.js') }}"></script>
    @endcan
    @can('delete department')
        <script defer src="{{ asset('assets/js/routes/departments/destroy.js') }}"></script>
    @endcan
@endpush
