@php
    try {
$lang = Utility::fetchUserLang();
        $createName     = ViewsConstants::COA_TP . '.create';
        $createRoute    = Route::has($createName)
            ? route($createName)
            : (Route::has(Str::kebab($createName))
                ? route(Str::kebab($createName))
                : '#');
        $createGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::COA_TP,
            'chart_of_account_type_create_route_unavailable'
        ) ?? 'Create Chart of Account Type route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('chart_of_account_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Chart of Account Type') }}
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="all-button-box {{ VC::RW }} {{ VC::DFL }} {{ VC::JCE }}">
        @can(PermissionsConstants::CR_COA_TYPE)
            <div class="{{ VC::CXL2 }} {{ VC::CL2 }} {{ VC::CM4 }} {{ VC::CS6 }} {{ VC::C6 }}">
                <a
                    href="#"
                    id="createTypeBtn"
                    data-url="{{ $createRoute }}"
                    data-guard-msg="{{ base64_encode($createGuardMsg) }}"
                    data-listener-alias="create-type"
                    data-ajax-popup="true"
                    data-title="{{ __('Create New Type') }}"
                    class="{{ VC::BT_XS }} btn-white btn-icon-only width-auto"
                >
                    <i class="{{ VC::TI_PLS }}"></i> {{ __('Create') }}
                </a>
            </div>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_MT }} {{ VC::BD }}">
                    <div class="{{ VC::TB }}-striped {{ VC::MB0 }} dataTable">
                        <table class="{{ VC::TB }}-striped {{ VC::MB0 }} dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $typesIterable = (is_array($types ?? null) && count($types ?? [])) || (($types ?? null) instanceof Collection && ($types)->isNotEmpty());
@endphp
                                @if($typesIterable)
                                    @foreach($types as $type)
                                        @php
                                            $typeId = data_get($type, 'id');
@endphp
                                        <tr>
                                            <td>{{ !empty(data_get($type, 'name')) ? data_get($type, 'name') : __('No chart of account type name available') }}</td>
                                            <td class="Action">
                                                <span>
                                                    @can('edit constant chart of account type')
                                                        @php
                                                            $editRoute = !empty($typeId) && Route::has(ViewsConstants::COA_TP . '.edit') ? route(ViewsConstants::COA_TP . '.edit', $typeId) : '#';
                                                            $editGuardMsg = Utility::fetchLinkMessage($lang ?? null, ViewsConstants::COA_TP, 'chart_of_account_type_edit_route_unavailable') ?? __('Failed to open chart of account type editor');
@endphp
                                                        <a href="{{ $editRoute }}" class="edit-icon{{ $editRoute === '#' ? ' disabled' : '' }}" data-url="{{ $editRoute }}" data-guard-msg="{{ base64_encode($editGuardMsg) }}" data-listener-alias="edit-type" data-ajax-popup="true" data-title="{{ __('Edit Unit') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                    @endcan
                                                    @can('delete constant chart of account type')
                                                        @php
                                                            try {
                                                                $destroyRoute = !empty($typeId) && Route::has(ViewsConstants::COA_TP . '.destroy') ? route(ViewsConstants::COA_TP . '.destroy', $typeId) : '#';
                                                                $destroyGuardMsg = Utility::fetchLinkMessage($lang ?? null, ViewsConstants::COA_TP, 'chart_of_account_type_destroy_route_unavailable') ?? __('Failed to open chart of account type deletion');
                                                                $deleteFormId = 'delete-form-' . ($typeId ?? 'x');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('chart_of_account_types/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $destroyRoute, 'id' => $deleteFormId, 'data-url' => $destroyRoute, 'data-guard-msg' => $destroyGuardMsg]) !!}
                                                            <a href="#" class="delete-icon{{ $destroyRoute === '#' ? ' disabled' : '' }}" data-listener-alias="delete-type" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __(Utility::fetchLinkMessage($lang ?? null, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang ?? null, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"><i class="{{ VC::TI_TRS }}"></i></a>
                                                        {!! Form::close() !!}
                                                    @endcan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="2" class="{{ VC::TXCT_DK }}">{{ __('No chart of account types available') }}</td></tr>
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
    <script defer src="{{ asset('assets/js/routes/chartOfAccounts/store.js') }}"></script>
@endpush
