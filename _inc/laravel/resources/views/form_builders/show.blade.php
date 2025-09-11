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
    use Collective\Html\FormFacade as Form;

    $user            = Auth::user();
    $lang            = Utility::fetchUserLang(user: $user);
    $hasFormBuilder  = !empty($formBuilder ?? null) && data_get($formBuilder, 'id');
    $formName        = $hasFormBuilder ? (data_get($formBuilder, 'name') ?: __('Unnamed form')) : __('Form not found');

    $fields          = $hasFormBuilder ? data_get($formBuilder, 'form_field') : [];
    $fieldsIsList    = (is_array($fields ?? null) && count($fields ?? []) > 0) || (($fields ?? null) instanceof Collection && $fields->isNotEmpty());
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ $hasFormBuilder ? ($formName . ' ' . __("Form Field")) : __('Form not found') }}
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/formBuilders/fields.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $indexBase     = VW::FM_BD . '.index';
        $indexKebab    = Str::kebab($indexBase);
        $indexResolved = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
        $indexUrl      = $indexResolved ? route($indexResolved) : '#';
        $indexGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_BD, 'index_route_unavailable') ?? __('Form builder index route is unavailable. Please contact technical support or your domain administrator.');
    @endphp
    <li class="breadcrumb-item">
        <a href="{{ $indexUrl }}" data-url="{{ $indexUrl }}" data-guard-msg="{{ $indexGuardMsg }}" data-sv-localized="true">{{ __('Form Builder') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Add Field') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    @can('create form field')
        @php
            $createBase     = VW::FM_FD . '.create';
            $createKebab    = Str::kebab($createBase);
            $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
            $createUrl      = ($createResolved && $hasFormBuilder) ? route($createResolved, data_get($formBuilder, 'id')) : '#';
            $createGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_FD, 'create_route_unavailable') ?? __('Create field route is unavailable. Please contact technical support or your domain administrator.');
        @endphp
        <div class="float-end">
            <a href="#"
               data-size="md"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create New Field') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ $createGuardMsg }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        </div>
    @endcan
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!$hasFormBuilder)
                        <div class="alert alert-warning mb-0" role="alert">{{ __('The requested form was not found or is unavailable.') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    @canany(['edit form builder','delete form builder'])
                                        <th class="text-end" width="200px">{{ __('Action') }}</th>
                                    @endcanany
                                </tr>
                                </thead>
                                <tbody>
                                @if($fieldsIsList)
                                    @foreach ($fields as $field)
                                        @php
                                            $fieldId   = data_get($field, 'id');
                                            $fieldName = data_get($field, 'name', __('Unnamed field'));
                                            $fieldType = ucfirst(strtolower(data_get($field, 'type', __('unknown'))));
                                        @endphp
                                        <tr>
                                            <td>{{ $fieldName }}</td>
                                            <td>{{ $fieldType }}</td>
                                            @canany(['edit form builder','delete form builder'])
                                                <td class="text-end">
                                                    @can('edit form builder')
                                                        @php
                                                            $editBase     = VW::FM_FD . '.edit';
                                                            $editKebab    = Str::kebab($editBase);
                                                            $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                            $editUrl      = ($editResolved && $fieldId && $hasFormBuilder) ? route($editResolved, [data_get($formBuilder,'id'), $fieldId]) : '#';
                                                            $editGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_FD, 'edit_route_unavailable') ?? __('Edit field route is unavailable. Please contact technical support or your domain administrator.');
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_FL_CT }}"
                                                               data-url="{{ $editUrl }}"
                                                               data-ajax-popup="true"
                                                               data-size="md"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Edit') }}"
                                                               data-title="{{ __('Form Builder Edit') }}"
                                                               data-guard-msg="{{ $editGuardMsg }}"
                                                               data-sv-localized="true">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('delete form builder')
                                                        @php
                                                            $destroyBase     = VW::FM_FD . '.destroy';
                                                            $destroyKebab    = Str::kebab($destroyBase);
                                                            $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                                            $destroyUrl      = ($destroyResolved && $fieldId && $hasFormBuilder) ? route($destroyResolved, [data_get($formBuilder,'id'), $fieldId]) : '#';
                                                            $destroyGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_FD, 'destroy_route_unavailable') ?? __('Delete field route is unavailable. Please contact technical support or your domain administrator.');
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => 'delete-form-'.$fieldId, 'data-guard-msg' => $destroyGuardMsg, 'data-sv-localized' => 'true']) !!}
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_CT_PR }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Delete') }}"
                                                                   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                   data-confirm-yes="document.getElementById('delete-form-{{$fieldId}}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </td>
                                            @endcanany
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        @canany(['edit form builder','delete form builder'])
                                            <td colspan="3" class="text-center">{{ __('No fields found.') }}</td>
                                        @else
                                            <td colspan="2" class="text-center">{{ __('No fields found.') }}</td>
                                        @endcanany
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
