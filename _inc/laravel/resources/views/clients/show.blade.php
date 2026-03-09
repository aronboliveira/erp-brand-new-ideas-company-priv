@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
        $profile = Utility::getFile('uploads/avatar/');
    } catch (\Throwable $e) {
        \Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Client')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        try {
            $clientIndexRouteBase = VW::CLT.'.index';
            $clientIndexRouteKebab = Str::kebab($clientIndexRouteBase);
            $clientIndexResolvedName = Route::has($clientIndexRouteBase) ? $clientIndexRouteBase : (Route::has($clientIndexRouteKebab) ? $clientIndexRouteKebab : null);
            $clientIndexUrl = $clientIndexResolvedName ? route($clientIndexResolvedName) : '#';
            $clientIndexLang = isset($lang) ? $lang : Utility::fetchUserLang();
            $clientIndexGuardMsg = Utility::fetchLinkMessage($clientIndexLang, VW::CLT, 'client_index_route_unavailable') ?? 'Client index route is unavailable. Please contact technical support or your domain administrator.';
            $clientIndexBreadcrumbLinkId = 'breadcrumb-client-index-link';
        } catch (\Throwable $e) {
            \Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <li class="{{ VC::BCI }}">
        <a id="{{ $clientIndexBreadcrumbLinkId }}"
        href="{{ $clientIndexUrl }}"
        data-url="{{ $clientIndexUrl }}"
        data-guard-msg="{{ base64_encode($clientIndexGuardMsg) }}"
        data-sv-localized="true">{{ __('Client') }}</a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/clients/showIndex.js') }}"></script>
    @endpush
    <li class="{{ VC::BCI }}">  {{ !empty($client?->name) ? ucwords($client->name).__("'s Detail") : __('No client name available')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CXL12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        @php
                            try {
                                $isDateFormatAvailable = method_exists($user,'dateFormat');
                                $isPriceFormatAvailable = method_exists($user,'priceFormat');
                                $isEstimateNumberFormatAvailable = method_exists($user,'estimateNumberFormat');
                                $estimationList = ((is_array($estimations ?? null) && count($estimations ?? [])) || (($estimations ?? null) instanceof Collection && ($estimations)->isNotEmpty())) ? $estimations : [];
                            } catch (\Throwable $e) {
                                \Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Estimate') }}</th>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(($user?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CLT)
                                        <th width="250px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($estimationList as $estimate)
                                    @php
                                        try {
                                            $estId = data_get($estimate,'id');
                                            $estiNo = data_get($estimate,'estimation_id');
                                            $clientName = data_get($estimate,'client.name') ?: __('No client name available');
                                            $issueAt = data_get($estimate,'issue_date');
                                            $statusIdx = (int) (data_get($estimate,'status') ?? -1);
                                            $statusClasses = [0 => VC::BDG.' bg-primary', 1 => VC::BDG.' bg-danger', 2 => VC::BDG.' bg-warning', 3 => VC::BDG.' bg-success', 4 => VC::BDG.' bg-info'];
                                            $label = data_get(Estimation::$statuses ?? [],$statusIdx) ?: __('No status available');
                                            $valueTotal = (float) (method_exists($estimate,'getTotal') ? $estimate->getTotal() : 0);
                                        } catch (\Throwable $e) {
                                            \Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        <td class="Id">
                                            @can('View Estimation')
                                                @php
                                                    try {
                                                        $showName = ViewsConstants::EST . '.show';
                                                        $showUrl = ($estId && Route::has($showName)) ? route($showName, $estId) : '#';
                                                        $showGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EST, 'estimation_show_route_unavailable') ?: __('Failed to get estimation show route');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <a href="#" class="{{ VC::BT_OUTPM }}" data-url="{{ $showUrl }}" data-guard-msg="{{ base64_encode($showGuardMsg) }}" data-listener-alias="view-estimate">
                                                    <i class="{{ VC::TI_CC_PLS }}"></i>
                                                    {{ $estiNo ? ($isEstimateNumberFormatAvailable ? $user?->estimateNumberFormat($estiNo) : __('Failed to format estimate number')) : __('No estimate number available') }}
                                                </a>
                                            @else
                                                {{ $estiNo ? ($isEstimateNumberFormatAvailable ? $user?->estimateNumberFormat($estiNo) : __('Failed to format estimate number')) : __('No estimate number available') }}
                                            @endcan
                                        </td>
                                        <td>{{ $clientName }}</td>
                                        <td>{{ $issueAt ? ($isDateFormatAvailable ? $user?->dateFormat($issueAt) : (string) $issueAt) : __('No issue date available') }}</td>
                                        <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($valueTotal) : (string) $valueTotal }}</td>
                                        <td>
                                            @php
	try {
		($badgeClass = $statusClasses[$statusIdx] ?? (VC::BDG.' bg-secondary'))
		                                            <span class="{{ $badgeClass }} p-2 {{ VC::PX3 }} rounded">{{ __($label) }}</span>
		                                        </td>
		                                        @if(($user?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CLT)
		                                            <td class="Action">
		                                                @can('View Estimation')
		                                                    @php
		                                                        $showName = ViewsConstants::EST . '.show';
		                                                        $showUrl = ($estId && Route::has($showName)) ? route($showName, $estId) : '#';
		                                                        $showGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EST, 'estimation_show_route_unavailable') ?: __('Failed to get estimation show route');
	} catch (\Throwable $e) {
		\Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
                                                    <div class="{{ VC::ACT_BTN_WRN }}">
                                                        <a href="#" class="{{ VC::BT_SM_CT }}" data-url="{{ $showUrl }}" data-guard-msg="{{ base64_encode($showGuardMsg) }}" data-listener-alias="view-estimate" data-bs-toggle="tooltip" title="{{ __('View') }}"><i class="{{ VC::TI_EYE_WT }}"></i></a>
                                                    </div>
                                                @endcan
                                                @can('Edit Estimation')
                                                    @php
                                                        try {
                                                            $editName = ViewsConstants::EST . '.edit';
                                                            $editUrl = ($estId && Route::has($editName)) ? route($editName, $estId) : '#';
                                                            $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EST, 'estimation_edit_route_unavailable') ?: __('Failed to get estimation edit route');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="#" class="{{ VC::BT_SM_CT }}" data-url="{{ $editUrl }}" data-guard-msg="{{ base64_encode($editGuardMsg) }}" data-listener-alias="edit-estimate" data-ajax-popup="true" data-title="{{ __('Edit Estimation') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                    </div>
                                                @endcan
                                                @can('Delete Estimation')
                                                    @php
                                                        try {
                                                            $destroyName = ViewsConstants::EST . '.destroy';
                                                            $destroyUrl = ($estId && Route::has($destroyName)) ? route($destroyName, $estId) : '#';
                                                            $destroyGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EST, 'estimation_destroy_route_unavailable') ?: __('Failed to get estimation delete route');
                                                            $deleteFormId = 'delete-form-' . $estId;
                                                        } catch (\Throwable $e) {
                                                            \Log::error('clients/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open([
                                                            'method'         => 'DELETE',
                                                            'url'            => $destroyUrl,
                                                            'id'             => $deleteFormId,
                                                            'data-url'       => $destroyUrl,
                                                            'data-guard-msg' => $destroyGuardMsg,
                                                        ]) !!}
                                                        <a href="#" class="{{ VC::BT_SM_CT_PR }}" data-listener-alias="delete-estimate" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}">
                                                <div class="{{ VC::C6 }} {{ VC::TXCT }}">
                                                    <p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No estimations available') }}</p>
                                                </div>
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
    <script defer src="{{ asset('assets/js/routes/clients/show.js') }}">
    </script>
@endpush
