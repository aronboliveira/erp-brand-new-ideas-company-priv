@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Custom Field')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Custom Field')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create constant custom field')
            @php
                $linkId   = 'create-custom-field-link';
                $createUrl = Route::has(ViewsConstants::CST_FD.'.create')
                    ? route(ViewsConstants::CST_FD.'.create')
                    : '#';
                $createCustomFieldMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CST_FD,
                    'custom_field_create_route_unavailable'
                ) ?? 'Create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                href="#"
                id="{{ $linkId }}"
                data-url="{{ $createUrl }}"
                data-sv-localized="true"
                data-guard-msg=" {{ $createCustomFieldMsg }} "
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Custom Field') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.account_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Custom Field')}}</th>
                                <th> {{__('Type')}}</th>
                                <th> {{__('Module')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php
                                    $list = ((is_array($custom_fields ?? null) && count($custom_fields ?? [])) || (($custom_fields ?? null) instanceof Collection && ($custom_fields)->isNotEmpty())) ? $custom_fields : [];
                                @endphp
                                @forelse($list as $field)
                                    <tr>
                                        <td>{{ isset($field->name) && $field->name !== '' ? $field->name : __('No name available') }}</td>
                                        <td>{{ isset($field->type) && $field->type !== '' ? $field->type : __('No type available') }}</td>
                                        <td>{{ isset($field->module) && $field->module !== '' ? $field->module : __('No module available') }}</td>
                                        @if(Gate::check('edit constant custom field') || Gate::check('delete constant custom field'))
                                            <td class="Action">
                                                <span>
                                                    @can('edit constant custom field')
                                                        @php
                                                            $linkId = 'edit-custom-field-link-'.($field->id ?? '0');
                                                            $editUrl = route(ViewsConstants::CST_FD.'.edit', $field->id);
                                                            $editCustomFieldMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CST_FD, 'custom_field_edit_route_unavailable') ?? 'Edit Custom Field route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a href="#"
                                                            id="{{ $linkId }}"
                                                            class="{{ VC::BT_SM_CT }} edit-custom-field-link"
                                                            data-route-guard
                                                            data-url="{{ $editUrl }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $editCustomFieldMsg }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Custom Field') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete constant custom field')
                                                        @php
                                                            $delLinkId = 'delete-custom-field-link-'.($field->id ?? '0');
                                                            $delFormId = 'delete-custom-field-form-'.($field->id ?? '0');
                                                            $destroyUrl = route(ViewsConstants::CST_FD.'.destroy', $field->id);
                                                            $deleteCustomFieldMsg = Utility::fetchLinkMessage($lang, ViewsConstants::CST_FD, 'custom_field_delete_unavailable') ?? 'Delete Custom Field route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            {!! Form::open(['method' => 'DELETE','url' => $destroyUrl,'id' => $delFormId]) !!}
                                                                <a href="#"
                                                                id="{{ $delLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }} delete-custom-field-link"
                                                                data-route-guard
                                                                data-url="{{ $destroyUrl }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $deleteCustomFieldMsg }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang,'generics','are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang,'generics','irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
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
                                        <td colspan="4">
                                            <div class="text-center">
                                                <i class="{{ VC::TI_INB }} {{ VC::FS_3X }} {{ VC::TX_MUTED }}"></i>
                                                <p class="{{ VC::TX_MUTED }} mt-2">{{ __('No custom fields found') }}</p>
                                            </div>
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
@endsection

@push(StacksConstants::ADM_SCR_PG)
    @can('create constant custom field')
        <script defer src="{{ asset('assets/js/routes/customFields/create.js') }}"></script>
    @endcan
    @can('edit constant custom field')
        <script defer src="{{ asset('assets/js/routes/customFields/edit.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/customFields/delete.js') }}"></script>
    @endcan
@endpush

