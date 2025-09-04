@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
    $createRoute = Route::has(ViewsConstants::ALW_OPT.'.create')
        ? route(ViewsConstants::ALW_OPT.'.create')
        : Route::has(Str::kebab(ViewsConstants::ALW_OPT.'.create'))
            ? route(Str::kebab(ViewsConstants::ALW_OPT.'.create'))
            : '#';
    $createId = 'allowance-option-create-link';
    $createMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW_OPT,
        'allowance_option_create_route_unavailable'
    ) ?? 'Create Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Allowance Option') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a
            href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}
        >
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Allowance Option') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create allowance option')
            <a
                id="{{ $createId }}"
                href="{{ $createRoute }}"
                data-url="{{ $createRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createMsg }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Allowance Option') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Allowance Option') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($allowanceoptions as $option)
                                    <tr>
                                        <td>{{ $option->name }}</td>
                                        <td>
                                            @can('edit allowance option')
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    @php
                                                        $editRoute = Route::has(ViewsConstants::ALW_OPT.'.edit')
                                                            ? route(ViewsConstants::ALW_OPT.'.edit', $option->id)
                                                            : Route::has(Str::kebab(ViewsConstants::ALW_OPT.'.edit'))
                                                                ? route(Str::kebab(ViewsConstants::ALW_OPT.'.edit'), $option->id)
                                                                : '#';
                                                        $editId = "allowance-option-edit-{$option->id}-link";
                                                        $editMsg = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::ALW_OPT,
                                                            'allowance_option_edit_route_unavailable'
                                                        ) ?? 'Edit Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <a
                                                        id="{{ $editId }}"
                                                        href="{{ $editRoute }}"
                                                        data-url="{{ $editRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Allowance Option') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete allowance option')
                                                @php
                                                    $destroyRoute = Route::has(ViewsConstants::ALW_OPT.'.destroy')
                                                        ? route(ViewsConstants::ALW_OPT.'.destroy', $option->id)
                                                        : Route::has(Str::kebab(ViewsConstants::ALW_OPT.'.destroy'))
                                                            ? route(Str::kebab(ViewsConstants::ALW_OPT.'.destroy'), $option->id)
                                                            : '#';
                                                    $deleteId = "allowance-option-delete-{$option->id}-link";
                                                    $deleteMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::ALW_OPT,
                                                        'allowance_option_destroy_route_unavailable'
                                                    ) ?? 'Delete Allowance Option route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route'  => [$destroyRoute],
                                                        'id'     => "delete-form-{$option->id}"
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deleteId }}"
                                                            href="#"
                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                            data-url="{{ $destroyRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $deleteMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $option->id }}').submit();"
                                                        >
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script async src="{{ asset('assets/js/routes/allowanceOptions/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/allowanceOptions/index.js') }}"></script>
@endsection