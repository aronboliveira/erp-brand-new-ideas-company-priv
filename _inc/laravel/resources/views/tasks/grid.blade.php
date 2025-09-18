@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\{ProjectTask, Utility};
	use Illuminate\Support\{Facades\Log, Facades\Route, Facades\Auth, Str};

	$tasks ??= [];
	$user = Auth::user();
	$lang = Utility::fetchUserLang(user: $user);
	$noTasksLabel = __('No tasks found') ?: __('No tasks available');
	$showGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'show_project_task_route_unavailable') ?? 'Show project task route is unavailable. Please contact technical support or your domain administrator.';
	$indexBase = ViewsConstants::PRJ_TSK_C . '.index';
	$indexResolved = null;
	try {
		$indexResolved = Route::has($indexBase)
			? $indexBase
			: (Route::has(Str::kebab($indexBase)) ? Str::kebab($indexBase) : null);
	} catch (\Error $e) {
		Log::error('Blade projects/tasks/list: route name resolution error (Error): ' . $e->getMessage());
	} catch (\InvalidArgumentException $e) {
		Log::error('Blade projects/tasks/list: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade projects/tasks/list: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade projects/tasks/list: throwable while resolving route name: ' . $e->getMessage());
	}
@endphp

<div class="{{ VC::C12 }}">
	<div class="{{ VC::CD }}">
		<div class="{{ VC::RW }}">
			@if(count($tasks) > 0)
				@foreach($tasks as $task)
					@php
						$permissions = null;
						try {
							$permissions = method_exists($user, 'getPermission') ? $user?->getPermission(data_get($task, 'project_id')) : null;
						} catch (\Throwable $e) {
							Log::error('Blade projects/tasks/list: error getting permissions: ' . $e->getMessage());
						}

						$showUrl = '#';
						try {
							$projectId = data_get($task, 'project.id');
							$showUrl = ($indexResolved && !empty($projectId)) ? route($indexResolved, $projectId) : '#';
						} catch (\Error $e) {
							Log::error('Blade projects/tasks/list: route URL generation error (Error): ' . $e->getMessage());
							$showUrl = '#';
						} catch (\InvalidArgumentException $e) {
							Log::error('Blade projects/tasks/list: invalid argument while generating URL: ' . $e->getMessage());
							$showUrl = '#';
						} catch (\Exception $e) {
							Log::error('Blade projects/tasks/list: general exception while generating URL: ' . $e->getMessage());
							$showUrl = '#';
						} catch (\Throwable $e) {
							Log::error('Blade projects/tasks/list: throwable while generating URL: ' . $e->getMessage());
							$showUrl = '#';
						}

						$progressPct = null;
						try {
							$progressPct = method_exists($task, 'taskProgress') ? data_get($task->taskProgress($task), 'percentage') : null;
						} catch (\Throwable $e) {
							Log::error('Blade projects/tasks/list: error computing task progress: ' . $e->getMessage());
							$progressPct = null;
						}
						$progressVal = is_string($progressPct) ? str_replace('%', '', $progressPct) : null;

						$priorityKey = data_get($task, 'priority');
						$priorityColor = data_get(ProjectTask::$priority_color, $priorityKey) ?? 'secondary';
						$priorityText = data_get(ProjectTask::$priority, $priorityKey) ?? __('No priority available');

						$endDateOut = null;
						$isOverdue = false;
						try {
							$ed = data_get($task, 'end_date');
							if (!empty($ed) && $ed !== '0000-00-00') {
								$endDateOut = is_callable([Utility::class, 'getDateFormated']) ? Utility::getDateFormated($ed) : null;
								$isOverdue = @strtotime($ed) < @time();
							}
						} catch (\Throwable $e) {
							Log::error('Blade projects/tasks/list: error formatting end date: ' . $e->getMessage());
							$endDateOut = null;
							$isOverdue = false;
						}

						$usersList = [];
						try {
							$usersList = method_exists($task, 'users') ? $task->users() : [];
						} catch (\Throwable $e) {
							Log::error('Blade projects/tasks/list: error fetching task users: ' . $e->getMessage());
							$usersList = [];
						}
					@endphp
					<div class="{{ VC::CM4 }}">
						<div class="{{ VC::CD }} m-3 card-progress {{ VC::BD }} {{ VC::SNN }}" id="{{ data_get($task, 'id') }}" style="{{ !empty(data_get($task,'priority_color')) ? 'border-left: 2px solid '.data_get($task,'priority_color').' !important' : '' }};">
							<div class="card-body">
								<div class="{{ VC::RW }} align-items-center mb-2">
									<div class="{{ VC::C6 }}">
										<span class="{{ VC::BDG_XS }} badge-pill badge-{{ $priorityColor }}">{{ $priorityText }}</span>
									</div>
									<div class="{{ VC::C6 }} text-end">
										@if(is_numeric($progressVal) && floatval($progressVal) > 0)
											<span class="{{ VC::TXSM }}">{{ $progressPct }}</span>
										@endif
									</div>
								</div>

								@if(isset($permissions) && is_array($permissions) && in_array('show task', $permissions, true))
									<a class="{{ VC::H6 }} task-name-break project-task-index-link"
									   href="{{ $showUrl }}"
									   data-url="{{ $showUrl }}"
									   data-guard-msg="{{ $showGuardMsg }}"
									   data-sv-localized="true">{{ data_get($task, 'name') ?? __('No name available') }}</a>
								@else
									<a class="{{ VC::H6 }} task-name-break project-task-index-link"
									   href="#"
									   data-url="#"
									   data-guard-msg="{{ $showGuardMsg }}"
									   data-sv-localized="true">{{ data_get($task, 'name') ?? __('No name available') }}</a>
								@endif

								<div class="{{ VC::RW }} {{ VC::ALC }}">
									<div class="{{ VC::C12 }}">
										<div class="actions d-inline-block">
											@if(is_iterable(data_get($task,'taskFiles')) && count($task->taskFiles) > 0)
												<div class="action-item {{ VC::MR2 }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ count($task->taskFiles) }}</div>
											@endif
											@if(is_iterable(data_get($task,'comments')) && count($task->comments) > 0)
												<div class="action-item {{ VC::MR2 }}"><i class="ti ti-brand-hipchart {{ VC::MR2 }}"></i>{{ count($task->comments) }}</div>
											@endif
											@if(method_exists($task,'checklist') && $task->checklist->count() > 0)
												<div class="action-item {{ VC::MR2 }}"><i class="ti ti-tasks {{ VC::MR2 }}"></i>{{ $task->countTaskChecklist() }}</div>
											@endif
										</div>
									</div>

									<div class="{{ VC::C5 }}">
										@if(!empty($endDateOut))
											<small @if($isOverdue) class="text-danger" @endif>{{ $endDateOut }}</small>
										@endif
									</div>
									<div class="{{ VC::C7 }} text-end">
										@if(is_iterable($usersList) && count($usersList) > 0)
											<div class="avatar-group">
												@foreach($usersList as $key => $u)
													@if($key < 3)
														<a href="#" class="{{ VC::AV_CC_SM }}">
															<img {!! data_get($u, 'img_avatar') ?? '' !!} title="{{ data_get($u,'name') ?? '' }}">
														</a>
													@else
														@break
													@endif
												@endforeach
												@if(count($usersList) > 3)
													<a href="#" class="{{ VC::AV_CC_SM }}">
														<img avatar="+ {{ count($usersList) - 3 }}">
													</a>
												@endif
											</div>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
				@endforeach
			@else
				<div class="{{ VC::CM12 }}">
					<h6 class="text-center m-3">{{ $noTasksLabel }}</h6>
				</div>
			@endif
		</div>
	</div>
  <script defer src="{{ asset('assets/js/routes/projects/tasks/gridShow.js') }}"></script>
</div>
