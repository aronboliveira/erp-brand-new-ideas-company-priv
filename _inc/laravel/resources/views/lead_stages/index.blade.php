@php
    try {
$user = Auth::user();

        $hasFetchUserLang    = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
        $lang = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();

        $createResolved  = Route::has(VW::LD_STG . '.create') ? route(VW::LD_STG . '.create') : '#';
        $createGuardMsg  = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LD_STG, 'create_lead_stage_route_unavailable') : null)
            ?? __('Create lead stage route is unavailable. Please contact technical support or your domain administrator.');

        $orderResolved   = Route::has(VW::LD_STG . '.order') ? route(VW::LD_STG . '.order') : '#';
        $orderGuardMsg   = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LD_STG, 'order_lead_stage_route_unavailable') : null)
            ?? __('Reordering lead stages is unavailable. Please contact technical support or your domain administrator.');

        $pipelinesIsList = (is_array($pipelines ?? null) && count($pipelines ?? []) > 0)
            || (($pipelines ?? null) instanceof Collection && $pipelines->isNotEmpty());
    } catch (\Throwable $e) {
        \Log::error('lead_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Lead Stages') }}
@endsection

@push(ST::ADM_SCR_PG)
    <script async src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/leads/stages/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/leads/stages/index.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Lead Stage') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a href="#"
           data-size="md"
           data-url="{{ $createResolved }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Create Lead Stage') }}"
           class="{{ VC::BT_SM_PM }}"
           data-guard-msg="{{ base64_encode($createGuardMsg) }}"
           data-sv-localized="true">
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C3 }}">
            @include('layouts.crm_setup')
        </div>
        <div class="{{ VC::C9 }}">
            <div class="{{ VC::RW }} justify-content-center">
                <div class="p-3 {{ VC::CD }}">
                    @if($pipelinesIsList)
                        <ul class="{{ VC::NAV_PL }} {{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
                            @php $i = 0; @endphp
                            @foreach($pipelines as $key => $pipeline)
                                @php
                                    try {
                                        $pName = isset($pipeline['name']) && $pipeline['name'] !== '' ? $pipeline['name'] : __('Unnamed pipeline');
                                    } catch (\Throwable $e) {
                                        \Log::error('lead_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        $pName = __('Unnamed pipeline');
                                    }
                                @endphp
                                <li class="{{ VC::NV_IT }}" role="presentation">
                                    <button class="{{ VC::NV_LK }} @if($i===0) active @endif"
                                            id="tab-btn-{{ $key }}"
                                            data-bs-toggle="pill"
                                            data-bs-target="#tab{{ $key }}"
                                            type="button"
                                            role="tab">
                                        {{ $pName }}
                                    </button>
                                </li>
                                @php $i++; @endphp
                            @endforeach
		                        </ul>
		                    @else
		                        <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">
		                            {{ __('Lead pipelines were not available or failed to load.') }}
		                        </div>
		                    @endif
		                </div>

		                <div class="{{ VC::CD }}">
		                    <div class="{{ VC::CD_BD }}">
		                        @if($pipelinesIsList)
		                            <div class="tab-content" id="pills-tabContent">
		                                @php $i = 0; @endphp
		                                @foreach($pipelines as $key => $pipeline)
                                                    @php
                                                        try {
                                                            $leadStages = $pipeline['lead_stages'] ?? [];
                                                            $leadStagesIsList = (is_array($leadStages ?? null) && count($leadStages ?? []) > 0)
                                                                || (($leadStages ?? null) instanceof Collection && $leadStages->isNotEmpty());
                                                        } catch (\Throwable $e) {
                                                            \Log::error('lead_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            $leadStages = [];
                                                            $leadStagesIsList = false;
                                                        }
                                                    @endphp
                                    <div class="{{ VC::TAB_FD_SH }} @if($i===0) active @endif"
                                         id="tab{{ $key }}"
                                         role="tabpanel"
                                         aria-labelledby="tab-btn-{{ $key }}">
                                        @if($leadStagesIsList)
                                            <ul class="list-unstyled {{ VC::LGRP }} sortable stage"
                                                data-sort-url="{{ $orderResolved }}"
                                                data-guard-msg="{{ base64_encode($orderGuardMsg) }}"
                                                data-sv-localized="true">
                                                @foreach ($leadStages as $stage)
                                                    @php
                                                        $sid   = isset($stage->id) ? (string)$stage->id : '';
                                                        $sname = isset($stage->name) && $stage->name !== '' ? $stage->name : __('Unnamed stage');
@endphp
                                                    <li class="{{ VC::DFL_AIC_JCB_IT }}"
                                                        data-id="{{ $sid }}">
                                                        <h6 class="{{ VC::MB0 }}">
                                                            <i class="{{ VC::TI_AR_M3 }}" data-feather="move"></i>
                                                            <span>{{ $sname }}</span>
                                                        </h6>
                                                        <span class="{{ VC::FEND }}">
                                                            @can('edit lead stage')
                                                                @php
                                                                    try {
                                                                        $editUrl = Route::has(VW::LD_STG . '.edit') && $sid !== ''
                                                                            ? route(VW::LD_STG . '.edit', $sid)
                                                                            : '#';
                                                                        $editMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LD_STG, 'edit_lead_stage_route_unavailable') : null)
                                                                            ?? __('Edit lead stage route is unavailable. Please contact technical support or your domain administrator.');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('lead_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_FL_CT }}"
                                                                       data-url="{{ $editUrl }}"
                                                                       data-ajax-popup="true"
                                                                       data-size="md"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Edit') }}"
                                                                       data-title="{{ __('Edit Lead Stages') }}"
                                                                       data-guard-msg="{{ base64_encode($editMsg) }}"
                                                                       data-sv-localized="true">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan

                                                            @php
                                                                $canDelete = auth()->user()?->can('delete lead stage') ?? false;
@endphp
                                                            @if($canDelete)
                                                                @php
                                                                    try {
                                                                        $destroyUrl = Route::has(VW::LD_STG . '.destroy') && $sid !== ''
                                                                            ? route(VW::LD_STG . '.destroy', $sid)
                                                                            : '#';
                                                                        $formId   = 'delete-form-' . ($sid === '' ? 'x' : $sid);
                                                                        $delMsg   = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LD_STG, 'delete_lead_stage_route_unavailable') : null)
                                                                            ?? __('Delete lead stage route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $confirmA = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null)
                                                                            ?? __('Are You Sure?');
                                                                        $confirmB = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null)
                                                                            ?? __('This action can not be undone. Do you want to continue?');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('lead_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Form::open([
                                                                        'method'            => 'DELETE',
                                                                        'url'               => $destroyUrl,
                                                                        'id'                => $formId,
                                                                        'data-url'          => $destroyUrl,
                                                                        'data-guard-msg'    => $delMsg,
                                                                        'data-sv-localized' => 'true'
                                                                    ]) !!}
                                                                        <a href="#"
                                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                                           data-bs-toggle="tooltip"
                                                                           title="{{ __('Delete') }}"
                                                                           data-confirm="{{ __($confirmA) }}|{{ __($confirmB) }}"
                                                                           data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="{{ VC::ALT_INF_MB0 }}" role="alert">
                                                {{ __('No lead stages were found for this pipeline.') }}
                                            </div>
                                        @endif
                                    </div>
                                    @php $i++; @endphp
                                @endforeach
                            </div>
                        @else
                            <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">
                                {{ __('Lead pipelines were not available or failed to load.') }}
                            </div>
                        @endif
                        <p class="{{ VC::MT4 }}"><strong>{{ __('Note') }} : </strong><b>{{ __('You can easily change order of lead stage using drag & drop.') }}</b></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
