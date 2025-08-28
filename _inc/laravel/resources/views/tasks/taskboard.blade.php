@php
	use App\Config\Constants\{
		ExtendingLayoutsConstants,
		StacksConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants,
		YieldingConstants
	};
	use App\Models\{ProjectTask, Utility};
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$lang = Utility::fetchUserLang();
	$view ??= 'grid';
	$toggleBase = ViewsConstants::TSKB.'.view';
	$toggleResolved = null;
	$gridUrl = '#';
	$listUrl = '#';
	$guardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TSK, 'task_board_view_route_unavailable') ?? 'Task board view route is unavailable. Please contact technical support or your domain administrator.';
	$listLabel = __('List View') ?: __('No list label available');
	$cardLabel = __('Card View') ?: __('No card label available');
	$searchPh = __('Search by Name') ?: __('No search placeholder available');
	$newestLabel = __('Newest') ?: __('No newest label available');
	$oldestLabel = __('Oldest') ?: __('No oldest label available');
	$fromAzLabel = __('From A-Z') ?: __('No A-Z label available');
	$fromZaLabel = __('From Z-A') ?: __('No Z-A label available');
	$showAllLabel = __('Show All') ?: __('No show all label available');
	$seeMyTasksLabel = __('See My Tasks') ?: __('No see my tasks label available');
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Tasks') }}
@endsection

@php
	try {
		$toggleResolved = Route::has($toggleBase)
			? $toggleBase
			: (Route::has(Str::kebab($toggleBase)) ? Str::kebab($toggleBase) : null);
	} catch (\Error $e) {
		Log::error('Blade taskBoard/header: route name resolution error (Error): ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taskBoard/header: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade taskBoard/header: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade taskBoard/header: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$gridUrl = $toggleResolved ? route($toggleResolved, 'grid') : '#';
		$listUrl = $toggleResolved ? route($toggleResolved, 'list') : '#';
	} catch (\Error $e) {
		Log::error('Blade taskBoard/header: URL generation error (Error): ' . $e->getMessage());
		$gridUrl = '#';
		$listUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade taskBoard/header: invalid argument while generating URLs: ' . $e->getMessage());
		$gridUrl = '#';
		$listUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade taskBoard/header: general exception while generating URLs: ' . $e->getMessage());
		$gridUrl = '#';
		$listUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade taskBoard/header: throwable while generating URLs: ' . $e->getMessage());
		$gridUrl = '#';
		$listUrl = '#';
	}
@endphp

@section(YieldingConstants::ADM_ACT_BTN)
	@if($view === 'grid')
		<a id="task-view-toggle-list"
		   href="{{ $listUrl }}"
		   class="{{ VC::BT_SM }} bg-white btn-icon rounded-pill {{ VC::MR2 }} m-0"
		   data-url="{{ $listUrl }}"
		   data-guard-msg="{{ $guardMsg }}"
		   data-sv-localized="true">
			<span class="btn-inner--text text-dark">{{ $listLabel }}</span>
		</a>
	@else
		<a id="task-view-toggle-grid"
		   href="{{ $gridUrl }}"
		   class="{{ VC::BT_SM }} bg-white btn-icon rounded-pill {{ VC::MR2 }} m-0"
		   data-url="{{ $gridUrl }}"
		   data-guard-msg="{{ $guardMsg }}"
		   data-sv-localized="true">
			<span class="btn-inner--text text-dark">{{ $cardLabel }}</span>
		</a>
	@endif

	<div class="bg-neutral rounded-pill d-inline-block">
		<div class="input-group input-group-sm input-group-merge input-group-flush">
			<div class="input-group-prepend">
				<span class="{{ VC::INP_GP_TXT }}"><i class="{{ VC::TI_SRC }}"></i></span>
			</div>
			<input type="text" id="task_keyword" class="form-control form-control-flush" placeholder="{{ $searchPh }}">
		</div>
	</div>

	<div class="dropdown {{ VC::BT_SM }} btn-white btn-icon-only rounded-circle ml-2 m-0">
		<a href="#" class="action-item text-dark" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="ti ti-filter"></i>
		</a>
		<div class="dropdown-menu dropdown-menu-right dropdown-steady" id="task_sort">
			<a class="dropdown-item active" href="#" data-val="created_at-desc">
				<i class="ti ti-sort-amount-down"></i>{{ $newestLabel }}
			</a>
			<a class="dropdown-item" href="#" data-val="created_at-asc">
				<i class="ti ti-sort-amount-up"></i>{{ $oldestLabel }}
			</a>
			<a class="dropdown-item" href="#" data-val="name-asc">
				<i class="ti ti-sort-alpha-down"></i>{{ $fromAzLabel }}
			</a>
			<a class="dropdown-item" href="#" data-val="name-desc">
				<i class="ti ti-sort-alpha-up"></i>{{ $fromZaLabel }}
			</a>
		</div>
	</div>

	<div class="dropdown btn btn-sm btn-white btn-icon-only rounded-circle ml-2 m-0">
		<a href="#" class="action-item text-dark" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="ti ti-flag"></i>
		</a>
		<div class="dropdown-menu dropdown-menu-right task-filter-actions dropdown-steady" id="task_status">
			<a class="dropdown-item filter-action filter-show-all pl-4" href="#">{{ $showAllLabel }}</a>
			<hr class="my-0">
			<a class="dropdown-item filter-action pl-4 active" href="#" data-val="see_my_tasks">{{ $seeMyTasksLabel }}</a>
			<hr class="my-0">
			@foreach(ProjectTask::$priority as $key => $val)
				<a class="dropdown-item filter-action pl-4" href="#" data-val="{{ $key }}">{{ __($val) }}</a>
			@endforeach
			<hr class="my-0">
			<a class="dropdown-item filter-action filter-other pl-4" href="#" data-val="due_today">{{ __('Due Today') }}</a>
			<a class="dropdown-item filter-action filter-other pl-4" href="#" data-val="over_due">{{ __('Over Due') }}</a>
			<a class="dropdown-item filter-action filter-other pl-4" href="#" data-val="starred">{{ __('Starred') }}</a>
		</div>
	</div>
@endsection

@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }} min-750" id="taskboard_view"></div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
	<script defer src="{{ asset('assets/js/routes/tasks/board/toggle.js') }}"></script>
@endpush
