@php
    try {
$user = Auth::user();

        $hasFetchUserLang    = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);

        $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();
    } catch (\Throwable $e) {
        \Log::error('labels/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Labels') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Labels') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    @can('create label')
        @php
            try {
                $createBase = VW::LBL.'.create';
                $createUrl  = Route::has($createBase) ? route($createBase) : '#';
                $createMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LBL, 'create_label_route_unavailable') : null)
                              ?? __('Create label route is unavailable. Please contact technical support or your domain administrator.');
            } catch (\Throwable $e) {
                \Log::error('labels/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <div class="{{ VC::FEND }}">
            <a href="#"
               class="{{ VC::BT_SM_PM }}"
               data-size="md"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="{{ __('Create Labels') }}"
               data-guard-msg="{{ base64_encode($createMsg) }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        </div>
    @endcan
@endsection

@section(YW::ADM_CTT)
    @if(!$user)
        <div class="{{ VC::CD }}">
            <div class="{{ VC::CD_BD }}">
                <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">
                    {{ __('The current user context was not available; data could not be displayed.') }}
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="{{ VC::C3 }}">
                @include('layouts.crm_setup')
            </div>
            <div class="{{ VC::C9 }}">
                <div class="row {{ VC::JCC }}">
                    <div class="p-3 {{ VC::CD }}">
                        <ul class="{{ VC::NAV_PL }} {{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
                            @php
	try {
		($i = 0)
		                            @forelse(($pipelines ?? []) as $key => $pipeline)
		                                @php
		                                    $pName = isset($pipeline['name']) && $pipeline['name'] !== '' ? $pipeline['name'] : __('Unnamed pipeline');
	} catch (\Throwable $e) {
		\Log::error('labels/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
                                <li class="{{ VC::NV_IT }}" role="presentation">
                                    <button class="{{ VC::NV_LK }} @if($i===0) active @endif"
                                            id="pills-pipeline-tab-{{ $key }}"
                                            data-bs-toggle="pill"
                                            data-bs-target="#tab{{ $key }}"
                                            type="button"
                                            role="tab"
                                            aria-controls="tab{{ $key }}"
                                            aria-selected="{{ $i===0 ? 'true' : 'false' }}">
                                        {{ $pName }}
                                    </button>
                                </li>
                                @php
	try {
		($i++)
		                            @empty
		                                <li class="{{ VC::NV_IT }}">
		                                    <span class="{{ VC::NV_LK }}">{{ __('No pipelines available.') }}</span>
		                                </li>
		                            @endforelse
		                        </ul>
		                    </div>

		                    <div class="{{ VC::CD }}">
		                        <div class="{{ VC::CD_BD }}">
		                            <div class="tab-content" id="pills-tabContent">
		                                @php($i = 0)
		                                @forelse(($pipelines ?? []) as $key => $pipeline)
		                                    @php
		                                        $labels = isset($pipeline['labels']) && is_iterable($pipeline['labels']) ? $pipeline['labels'] : [];
	} catch (\Throwable $e) {
		\Log::error('labels/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
                                    <div class="{{ VC::TAB_FD_SH }} @if($i===0) active @endif"
                                         id="tab{{ $key }}"
                                         role="tabpanel"
                                         aria-labelledby="pills-pipeline-tab-{{ $key }}">
                                        <ul class="{{ VC::LGRP }} sortable">
                                            @forelse($labels as $label)
                                                @php
                                                    try {
                                                        $lid      = isset($label->id) ? (string)$label->id : '';
                                                        $lname    = isset($label->name) && $label->name !== '' ? $label->name : __('Unnamed label');

                                                        $editBase = VW::LBL.'.edit';
                                                        $editUrl  = (Route::has($editBase) && $lid !== '') ? route($editBase, $lid) : '#';
                                                        $editMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LBL, 'edit_label_route_unavailable') : null)
                                                                    ?? __('Edit label route is unavailable. Please contact technical support or your domain administrator.');

                                                        $destroyBase = VW::LBL.'.destroy';
                                                        $destroyUrl  = (Route::has($destroyBase) && $lid !== '') ? route($destroyBase, $lid) : '#';
                                                        $destroyMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LBL, 'destroy_label_route_unavailable') : null)
                                                                       ?? __('Delete label route is unavailable. Please contact technical support or your domain administrator.');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('labels/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <li class="{{ VC::LGI }}" data-id="{{ $lid }}">
                                                    <span class="{{ VC::TXSM }} text-dark">{{ $lname }}</span>
                                                    <span class="{{ VC::FEND }}">
                                                        @can('edit label')
                                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_FL_CT }}"
                                                                   data-url="{{ $editUrl }}"
                                                                   data-ajax-popup="true"
                                                                   data-size="md"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Edit') }}"
                                                                   data-title="{{ __('Edit Labels') }}"
                                                                   data-guard-msg="{{ base64_encode($editMsg) }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @if(!empty($labels))
                                                            @can('delete label')
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Form::open([
                                                                        'method'            => 'DELETE',
                                                                        'url'               => $destroyUrl,
                                                                        'id'                => 'delete-form-'.$lid,
                                                                        'data-url'          => $destroyUrl,
                                                                        'data-guard-msg'    => $destroyMsg,
                                                                        'data-sv-localized' => 'true',
                                                                    ]) !!}
                                                                        <a href="#"
                                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                                           data-bs-toggle="tooltip"
                                                                           title="{{ __('Delete') }}"
                                                                           data-confirm="{{ __( ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?' ) }}|{{ __( ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?' ) }}">
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                        @endif
                                                    </span>
                                                </li>
                                            @empty
                                                <li class="{{ VC::LGI }}">{{ __('No labels found for this pipeline.') }}</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    @php($i++)
                                @empty
                                    <div class="{{ VC::TAB_FD_SH }} active">
                                        <div class="{{ VC::ALT_INF_MB0 }}" role="alert">{{ __('There are no pipelines to display.') }}</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/labels/index.js') }}"></script>
@endpush
