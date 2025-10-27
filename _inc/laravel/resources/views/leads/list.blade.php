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
    use Illuminate\Support\Str;

    $authUser = Auth::user();
    $lang = Utility::fetchUserLang(user: $authUser);
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    function resolveRoute(string $base): ?string {
        $k = Str::kebab($base);
        return Route::has($base) ? $base : (Route::has($k) ? $k : null);
    }

    $dashBase = 'dashboard';
    $dashUrl = Route::has($dashBase) ? route($dashBase) : '#';
    $dashGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $indexBase = VW::LD . '.index';
    $indexResolved = resolveRoute($indexBase);
    $indexUrl = $indexResolved ? route($indexResolved) : '#';
    $indexGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'lead_index_route_unavailable') : 'Lead index route is unavailable. Please contact technical support or your domain administrator.') ?? 'Lead index route is unavailable. Please contact technical support or your domain administrator.');

    $createBase = VW::LD . '.create';
    $createResolved = resolveRoute($createBase);
    $createUrl = $createResolved ? route($createResolved) : '#';
    $createGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'lead_create_route_unavailable') : 'Create lead route is unavailable. Please contact technical support or your domain administrator.') ?? 'Create lead route is unavailable. Please contact technical support or your domain administrator.');

    $pipelineName = !empty($pipeline) ? (data_get($pipeline,'name', __('No name for pipeline available'))) : null;
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Leads') }}@if(!empty($pipelineName)) - {{ $pipelineName }}@endif
@endsection

@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/leads/lang/listGuard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/leads/listGuard.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-guard-msg="{{ $dashGuard }}"
           data-sv-localized="true"
           class="lead-route-guard"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Lead') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a href="{{ $indexUrl }}"
           data-url="{{ $indexUrl }}"
           data-guard-msg="{{ $indexGuard }}"
           data-sv-localized="true"
           data-bs-toggle="tooltip"
           title="{{ __('Kanban View') }}"
           class="{{ VC::BT_SM_PM }} lead-route-guard">
            <i class="ti ti-layout-grid"></i>
        </a>
        <a href="#"
           data-size="lg"
           data-url="{{ $createUrl }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Create New Lead') }}"
           data-guard-msg="{{ $createGuard }}"
           data-sv-localized="true"
           class="{{ VC::BT_SM_PM }} lead-route-guard">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section(YW::ADM_CTT)
    @if(!empty($pipeline))
        <div class="{{ VC::RW }}">
            <div class="col-xl-12">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Stage') }}</th>
                                    <th>{{ __('Users') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(Utility::isFilled($leads) ?? [])
                                    @foreach ($leads as $lead)
                                        @php
                                            $lid = data_get($lead,'id');
                                            $isActive = (bool) data_get($lead,'is_active',false);
                                            $name = data_get($lead,'name', __('No name available'));
                                            $subject = data_get($lead,'subject', __('No subject available'));
                                            $stageName = data_get($lead,'stage.name','-');
                                            $showBase = VW::LD . '.show';
                                            $showResolved = resolveRoute($showBase);
                                            $showUrl = ($showResolved && $lid) ? route($showResolved,$lid) : '#';
                                            $showGuard = __((Utility::fetchLinkMessage($lang,VW::LD,'leads_show_route_unavailable') ?? 'Show lead route is unavailable. Please contact technical support or your domain administrator.'));
                                        @endphp
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td>{{ $subject }}</td>
                                            <td>{{ $stageName }}</td>
                                            <td>
                                                @foreach(data_get($lead,'users',[]) as $assignee)
                                                    <a href="#" class="{{ VC::BT_SM }} p-0 rounded-circle">
                                                        <img alt="image"
                                                             data-bs-toggle="tooltip"
                                                             title="{{ data_get($assignee,'name','') }}"
                                                             src="{{ data_get($assignee,'avatar')
                                                                    ? asset('/storage/uploads/avatar/'.data_get($assignee,'avatar'))
                                                                    : asset('/storage/uploads/avatar/avatar.png') }}"
                                                             class="rounded-circle" width="25" height="25">
                                                    </a>
                                                @endforeach
                                            </td>
                                            @if(($authUser?->type ?? null) !== 'client')
                                                <td class="Action">
                                                    <span>
                                                        @can('view lead')
                                                            @if($isActive)
                                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                                    <a href="{{ $showUrl }}"
                                                                       data-url="{{ $showUrl }}"
                                                                       data-guard-msg="{{ $showGuard }}"
                                                                       data-sv-localized="true"
                                                                       class="{{ VC::BT_SM_FL_CT }} lead-route-guard"
                                                                       data-size="xl"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('View') }}"
                                                                       data-title="{{ __('Lead Detail') }}">
                                                                        <i class="{{ VC::TI_EYE_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endcan
                                                        @can('edit lead')
                                                            @php
                                                                $editBase = VW::LD . '.edit';
                                                                $editResolved = resolveRoute($editBase);
                                                                $editUrl = ($editResolved && $lid) ? route($editResolved,$lid) : '#';
                                                                $editGuard = __((Utility::fetchLinkMessage($lang,VW::LD,'leads_edit_route_unavailable') ?? 'Edit lead route is unavailable. Please contact technical support or your domain administrator.'));
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_FL_CT }} lead-route-guard"
                                                                   data-url="{{ $editUrl }}"
                                                                   data-ajax-popup="true"
                                                                   data-size="xl"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Edit') }}"
                                                                   data-title="{{ __('Lead Edit') }}"
                                                                   data-guard-msg="{{ $editGuard }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete lead')
                                                            @php
                                                                $destroyBase = VW::LD . '.destroy';
                                                                $destroyResolved = resolveRoute($destroyBase);
                                                                $destroyUrl = ($destroyResolved && $lid) ? route($destroyResolved,$lid) : '#';
                                                                $destroyGuard = __((Utility::fetchLinkMessage($lang,VW::LD,'leads_destroy_route_unavailable') ?? 'Delete lead route is unavailable. Please contact technical support or your domain administrator.'));
                                                                $confirmTitle = __((Utility::fetchLinkMessage($lang,'generics','are_you_sure') ?? 'Are You Sure?'));
                                                                $confirmBody  = __((Utility::fetchLinkMessage($lang,'generics','irreversible_action') ?? 'This action can not be undone. Do you want to continue?'));
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Collective\Html\FormFacade::open([
                                                                    'method' => 'DELETE',
                                                                    'route'  => [$destroyResolved ?? $destroyBase, $lid],
                                                                    'id'     => 'delete-form-'.$lid
                                                                ]) !!}
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_CT_PR }} lead-route-guard"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Delete') }}"
                                                                   data-url="{{ $destroyUrl }}"
                                                                   data-guard-msg="{{ $destroyGuard }}"
                                                                   data-sv-localized="true"
                                                                   data-confirm="{{ $confirmTitle }}|{{ $confirmBody }}"
                                                                   data-confirm-yes="document.getElementById('delete-form-{{ $lid }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                            </div>
                                                        @endcan
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="font-style">
                                        <td colspan="6" class="text-center">{{ __('No data available in table') }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center text-muted py-4">{{ __('Failed to mount this view because the pipeline was not available.') }}</div>
    @endif
@endsection
