@php
$user ??= null;
	$lang ??= 'en';
	try {
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user: $user) ?? 'en';
	} catch (\Error $e) {
		Log::error('Error in promotions/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in promotions/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in promotions/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Promotion') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    @php
        $dashboardBaseName ??= 'dashboard';
        try {
            $dashboardKebabName = Str::kebab($dashboardBaseName);
            $dashboardResolvedName = Route::has($dashboardBaseName) ? $dashboardBaseName : (Route::has($dashboardKebabName) ? $dashboardKebabName : null);
            $dashboardUrl = $dashboardResolvedName ? route($dashboardResolvedName) : '#';
            $dashboardLinkId = 'dashboard-breadcrumb-link';
            $dashboardGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Throwable $e) {
            \Log::error('promotions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashboardUrl }}"
           id="{{ $dashboardLinkId }}"
           data-url="{{ $dashboardUrl }}"
           data-guard-msg="{{ base64_encode($dashboardGuardMsg) }}"
           {{ $dashboardUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Promotion') }}</li>
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/js/routes/dashboard/index.js') }}" defer></script>
    @endpush
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create promotion')
            @php
                try {
                    $prmCreateBaseName = ViewsConstants::PRM . '.create';
                    $prmCreateKebabName = Str::kebab($prmCreateBaseName);
                    $prmCreateResolvedName = Route::has($prmCreateBaseName) ? $prmCreateBaseName : (Route::has($prmCreateKebabName) ? $prmCreateKebabName : null);
                    $prmCreateUrl = $prmCreateResolvedName ? route($prmCreateResolvedName) : '#';
                    $prmCreateLinkId = 'promotion-create-link';
                    $prmCreateTitle = __('Create New Promotion');
                    $prmCreateTooltip = __('Create');
                    $prmCreateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRM, 'create_promotion_unavailable') ?? 'Create promotion route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('promotions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                <a href="{{ $prmCreateUrl }}"
                   id="{{ $prmCreateLinkId }}"
                   class="{{ ViewClassNamesConstants::BT_SM_CT }}"
                   data-url="{{ $prmCreateUrl }}"
                   data-ajax-popup="true"
                   data-size="lg"
                   data-bs-toggle="tooltip"
                   title="{{ $prmCreateTooltip }}"
                   data-title="{{ $prmCreateTitle }}"
                   data-guard-msg="{{ base64_encode($prmCreateGuardMsg) }}">
                    <i class="{{ ViewClassNamesConstants::TI_PLS_LG }}"></i>
                </a>
            </div>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/promotions/create.js') }}" defer></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::CM12 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    @role('company')
                                        <th>{{ __('Employee Name') }}</th>
                                    @endrole
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Promotion Title') }}</th>
                                    <th>{{ __('Promotion Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit promotion') || Gate::check('delete promotion'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @php
                                    $promotionEditScriptPushed = $promotionEditScriptPushed ?? false;
                                    $promotionDeleteScriptPushed = $promotionDeleteScriptPushed ?? false;
@endphp
                                @foreach ($promotions as $promotion)
                                    @php
                                        try {
                                            $promotionId = isset($promotion) && !empty(data_get($promotion, 'id')) ? data_get($promotion, 'id') : null;
                                            $employeeName = !empty(data_get($promotion, 'employee.name')) ? data_get($promotion, 'employee.name') : __('Failed to get employee name');
                                            $designationName = !empty(data_get($promotion, 'designation.name')) ? data_get($promotion, 'designation.name') : __('Failed to get designation name');
                                            $promotionTitle = data_get($promotion, 'promotion_title') ?? __('Failed to get promotion title');
                                            $promotionDateRaw = data_get($promotion, 'promotion_date');
                                            $promotionDateSafe = !empty($promotionDateRaw) ? $user?->dateFormat($promotionDateRaw) : __('No promotion date could be retrieved.');
                                            $promotionDesc = data_get($promotion, 'description') ?? __('Failed to get promotion description');
                                        } catch (\Throwable $e) {
                                            \Log::error('promotions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        @role('company')
                                            <td>{{ $employeeName }}</td>
                                        @endrole
                                        <td>{{ $designationName }}</td>
                                        <td>{{ $promotionTitle }}</td>
                                        <td>{{ $promotionDateSafe }}</td>
                                        <td>{{ $promotionDesc }}</td>
                                        @if(Gate::check('edit promotion') || Gate::check('delete promotion'))
                                            <td>
                                                @can('edit promotion')
                                                    @php
                                                        try {
                                                            $prmEditBaseName = ViewsConstants::PRM . '.edit';
                                                            $prmEditKebabName = Str::kebab($prmEditBaseName);
                                                            $prmEditResolvedName = Route::has($prmEditBaseName) ? $prmEditBaseName : (Route::has($prmEditKebabName) ? $prmEditKebabName : null);
                                                            $prmEditParams = $promotionId ? [$promotionId] : ['#'];
                                                            $prmEditUrl = ($prmEditResolvedName && $promotionId) ? route($prmEditResolvedName, $prmEditParams) : '#';
                                                            $prmEditLinkId = 'promotion-edit-link-' . ($promotionId ?? 'x');
                                                            $prmEditTitle = __('Edit Promotion');
                                                            $prmEditTooltip = __('Edit');
                                                            $prmEditGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRM, 'edit_promotion_unavailable') ?? 'Edit promotion route is unavailable. Please contact technical support or your domain administrator.';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('promotions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_INF }}">
                                                        <a href="{{ $prmEditUrl }}"
                                                           id="{{ $prmEditLinkId }}"
                                                           class="{{ ViewClassNamesConstants::BT_SM_CT }}"
                                                           data-url="{{ $prmEditUrl }}"
                                                           data-ajax-popup="true"
                                                           data-title="{{ $prmEditTitle }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ $prmEditTooltip }}"
                                                           data-guard-msg="{{ base64_encode($prmEditGuardMsg) }}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    @if(!$promotionEditScriptPushed)
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script src="{{ asset('assets/js/routes/promotions/edit.js') }}" defer></script>
                                                        @endpush
                                                        @php
 $promotionEditScriptPushed ??= true;
@endphp
                                                    @endif
                                                @endcan
                                                @can('delete promotion')
                                                    @php
                                                        try {
                                                            $prmDestroyBaseNameA = ViewsConstants::PRM . '.destroy';
                                                            $prmDestroyKebabA = Str::kebab($prmDestroyBaseNameA);
                                                            $prmDestroyBaseNameB = ViewsConstants::PRM . '.destroy';
                                                            $prmDestroyKebabB = Str::kebab($prmDestroyBaseNameB);
                                                            $prmDestroyResolvedName = Route::has($prmDestroyBaseNameA) ? $prmDestroyBaseNameA : (Route::has($prmDestroyKebabA) ? $prmDestroyKebabA : (Route::has($prmDestroyBaseNameB) ? $prmDestroyBaseNameB : (Route::has($prmDestroyKebabB) ? $prmDestroyKebabB : null)));
                                                            $prmDestroyParams = $promotionId ? [$promotionId] : ['#'];
                                                            $prmDestroyUrl = ($prmDestroyResolvedName && $promotionId) ? route($prmDestroyResolvedName, $prmDestroyParams) : '#';
                                                            $prmDeleteFormId = 'promotion-delete-form-' . ($promotionId ?? 'x');
                                                            $prmDeleteLinkId = 'promotion-delete-link-' . ($promotionId ?? 'x');
                                                            $prmDeleteGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRM, 'delete_promotion_unavailable') ?? 'Delete promotion route is unavailable. Please contact technical support or your domain administrator.';
                                                            $areYouSureMsg = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                                            $irreversibleMsg = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('promotions/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $prmDestroyUrl, 'id' => $prmDeleteFormId]) !!}
                                                            <a href="#"
                                                               id="{{ $prmDeleteLinkId }}"
                                                               class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                               data-url="{{ $prmDestroyUrl }}"
                                                               data-form-id="{{ $prmDeleteFormId }}"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('Delete') }}"
                                                               data-guard-msg="{{ base64_encode($prmDeleteGuardMsg) }}"
                                                               data-confirm="{{ __($areYouSureMsg) }}|{{ __($irreversibleMsg) }}"
                                                               data-confirm-yes="document.getElementById('{{ $prmDeleteFormId }}').submit();">
                                                                <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @if(!$promotionDeleteScriptPushed)
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script src="{{ asset('assets/js/routes/promotions/delete.js') }}" defer></script>
                                                        @endpush
                                                        @php
 $promotionDeleteScriptPushed ??= true;
@endphp
                                                    @endif
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
