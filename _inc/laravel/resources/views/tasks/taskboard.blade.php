@include('partials.helpers.route_helpers')
@php
    try {
$lang = Utility::fetchUserLang();
        $view ??= 'grid';

        $gridRoute = resolveRouteWithGuard(VW::TSKB.'.view', $lang, VW::TSK, 'task_board_view_route_unavailable', ['grid'], 'Task board unavailable.');
        $listRoute = resolveRouteWithGuard(VW::TSKB.'.view', $lang, VW::TSK, 'task_board_view_route_unavailable', ['list'], 'Task board unavailable.');

        $listLabel = __('List View') ?: __('No list label available');
        $cardLabel = __('Card View') ?: __('No card label available');
        $searchPh = __('Search by Name') ?: __('No search placeholder available');
        $newestLabel = __('Newest') ?: __('No newest label available');
        $oldestLabel = __('Oldest') ?: __('No oldest label available');
        $fromAzLabel = __('From A-Z') ?: __('No A-Z label available');
        $fromZaLabel = __('From Z-A') ?: __('No Z-A label available');
        $showAllLabel = __('Show All') ?: __('No show all label available');
        $seeMyTasksLabel = __('See My Tasks') ?: __('No see my tasks label available');
    } catch (\Throwable $e) {
        \Log::error('tasks/taskboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Tasks') }}
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @if($view === 'grid')
        <a id="task-view-toggle-list" href="{{ $listRoute['url'] }}" class="{{ VC::BT_SM }} bg-white btn-icon rounded-pill {{ VC::MR2 }} m-0" data-url="{{ $listRoute['url'] }}" data-guard-msg="{{ base64_encode($listRoute['guardMsg']) }}" data-sv-localized="true">
            <span class="btn-inner--text {{ VC::TX_DK }}">{{ $listLabel }}</span>
        </a>
    @else
        <a id="task-view-toggle-grid" href="{{ $gridRoute['url'] }}" class="{{ VC::BT_SM }} bg-white btn-icon rounded-pill {{ VC::MR2 }} m-0" data-url="{{ $gridRoute['url'] }}" data-guard-msg="{{ base64_encode($gridRoute['guardMsg']) }}" data-sv-localized="true">
            <span class="btn-inner--text {{ VC::TX_DK }}">{{ $cardLabel }}</span>
        </a>
    @endif

    <div class="bg-neutral rounded-pill d-inline-block">
        <div class="input-group input-group-sm input-group-merge input-group-flush">
            <div class="input-group-prepend">
                <span class="{{ VC::INP_GP_TXT }}"><i class="{{ VC::TI_SRC }}"></i></span>
            </div>
            <input type="text" id="task_keyword" class="{{ VC::FM_CT }} form-control-flush" placeholder="{{ $searchPh }}">
        </div>
    </div>

    <div class="dropdown {{ VC::BT_SM }} btn-white btn-icon-only rounded-circle ml-2 m-0">
        <a href="#" class="action-item {{ VC::TX_DK }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-filter"></i>
        </a>
        <div class="{{ VC::DRP_MN }} dropdown-menu-right dropdown-steady" id="task_sort">
            <a class="{{ VC::DRP_IT }} active" href="#" data-val="created_at-desc"><i class="ti ti-sort-amount-down"></i>{{ $newestLabel }}</a>
            <a class="{{ VC::DRP_IT }}" href="#" data-val="created_at-asc"><i class="ti ti-sort-amount-up"></i>{{ $oldestLabel }}</a>
            <a class="{{ VC::DRP_IT }}" href="#" data-val="name-asc"><i class="ti ti-sort-alpha-down"></i>{{ $fromAzLabel }}</a>
            <a class="{{ VC::DRP_IT }}" href="#" data-val="name-desc"><i class="ti ti-sort-alpha-up"></i>{{ $fromZaLabel }}</a>
        </div>
    </div>

    <div class="dropdown {{ VC::BT_SM }} btn-white btn-icon-only rounded-circle ml-2 m-0">
        <a href="#" class="action-item {{ VC::TX_DK }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-flag"></i>
        </a>
        <div class="{{ VC::DRP_MN }} dropdown-menu-right task-filter-actions dropdown-steady" id="task_status">
            <a class="{{ VC::DRP_IT }} filter-action filter-show-all pl-4" href="#">{{ $showAllLabel }}</a>
            <hr class="my-0">
            <a class="{{ VC::DRP_IT }} filter-action pl-4 active" href="#" data-val="see_my_tasks">{{ $seeMyTasksLabel }}</a>
            <hr class="my-0">
            @foreach(ProjectTask::$priority as $key => $val)
                <a class="{{ VC::DRP_IT }} filter-action pl-4" href="#" data-val="{{ $key }}">{{ __($val) }}</a>
            @endforeach
            <hr class="my-0">
            <a class="{{ VC::DRP_IT }} filter-action filter-other pl-4" href="#" data-val="due_today">{{ __('Due Today') }}</a>
            <a class="{{ VC::DRP_IT }} filter-action filter-other pl-4" href="#" data-val="over_due">{{ __('Over Due') }}</a>
            <a class="{{ VC::DRP_IT }} filter-action filter-other pl-4" href="#" data-val="starred">{{ __('Starred') }}</a>
        </div>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }} min-750" id="taskboard_view"></div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/tasks/board/toggle.js') }}"></script>
@endpush
