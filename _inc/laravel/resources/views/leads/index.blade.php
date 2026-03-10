@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Leads') }} @if(!empty($pipeline)) - {{ data_get($pipeline,'name',__('No pipeline name available')) }} @endif
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/leads/lang/order.js') }}"></script>
    <script defer src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/leads/order.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Lead') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $changePipelineRoute = VW::DL . '.change.pipeline';
@endphp
        {{ Form::open([
            'route' => Route::has($changePipelineRoute) ? $changePipelineRoute : null,
            'url'   => Route::has($changePipelineRoute) ? null : URL::to(trim(VW::DL,'/').'/change/pipeline'),
            'id'    => 'change-pipeline',
            'class' => VC::BT_SM
        ]) }}
            {{ Form::select(
                'default_pipeline_id',
                Utility::isFilled($pipelines) ? $pipelines : ['' => __('No pipeline available' ?? [])],
                data_get($pipeline,'id',''),
                ['class'=> VC::FM_CT_SL.' me-4','id'=>'default_pipeline_id']
            ) }}
        {{ Form::close() }}

        @can('view lead')
            @php
                try {
                    $listGuard = Utility::fetchLinkMessage($lang, VW::DL, 'deals_list_route_unavailable')
                        ?? 'List view route is unavailable. Please contact technical support or your domain administrator.';
                    $listHref = Route::has(VW::LD.'.list') ? route(VW::LD.'.list') : '#';
                } catch (\Throwable $e) {
                    \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $listHref }}"
               class="{{ VC::BT_SM_PM }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($listGuard) }}"
               data-bs-toggle="tooltip"
               title="{{ __('List View') }}">
                <i class="{{ VC::TI_LT }}"></i>
            </a>
            <script defer>
                (function () {
                    try {
                        var a = document.querySelector('a[href="{{ $listHref }}"].{{ VC::BT_SM_PM }}');
                        if (a && "{{ $listHref }}" === "#") {
                            a.addEventListener('click', function (e) { e.preventDefault(); window.svShowGuard ? svShowGuard(a.getAttribute('data-guard-msg')) : alert(a.getAttribute('data-guard-msg')); });
                        }
                    } catch (_) {}
                })();
            </script>
        @endcan

        @can('create lead')
            @php
                try {
                    $createGuard = Utility::fetchLinkMessage($lang, VW::DL, 'deals_create_route_unavailable')
                        ?? 'Create route is unavailable. Please contact technical support or your domain administrator.';
                    $createHref = Route::has(VW::LD.'.create') ? route(VW::LD.'.create') : '#';
                } catch (\Throwable $e) {
                    \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="#"
               data-size="lg"
               data-url="{{ $createHref }}"
               data-ajax-popup="true"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createGuard) }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create New Lead') }}"
               data-title="{{ __('Create Lead') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            <script defer>
                (function () {
                    try {
                        var a = document.querySelector('a[data-url="{{ $createHref }}"].{{ VC::BT_SM_PM }}');
                        if (a && "{{ $createHref }}" === "#") {
                            a.addEventListener('click', function (e) { e.preventDefault(); window.svShowGuard ? svShowGuard(a.getAttribute('data-guard-msg')) : alert(a.getAttribute('data-guard-msg')); });
                        }
                    } catch (_) {}
                })();
            </script>
        @endcan
    </div>

    <script defer>
        (function () {
            if (window.svShowGuard) return;
            const RG = window.RouteGuard || {};
            window.svShowGuard = RG.showToast || function (msg) {
                var m = (msg || '').trim() || 'Route is unavailable. Please contact technical support or your domain administrator.';
                alert(m);
            };
        })();
    </script>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            @php
                $containersIds = [];
                $lead_stages   = [];
                try {
                    $lead_stages   = data_get($pipeline ?? null, 'leadStages', []);
                    $containersIds = [];
                    if (is_iterable($lead_stages)) {
                        foreach ($lead_stages as $lead_stage) {
                            $sid = data_get($lead_stage,'id');
                            if (isset($sid)) $containersIds[] = 'task-list-'.$sid;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp

            <div class="{{ VC::RW }} kanban-wrapper horizontal-scroll-cards"
                 data-containers='{!! json_encode($containersIds) !!}'
                 data-plugin="dragula">

                @if(is_iterable($lead_stages))
                    @foreach($lead_stages as $lead_stage)
                        @php
                            try {
                                $stageId   = (string) data_get($lead_stage,'id','0');
                                $stageName = (string) data_get($lead_stage,'name',__('No stage name available'));
                                $leads     = method_exists($lead_stage,'lead') ? ($lead_stage->lead() ?? []) : [];
                                $leadCount = is_countable($leads) ? count($leads) : 0;
                            } catch (\Throwable $e) {
                                \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp

                        <div class="col">
                            <div class="{{ VC::CD }}">
                                <div class="{{ VC::CD_HD }}">
                                    <div class="{{ VC::FEND }}">
                                        <span class="{{ VC::BT_SM_PM }} btn-icon count">{{ $leadCount }}</span>
                                    </div>
                                    <h4 class="{{ VC::MB0 }}">{{ $stageName }}</h4>
                                </div>

                                <div class="{{ VC::CD_BD }} kanban-box" id="task-list-{{ $stageId }}" data-id="{{ $stageId }}">
                                    @if(is_iterable($leads))
                                        @foreach($leads as $lead)
                                            @php
                                                try {
                                                    $leadId   = (string) data_get($lead,'id','0');
                                                    $leadName = (string) data_get($lead,'name',__('No lead name available'));
                                                    $isActive = (int) data_get($lead,'is_active',0) === 1;

                                                    $labels   = method_exists($lead,'labels')   ? ($lead->labels()   ?? []) : [];
                                                    $products = method_exists($lead,'products') ? ($lead->products() ?? []) : [];
                                                    $sources  = method_exists($lead,'sources')  ? ($lead->sources()  ?? []) : [];
                                                    $leadUsers= data_get($lead,'users',[]);
                                                    $guardShow   = Utility::fetchLinkMessage($lang, VW::DL, 'deals_show_route_unavailable')   ?? 'Show route is unavailable. Please contact technical support or your domain administrator.';
                                                    $guardEdit   = Utility::fetchLinkMessage($lang, VW::DL, 'deals_edit_route_unavailable')   ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                                    $guardLabels = Utility::fetchLinkMessage($lang, VW::DL, 'deals_labels_route_unavailable') ?? 'Labels route is unavailable. Please contact technical support or your domain administrator.';
                                                    $guardDelete = Utility::fetchLinkMessage($lang, 'generics', 'deal_destroy_route_unavailable') ?? 'Delete route is unavailable. Please contact technical support or your domain administrator.';
                                                } catch (\Throwable $e) {
                                                    \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp

                                            <div class="{{ VC::CD }}" data-id="{{ $leadId }}">
                                                <div class="{{ VC::PT3 }} {{ VC::PS3 }}">
                                                    @if(is_iterable($labels) && (is_countable($labels) ? count($labels) : true))
                                                        @foreach($labels as $label)
                                                            <div class="{{ VC::BDG_XS }} bg-{{ data_get($label,'color','secondary') }} p-2 px-3 rounded">
                                                                {{ data_get($label,'name',__('No label name available')) }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>

                                                <div class="{{ VC::CD_HD }} border-0 pb-0 position-relative">
                                                    <h5>
                                                        @can('view lead')
                                                            @php
                                                                $showHref = ($isActive && Route::has(VW::LD.'.show')) ? route(VW::LD.'.show',$leadId) : '#';
@endphp
                                                            <a href="{{ $showHref }}"
                                                               class="fc-daygrid-event"
                                                               data-sv-localized="true"
                                                               data-guard-msg="{{ base64_encode($guardShow) }}">
                                                                {{ $leadName }}
                                                            </a>
                                                        @else
                                                            <a href="#">{{ $leadName }}</a>
                                                        @endcan
                                                    </h5>

                                                    <div class="card-header-right">
                                                        @if(($user?->type ?? '') !== 'client')
                                                            <div class="btn-group card-option">
                                                                <button type="button" class="{{ VC::BT }} dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <i class="{{ VC::TD_DOTV }}"></i>
                                                                </button>
                                                                <div class="{{ VC::DRP_MN_EM }}">
                                                                    @can('edit lead')
                                                                        @php
                                                                            $labelsHref = Route::has(VW::LD.'.labels') ? URL::to(VW::LD.'/'.$leadId.'/labels') : '#';
                                                                            $editHref   = Route::has(VW::LD.'.edit')   ? URL::to(VW::LD.'/'.$leadId.'/edit')     : '#';
@endphp
                                                                        <a href="#!"
                                                                           data-size="md"
                                                                           data-url="{{ $labelsHref }}"
                                                                           data-ajax-popup="true"
                                                                           class="{{ VC::DRP_IT }}"
                                                                           data-sv-localized="true"
                                                                           data-guard-msg="{{ base64_encode($guardLabels) }}">
                                                                            <i class="ti ti-bookmark"></i>
                                                                            <span>{{ __('Labels') }}</span>
                                                                        </a>
                                                                        <a href="#!"
                                                                           data-size="lg"
                                                                           data-url="{{ $editHref }}"
                                                                           data-ajax-popup="true"
                                                                           class="{{ VC::DRP_IT }}"
                                                                           data-sv-localized="true"
                                                                           data-guard-msg="{{ base64_encode($guardEdit) }}">
                                                                            <i class="{{ VC::TI_PC }}"></i>
                                                                            <span>{{ __('Edit') }}</span>
                                                                        </a>
                                                                    @endcan
                                                                    @can('delete lead')
                                                                        @php
                                                                            try {
                                                                                $deleteRouteExists = Route::has(VW::LD.'.destroy');
                                                                                $deleteAction = $deleteRouteExists ? route(VW::LD.'.destroy',$leadId) : '#';
                                                                                $deleteConfirm = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')
                                                                                    .'|' .
                                                                                    __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                                            } catch (\Throwable $e) {
                                                                                \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                            }
@endphp
                                                                        @if($deleteRouteExists)
                                                                            {!! Form::open([
                                                                                'method' => 'DELETE',
                                                                                'route'  => [VW::LD.'.destroy',$leadId],
                                                                                'id'     => 'delete-form-'.$leadId
                                                                            ]) !!}
                                                                                <a href="#!"
                                                                                   class="{{ VC::DRP_IT }} bs-pass-para"
                                                                                   data-sv-localized="true"
                                                                                   data-guard-msg="{{ base64_encode($guardDelete) }}"
                                                                                   data-confirm="{{ $deleteConfirm }}"
                                                                                   data-confirm-yes="document.getElementById('delete-form-{{ $leadId }}').submit();">
                                                                                    <i class="{{ VC::TI_ARC }}"></i>
                                                                                    <span>{{ __('Delete') }}</span>
                                                                                </a>
                                                                            {!! Form::close() !!}
                                                                        @else
                                                                            <a href="#!"
                                                                               class="{{ VC::DRP_IT }}"
                                                                               data-sv-localized="true"
                                                                               data-guard-msg="{{ base64_encode($guardDelete) }}">
                                                                                <i class="{{ VC::TI_ARC }}"></i>
                                                                                <span>{{ __('Delete') }}</span>
                                                                            </a>
                                                                        @endif
                                                                    @endcan
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                @php
                                                    $productsCount = is_countable($products) ? count($products) : 0;
                                                    $sourcesCount  = is_countable($sources)  ? count($sources)  : 0;
@endphp

                                                <div class="{{ VC::CD_BD }}">
                                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                                        <ul class="list-inline {{ VC::MB0 }}">
                                                            <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Product') }}">
                                                                <i class="f-16 {{ VC::TX_PM }} ti ti-shopping-cart"></i> {{ $productsCount }}
                                                            </li>
                                                            <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Source') }}">
                                                                <i class="f-16 {{ VC::TX_PM }} ti ti-social"></i>{{ $sourcesCount }}
                                                            </li>
                                                        </ul>
                                                        <div class="user-group">
                                                            @if(is_iterable($leadUsers))
                                                                @foreach($leadUsers as $u)
                                                                    @php
                                                                        try {
                                                                            $uName = (string) data_get($u,'name',__('No user name available'));
                                                                            $uAvatar = data_get($u,'avatar');
                                                                            $src = $uAvatar ? asset('/storage/uploads/avatar/'.$uAvatar) : asset('storage/uploads/avatar/avatar.png');
                                                                        } catch (\Throwable $e) {
                                                                            \Log::error('leads/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                        }
@endphp
                                                                    <img src="{{ $src }}" alt="image" data-bs-toggle="tooltip" title="{{ $uName }}">
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script defer>
                                                (function () {
                                                    try {
                                                        var root = document.querySelector('[data-id="{{ $leadId }}"]');
                                                        if (!root) return;
                                                        var showA = root.querySelector('h5 a.fc-daygrid-event');
                                                        if (showA && showA.getAttribute('href') === '#') {
                                                            showA.addEventListener('click', function (e) { e.preventDefault(); window.svShowGuard ? svShowGuard(showA.getAttribute('data-guard-msg')) : alert(showA.getAttribute('data-guard-msg')); });
                                                        }
                                                        @can('edit lead')
                                                            var labelsA = root.querySelector('a.dropdown-item[data-url="{{ $labelsHref ?? '' }}"]');
                                                            if (labelsA && labelsA.getAttribute('data-url') === '#') {
                                                                labelsA.addEventListener('click', function (e) { e.preventDefault(); window.svShowGuard ? svShowGuard(labelsA.getAttribute('data-guard-msg')) : alert(labelsA.getAttribute('data-guard-msg')); });
                                                            }
                                                            var editA = root.querySelector('a.dropdown-item[data-url="{{ $editHref ?? '' }}"]');
                                                            if (editA && editA.getAttribute('data-url') === '#') {
                                                                editA.addEventListener('click', function (e) { e.preventDefault(); window.svShowGuard ? svShowGuard(editA.getAttribute('data-guard-msg')) : alert(editA.getAttribute('data-guard-msg')); });
                                                            }
                                                        @endcan
                                                        @can('delete lead')
                                                            @if(!($deleteRouteExists ?? false))
                                                                var delA = root.querySelector('a.dropdown-item span')?.closest('a.dropdown-item');
                                                                if (delA) {
                                                                    delA.addEventListener('click', function (e) { e.preventDefault(); window.svShowGuard ? svShowGuard(delA.getAttribute('data-guard-msg')) : alert(delA.getAttribute('data-guard-msg')); });
                                                                }
                                                            @endif
                                                        @endcan
                                                    } catch (_) {}
                                                })();
                                            </script>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>
    </div>
@endsection
