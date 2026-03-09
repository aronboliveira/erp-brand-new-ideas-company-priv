@php
    try {
$user         = Auth::user();
        $lang         = Utility::fetchUserLang(user: $user);
        $formsIsList  = (is_array($forms ?? null) && count($forms ?? []) > 0) || (($forms ?? null) instanceof Collection && $forms->isNotEmpty());
    } catch (\Throwable $e) {
        \Log::error('form_builders/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Form Builder') ?: 'Manage Form Builder' }}
@endsection

@push(ST::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/formBuilders/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/formBuilders/copy.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/formBuilders/index.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') ?: 'Dashboard' }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Form Builder') ?: 'Form Builder' }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    @php
        try {
            $createBase     = VW::FM_BD . '.create';
            $createKebab    = Str::kebab($createBase);
            $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
            $createUrl      = $createResolved ? route($createResolved) : '#';
            $createGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_BD, 'create_route_unavailable') ?? __('Create form route is unavailable. Please contact technical support or your domain administrator.');
        } catch (\Throwable $e) {
            \Log::error('form_builders/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <div class="{{ VC::FEND }}">
        <a
            href="{{ $createUrl }}"
            data-size="md"
            data-url="{{ $createUrl }}"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create New Form') ?: 'Create New Form' }}"
            class="{{ VC::BT_SM_PM }}"
            data-guard-msg="{{ base64_encode($createGuardMsg) }}"
            data-sv-localized="true"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CXL12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') ?: 'Name' }}</th>
                                    <th>{{ __('Response') ?: 'Response' }}</th>
                                    @if(($user?->{UsersConstants::COL_TP} ?? null) === PermissionsConstants::CPN)
                                        <th class="{{ VC::TX_END }}" width="200px">{{ __('Action') ?: 'Action' }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if($formsIsList)
                                    @foreach ($forms as $form)
                                        @php
                                            try {
                                                $formId      = data_get($form, 'id');
                                                $formName    = data_get($form, 'name', __('Unnamed form'));
                                                $formCode    = data_get($form, 'code');
                                                $responses   = data_get($form, 'response');
                                                $respCount   = ($responses instanceof Collection) ? $responses->count() : (is_array($responses) ? count($responses) : 0);
                                                $respDisplay = $respCount > 0 ? $respCount : __('No responses');
                                                $embedUrl    = $formCode ? url('/' . VW::FM . '/' . $formCode) : '#';
                                                $bindBase     = VW::FM_FD . '.bind';
                                                $bindResolved = Route::has($bindBase) ? $bindBase : (Route::has(Str::kebab($bindBase)) ? Str::kebab($bindBase) : null);
                                                $bindUrl      = ($bindResolved && $formId) ? route($bindResolved, $formId) : '#';
                                                $bindGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_FD, 'bind_route_unavailable') ?? __('Lead setting route is unavailable. Please contact technical support or your domain administrator.');
                                            } catch (\Throwable $e) {
                                                \Log::error('form_builders/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <tr>
                                            <td>{{ $formName }}</td>
                                            <td>{{ $respDisplay }}</td>

                                            @if(($user?->{UsersConstants::COL_TP} ?? null) === PermissionsConstants::CPN)
                                                <td class="{{ VC::TX_END }}">
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_FL_CT }} cp_link"
                                                           data-link="<iframe src='{{ $embedUrl }}' title='{{ $formName }}'></iframe>"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Click to copy iframe link') ?: 'Click to copy iframe link' }}">
                                                            <i class="ti ti-frame {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>

                                                    <div class="{{ VC::ACT_BTN }} bg-secondary {{ VC::MS2 }}">
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_FL_CT }}"
                                                           data-url="{{ $bindUrl }}"
                                                           data-ajax-popup="true"
                                                           data-size="md"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Convert into Lead Setting') ?: 'Convert into Lead Setting' }}"
                                                           data-title="{{ __('Convert into Lead Setting') ?: 'Convert into Lead Setting' }}"
                                                           data-guard-msg="{{ base64_encode($bindGuardMsg) }}"
                                                           data-sv-localized="true">
                                                            <i class="ti ti-exchange {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>

                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#"
                                                           class="{{ VC::BT_SM_FL_CT }} cp_link"
                                                           data-link="{{ $embedUrl }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Click to copy link') ?: 'Click to copy link' }}">
                                                            <i class="{{ VC::TI_COPY_WT }}"></i>
                                                        </a>
                                                    </div>

                                                    @can('manage form field')
                                                        @php
                                                            try {
                                                                $showBase     = VW::FM_BD . '.show';
                                                                $showResolved = Route::has($showBase) ? $showBase : (Route::has(Str::kebab($showBase)) ? Str::kebab($showBase) : null);
                                                                $showUrl      = ($showResolved && $formId) ? route($showResolved, $formId) : '#';
                                                                $showGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_BD, 'show_route_unavailable') ?? __('Form field route is unavailable. Please contact technical support or your domain administrator.');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('form_builders/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN }} bg-secondary {{ VC::MS2 }}">
                                                            <a href="{{ $showUrl }}"
                                                               class="{{ VC::BT_SM_FL_CT }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Form field') ?: 'Form field' }}"
                                                               data-guard-msg="{{ base64_encode($showGuardMsg) }}"
                                                               data-sv-localized="true">
                                                                <i class="ti ti-table {{ VC::TXT_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('view form response')
                                                        @php
                                                            try {
                                                                $respBase     = VW::FM . '.response';
                                                                $respResolved = Route::has($respBase) ? $respBase : (Route::has(Str::kebab($respBase)) ? Str::kebab($respBase) : null);
                                                                $respUrl      = ($respResolved && $formId) ? route($respResolved, $formId) : '#';
                                                                $respGuardMsg = Utility::fetchLinkMessage($lang, VW::FM, 'response_route_unavailable') ?? __('View response route is unavailable. Please contact technical support or your domain administrator.');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('form_builders/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_WRN }}">
                                                            <a href="{{ $respUrl }}"
                                                               class="{{ VC::BT_SM_FL_CT }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('View Response') ?: 'View Response' }}"
                                                               data-guard-msg="{{ base64_encode($respGuardMsg) }}"
                                                               data-sv-localized="true">
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('edit form builder')
                                                        @php
                                                            try {
                                                                $editBase     = VW::FM_BD . '.edit';
                                                                $editResolved = Route::has($editBase) ? $editBase : (Route::has(Str::kebab($editBase)) ? Str::kebab($editBase) : null);
                                                                $editUrl      = ($editResolved && $formId) ? route($editResolved, $formId) : '#';
                                                                $editGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_BD, 'edit_route_unavailable') ?? __('Edit form route is unavailable. Please contact technical support or your domain administrator.');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('form_builders/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_FL_CT }}"
                                                               data-url="{{ $editUrl }}"
                                                               data-ajax-popup="true"
                                                               data-size="md"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Edit') ?: 'Edit' }}"
                                                               data-title="{{ __('Form Builder Edit') ?: 'Form Builder Edit' }}"
                                                               data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                                               data-sv-localized="true">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('delete form builder')
                                                        @php
                                                            try {
                                                                $destroyBase     = VW::FM_BD . '.destroy';
                                                                $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has(Str::kebab($destroyBase)) ? Str::kebab($destroyBase) : null);
                                                                $destroyUrl      = ($destroyResolved && $formId) ? route($destroyResolved, $formId) : '#';
                                                                $destroyGuardMsg = Utility::fetchLinkMessage($lang, VW::EXP, 'destroy_route_unavailable') ?? 'Delete form builder route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('form_builders/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => 'delete-form-'.$formId, 'data-guard-msg' => $destroyGuardMsg, 'data-sv-localized' => 'true']) !!}
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_CT_PR }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Delete') ?: 'Delete' }}"
                                                                   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                   data-confirm-yes="document.getElementById('delete-form-{{$formId}}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ ($user?->{UsersConstants::COL_TP} ?? null) === PermissionsConstants::CPN ? 3 : 2 }}" class="{{ VC::TXCT }}">
                                            {{ __('No forms found.') ?: 'No forms found.' }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
