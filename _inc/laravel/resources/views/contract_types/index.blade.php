@php
    try {
$user              = Auth::user();
        $lang              = Utility::fetchUserLang(user: $user);
        $createRoute       = Route::has(ViewsConstants::CTC_TP . '.create')
            ? route(ViewsConstants::CTC_TP . '.create')
            : (Route::has(Str::kebab(ViewsConstants::CTC_TP . '.create'))
                ? route(Str::kebab(ViewsConstants::CTC_TP . '.create'))
                : '#');
        $createBtnId       = 'contract-type-create-btn';
        $createGuardMsg    = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CTC_TP,
            'contract_type_create_route_unavailable'
        ) ?? 'Contract Type create route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('contract_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Contract Type') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Contract Type') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a
            id="{{ $createBtnId }}"
            href="#"
            data-url="{{ $createRoute }}"
            data-guard-msg="{{ base64_encode($createGuardMsg) }}"
            data-ajax-popup="true"
            data-size="md"
            class="{{ VC::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Create New Contract Type') }}"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C3 }}">
            @include('layouts.crm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
                                        <th class="{{ VC::TX_END }}">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($types) && ((is_array($types) && count($types) > 0) || ($types instanceof Collection && $types->isNotEmpty())))
                                    @foreach($types as $type)
                                        @php
                                            $typeId     = $type->id ?? null;
                                            $typeName   = !empty($type->name) ? $type->name : __('No contract type name available');
@endphp
                                        <tr class="font-style">
                                            <td>{{ $typeName }}</td>
                                            @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
                                                @php
                                                    try {
                                                        $editUrl   = $typeId ? route(ViewsConstants::CTC_TP . '.edit', $typeId) : '#';
                                                        $editBtnId = 'contract-type-edit-btn-' . ($typeId ?? 'x');
                                                        $editMsg   = Utility::fetchLinkMessage($lang, ViewsConstants::CTC_TP, 'contract_type_edit_route_unavailable') ?: __('Contract Type edit route is unavailable. Please contact technical support or your domain administrator.');
                                                        $delUrl    = $typeId ? route(ViewsConstants::CTC_TP . '.destroy', $typeId) : '#';
                                                        $delFormId = 'contract-type-delete-form-' . ($typeId ?? 'x');
                                                        $delBtnId  = 'contract-type-delete-btn-' . ($typeId ?? 'x');
                                                        $delMsg    = Utility::fetchLinkMessage($lang, ViewsConstants::CTC_TP, 'contract_type_destroy_route_unavailable') ?: __('Contract Type delete route is unavailable. Please contact technical support or your domain administrator.');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('contract_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <td class="action {{ VC::TX_END }}">
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        <a id="{{ $editBtnId }}" href="#" data-url="{{ $editUrl }}" data-guard-msg="{{ base64_encode($editMsg) }}" data-ajax-popup="true" data-size="md" class="{{ VC::BT_SM_FL_CT }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Type') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open([
                                                            'url'            => $delUrl,
                                                            'method'         => 'DELETE',
                                                            'id'             => $delFormId,
                                                            'data-url'       => $delUrl,
                                                            'data-guard-msg' => $delMsg,
                                                        ]) !!}
                                                            <a id="{{ $delBtnId }}" href="#" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2">
                                            <div class="{{ VC::TXCT }}">
                                                <i class="{{ VC::TI_INB }} {{ VC::FS_3X }} {{ VC::TX_MUTED }}"></i>
                                                <p class="{{ VC::TX_MUTED }} mt-2">{{ __('No Contract Types Found') }}</p>
                                            </div>
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

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        window.RouteGuard?.guardMultiple?.(
            '{{ $createBtnId }}'
            @if(!empty($types) && ((is_array($types) && count($types) > 0) || ($types instanceof Collection && $types->isNotEmpty())))
                @foreach($types as $type)
                    , 'contract-type-edit-btn-{{ $type->id }}'
                    , 'contract-type-delete-btn-{{ $type->id }}'
                @endforeach
            @endif
        );
    </script>
@endpush
