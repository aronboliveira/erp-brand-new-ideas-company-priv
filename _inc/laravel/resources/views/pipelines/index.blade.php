@php
    try {
$user = Auth::user();
        $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
        $canFetchMsg = is_callable([Utility::class, 'fetchLinkMessage']);

        $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
        $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

        $items = [];
        if (is_array($pipelines ?? null) && count($pipelines)) {
            $items = $pipelines;
        } elseif (($pipelines ?? null) instanceof Collection && $pipelines->isNotEmpty()) {
            $items = $pipelines;
        }

        $areYouSure       = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : 'Are You Sure?') ?? __('Are You Sure?');
        $irreversibleAct  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : 'This action can not be undone. Do you want to continue?') ?? __('This action can not be undone. Do you want to continue?');
    } catch (\Throwable $e) {
        \Log::error('pipelines/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Pipelines') }}
@endsection

@section(YD::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ base64_encode($dashGuard) }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Pipelines') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create pipeline')
            @php
                $createUrl   = Route::has(VW::PPL.'.create') ? route(VW::PPL.'.create') : '#';
                $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PPL, 'create_pipeline_unavailable') : 'Create Pipeline route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Pipeline route is unavailable. Please contact technical support or your domain administrator.');
@endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-size="md"
               data-title="{{ __('Create New Pipeline') }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createGuard) }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
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
                                    <th>{{ __('Pipeline') }}</th>
                                    <th width="250px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(empty($items))
                                    <tr>
                                        <td colspan="2">
                                            <div class="{{ VC::TXCT }}">
                                                <p>{{ __('No pipelines found.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($items as $pipeline)
                                        @php
                                            $pid        = $pipeline->id ?? null;
                                            $name       = $pipeline->name ?? __('No pipeline name available');
@endphp
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td class="Action">
                                                <span>
                                                    @can('edit pipeline')
                                                        @php
                                                            try {
                                                                $editUrlNamed = Route::has(VW::PPL.'.edit') ? route(VW::PPL.'.edit', $pid) : null;
                                                                $editUrlPath  = URL::to(VW::PPL.'/'.$pid.'/edit');
                                                                $editUrl      = $editUrlNamed ?? ($editUrlPath ?: '#');
                                                                $editGuard    = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PPL, 'edit_pipeline_unavailable') : 'Edit Pipeline route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Pipeline route is unavailable. Please contact technical support or your domain administrator.');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('pipelines/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a href="{{ $editUrl }}"
                                                            class="{{ VC::BT_SM_FL_CT }}"
                                                            data-url="{{ $editUrl }}"
                                                            data-ajax-popup="true"
                                                            data-size="md"
                                                            data-title="{{ __('Edit Pipeline') }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ base64_encode($editGuard) }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @if((is_countable($items) ? count($items) : 0) > 1)
                                                        @can('delete pipeline')
                                                            @php
                                                                try {
                                                                    $delUrl   = Route::has(VW::PPL.'.destroy') ? route(VW::PPL.'.destroy', $pid) : '#';
                                                                    $delGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PPL, 'delete_pipeline_unavailable') : 'Delete Pipeline route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Pipeline route is unavailable. Please contact technical support or your domain administrator.');
                                                                    $formId   = 'delete-pipeline-form-'.$pid;
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('pipelines/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $formId, 'data-url'=>$delUrl, 'data-sv-localized'=>'true', 'data-guard-msg'=>$delGuard]) !!}
                                                                    <a href="{{ $delUrl }}"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-url="{{ $delUrl }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ base64_encode($delGuard) }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ $areYouSure }}|{{ $irreversibleAct }}"
                                                                    data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/pipelines/index.js') }}"></script>
@endpush
