@php
	use App\Config\Constants\{ProjectsConstants, StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\{ProjectTask, Utility};
	use Illuminate\Support\{Facades\Log, Facades\Route, Facades\Auth, Str};
	$tasks ??= [];
	$user = null;
	$lang = Utility::fetchUserLang(user: $user);
	$noTasksLabel = __('No tasks found') ?: __('No tasks available');
	$indexBase = ViewsConstants::PRJ_TSK_C . '.index';
	$indexResolved = null;
	$showGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'show_project_task_route_unavailable') ?? 'Show project task route is unavailable. Please contact technical support or your domain administrator.';
	try {
		$indexResolved = Route::has($indexBase)
			? $indexBase
			: (Route::has(Str::kebab($indexBase)) ? Str::kebab($indexBase) : null);
	} catch (\Error $e) {
		Log::error('Blade projectTasks/table: route name resolution error: ' . $e->getMessage());
	} catch (\InvalidArgumentException $e) {
		Log::error('Blade projectTasks/table: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade projectTasks/table: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade projectTasks/table: throwable while resolving route name: ' . $e->getMessage());
	}
@endphp

<div class="{{ VC::C12 }}">
	<div class="{{ VC::CD }}">
		<div class="table-responsive">
			<table class="{{ VC::TB_AL }}">
				<thead>
					<tr>
						<th scope="col">{{ __('Name') }}</th>
						<th scope="col">{{ __('Stage') }}</th>
						<th scope="col">{{ __('Priority') }}</th>
						<th scope="col">{{ __('End Date') }}</th>
						<th scope="col">{{ __('Assigned To') }}</th>
						<th scope="col">{{ __('Completion') }}</th>
						<th scope="col"></th>
					</tr>
				</thead>
				<tbody class="list">
					@if(count($tasks) > 0)
						@foreach($tasks as $task)
							@php
								$projectId = data_get($task, 'project.id');
								$taskName = data_get($task, 'name') ?? __('No name available');
								$projectName = data_get($task, 'project.name') ?? __('No project name available');

								$showUrl = '#';
								try {
									$showUrl = ($indexResolved && !empty($projectId)) ? route($indexResolved, $projectId) : '#';
								} catch (\Throwable $e) {
									Log::error('Blade projectTasks/table: URL generation error: ' . $e->getMessage());
									$showUrl = '#';
								}

								$priorityKey = data_get($task, 'priority');
								$priorityColor = data_get(ProjectTask::$priority_color, $priorityKey) ?? 'secondary';
								$priorityText = data_get(ProjectTask::$priority, $priorityKey) ?? __('No priority available');

								$endDate = data_get($task, 'end_date');
								$isOverdue = false;
								$endDateOut = '';
								try {
									if (!empty($endDate) && $endDate !== '0000-00-00') {
										$endDateOut = Utility::getDateFormated($endDate);
										$isOverdue = @strtotime($endDate) < @time();
									}
								} catch (\Throwable $e) {
									Log::error('Blade projectTasks/table: end date formatting error: ' . $e->getMessage());
									$endDateOut = '';
									$isOverdue = false;
								}

								$stageName = data_get($task, 'stage.name') ?? __('No stage available');

								$ownerState = null;
								try {
									$ownerState = $user?->checkProject(data_get($task, 'project_id'));
								} catch (\Throwable $e) {
									Log::error('Blade projectTasks/table: checkProject error: ' . $e->getMessage());
									$ownerState = null;
								}
								$ownerBadge = ($ownerState === 'Owner') ? ProjectsConstants::STT_SCS : ProjectsConstants::STT_WRN;

								$filesCount = 0;
								$commentsCount = 0;
								$checklistCount = 0;
								try { $filesCount = is_iterable(data_get($task, 'taskFiles')) ? count($task->taskFiles) : 0; } catch (\Throwable $e) { $filesCount = 0; }
								try { $commentsCount = is_iterable(data_get($task, 'comments')) ? count($task->comments) : 0; } catch (\Throwable $e) { $commentsCount = 0; }
								try { $checklistCount = method_exists($task, 'countTaskChecklist') ? (int) $task->countTaskChecklist() : 0; } catch (\Throwable $e) { $checklistCount = 0; }

								$progress = ['percentage' => '0%', 'color' => 'secondary'];
								try {
									$tmp = $task->taskProgress($task);
									$progress['percentage'] = (string) data_get($tmp, 'percentage', '0%');
									$progress['color'] = (string) data_get($tmp, 'color', 'secondary');
								} catch (\Throwable $e) {
									Log::error('Blade projectTasks/table: taskProgress error: ' . $e->getMessage());
								}
								$progressPctOnly = is_string($progress['percentage']) ? str_replace('%', '', $progress['percentage']) : '0';

								$usersList = [];
								try { $usersList = $task->users() ?? []; } catch (\Throwable $e) { $usersList = []; }
							@endphp
							<tr>
								<td>
									<span class="{{ VC::H6 }} {{ VC::TXSM }} font-weight-bold {{ VC::MB0 }}">
										<a href="{{ $showUrl }}"
										   class="project-task-index-link"
										   data-url="{{ $showUrl }}"
										   data-guard-msg="{{ $showGuardMsg }}"
										   data-sv-localized="true">{{ $taskName }}</a>
									</span>
									<span class="{{ VC::DBL }} {{ VC::TXSM }} {{ VC::TXT_MT }}">
										{{ $projectName }}
										<span class="{{ VC::BDG_XS }} badge-{{ $ownerBadge }}">{{ __($ownerState ?? '-') }}</span>
									</span>
								</td>
								<td>{{ $stageName }}</td>
								<td>
									<span class="{{ VC::BDG }} badge-pill badge-sm badge-{{ $priorityColor }}">{{ __($priorityText) }}</span>
								</td>
								<td class="{{ $isOverdue ? 'text-danger' : '' }}">{{ $endDateOut }}</td>
								<td>
									<div class="avatar-group">
										@php($uCount = is_iterable($usersList) ? count($usersList) : 0)
										@if($uCount > 0)
											@foreach($usersList as $k => $u)
												@if($k < 3)
													@php
														$img = '';
														try { $img = $u->getImgImageAttribute(); } catch (\Throwable $e) { $img = ''; }
													@endphp
													<a href="#" class="{{ VC::AV_CC_SM }}">
														<img src="{{ $img }}" title="{{ data_get($u, 'name') ?? '' }}">
													</a>
												@else
													@break
												@endif
											@endforeach
											@if($uCount > 3)
												<a href="#" class="{{ VC::AV_CC_SM }}">
													<img avatar="+ {{ $uCount - 3 }}">
												</a>
											@endif
										@else
											{{ __('-') }}
										@endif
									</div>
								</td>
								<td>
									<div class="{{ VC::DFL_AIC }}">
										<span class="completion {{ VC::MR2 }}">{{ $progress['percentage'] }}</span>
										<div>
											<div class="{{ VC::PG }}" style="width: 100px;">
												<div class="progress-bar bg-{{ $progress['color'] }}"
													 role="progressbar"
													 aria-valuenow="{{ $progressPctOnly }}"
													 aria-valuemin="0"
													 aria-valuemax="100"
													 style="width: {{ $progress['percentage'] }};"></div>
											</div>
										</div>
									</div>
								</td>
								<td class="text-end w-15">
									<div class="actions">
										<a class="action-item px-1" data-toggle="tooltip" data-original-title="{{ __('Attachment') }}">
											<i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $filesCount }}
										</a>
										<a class="action-item px-1" data-toggle="tooltip" data-original-title="{{ __('Comment') }}">
											<i class="ti ti-brand-hipchart {{ VC::MR2 }}"></i>{{ $commentsCount }}
										</a>
										<a class="action-item px-1" data-toggle="tooltip" data-original-title="{{ __('Checklist') }}">
											<i class="ti ti-tasks {{ VC::MR2 }}"></i>{{ $checklistCount }}
										</a>
									</div>
								</td>
							</tr>
						@endforeach
					@else
						<tr>
							<th scope="col" colspan="7"><h6 class="text-center">{{ $noTasksLabel }}</h6></th>
						</tr>
					@endif
				</tbody>
			</table>
		</div>
	</div>
    <script defer src="{{ asset('assets/js/routes/projectTasks/list.js') }}"></script>
</div>

