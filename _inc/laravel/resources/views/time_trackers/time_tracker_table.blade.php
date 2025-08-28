@php
	$i ??= 0;
	use App\Config\Constants\ActivitiesConstants;
@endphp
@forelse ($trackers as $weekKey => $track)
	@php
		try {
			$first_acc = 0;
			$tracker = is_object($trackers) && method_exists($trackers, 'toArray') ? $trackers->toArray() : (is_array($trackers) ? $trackers : []);
			$data = is_object($track) && method_exists($track, 'toArray') ? $track->toArray() : (is_array($track) ? $track : []);
			$yearSource = isset($data['0']) ? data_get($data, '0.' . ActivitiesConstants::COL_ST_TIME) : null;
			$year = $yearSource ? date('Y', strtotime((string) $yearSource)) : \Carbon\Carbon::now()->year;
			$timeCol = collect($track ?? []);
			$totalSeconds = $timeCol->sum(ActivitiesConstants::COL_TTL_TIME);
			$day_group = $timeCol->groupBy(function ($date) {
				return \Carbon\Carbon::parse(data_get($date, 'start_time'))->format('d');
			});
			$timeFmt = \App\Models\Utility::secondToTime($totalSeconds) ?? __('Failed to get week total time');
			$yearNow = date('Y');
			$dateRange = \App\Models\Utility::getStartAndEndDate(($weekKey ?? 0) - 1, $yearNow) ?? ['start_date' => null, 'end_date' => null];
			$currentWeek = (int) date('W');
			$todayTs = strtotime(date('Y-m-d')) - 7 * 24 * 60 * 60;
			$lastWeek = (int) date('W', $todayTs);
		} catch (\InvalidArgumentException $e) {
			\Illuminate\Support\Facades\Log::error('timetracker.blade:week-build:invalid-argument', ['file' => __FILE__, 'week_key' => $weekKey, 'error' => $e->getMessage()]);
			$first_acc = 0;
			$day_group = collect();
			$timeFmt = __('Failed to get week total time');
			$dateRange = ['start_date' => null, 'end_date' => null];
			$currentWeek = (int) date('W');
			$lastWeek = (int) date('W', strtotime(date('Y-m-d')) - 7 * 24 * 60 * 60);
		} catch (\Throwable $e) {
			\Illuminate\Support\Facades\Log::error('timetracker.blade:week-build:throwable', ['file' => __FILE__, 'week_key' => $weekKey, 'error' => $e->getMessage()]);
			$first_acc = 0;
			$day_group = collect();
			$timeFmt = __('Failed to get week total time');
			$dateRange = ['start_date' => null, 'end_date' => null];
			$currentWeek = (int) date('W');
			$lastWeek = (int) date('W', strtotime(date('Y-m-d')) - 7 * 24 * 60 * 60);
		}
	@endphp
	<div class="card">
		<div class="card-body timetracker_options">
			<div class="clearfix">
				<div class="float-left">
					<h5 class="week-date">
						@if($currentWeek === (int) $weekKey)
							{{ __('This week') }}
						@elseif($lastWeek === (int) $weekKey)
							{{ __('Last week') }}
						@else
							{{ is_string($dateRange['start_date'] ?? null) ? date('M d', strtotime(($dateRange['start_date'] ?? '') . ' +1 day')) : __('Could not find start date') }} - {{ is_string($dateRange['end_date'] ?? null) ? date('M d', strtotime(($dateRange['end_date'] ?? '') . ' +1 day')) : __('Could not find end date') }}
						@endif
					</h5>
				</div>
				<div class="float-right">
					<div>{{ __('Week total') }} : <b>{{ $timeFmt }}</b></div>
				</div>
				<span class="clearfix"></span>
			</div>
			<div class="time-schrdule bg-white p-2 small">
				<div class="row">
					<div class="col-3"><b>{{ __('Title') }}</b></div>
					<div class="col-1"><b>{{ __('Project Name') }}</b></div>
					<div class="col-1"><b>{{ __('User') }}</b></div>
					<div class="col-2"><b>{{ __('Tags') }}</b></div>
					<div class="col-1"><b>{{ __('Date') }}</b></div>
					<div class="col-1"><b>{{ __('Start') }}</b></div>
					<div class="col-1"><b>{{ __('End') }}</b></div>
					<div class="col-1"><b>{{ __('Time') }}</b></div>
					<div class="col-1"><b></b></div>
				</div>
				<div class="bb1"></div>
				<div class="project-acc">
					@foreach (($day_group ?? collect())->reverse() as $dayKey => $day_tracks)
						@php
							try {
								$time_day = collect($day_tracks ?? []);
								$total_day = \App\Models\Utility::secondToTime($time_day->sum(ActivitiesConstants::COL_TTL_TIME)) ?? __('Failed to get day total time');
								$name_group = $time_day->groupBy('name') ?? collect();
								$class = 'open-accordion';
							} catch (\Throwable $e) {
								\Illuminate\Support\Facades\Log::error('timetracker.blade:day-build:throwable', ['file' => __FILE__, 'day_key' => $dayKey, 'error' => $e->getMessage()]);
								$time_day = collect();
								$total_day = __('Failed to get day total time');
								$name_group = collect();
								$class = 'open-accordion';
							}
						@endphp
						@foreach ($name_group->reverse() as $nameKey => $name)
							@php
								try {
									$name_array = is_object($name) && method_exists($name, 'toArray') ? $name->toArray() : (is_array($name) ? $name : []);
									$total_name = \App\Models\Utility::secondToTime(collect($name_array)->sum(ActivitiesConstants::COL_TTL_TIME)) ?? __('Failed to get total time');
									$sdates = collect($name_array)->pluck(ActivitiesConstants::COL_ST_TIME)->filter()->toArray();
									$edates = collect($name_array)->pluck('end_time')->filter()->toArray();
									$start_time = !empty($sdates) ? min($sdates) : null;
									$end_time = !empty($edates) ? max($edates) : null;
									$dateStr = '';
									$user_name = '';
									$project_name = '';
									if (!empty($name_array)) {
										$dateStr = data_get($name, '0.start_time') ? date('M-d-Y', strtotime((string) data_get($name, '0.start_time'))) : __('No date available');
										$user_name = (string) data_get($name, '0.user_name', __('Could not find user name'));
										$project_name = (string) data_get($name, '0.project_name', __('Could not find project name'));
									}
									if ($first_acc === 0) {
										$class = 'open-accordion';
										$first_acc = 1;
										$aicon = 'fa-chevron-up';
										$disply = '';
										$arrow = 'close-acc';
									} else {
										$arrow = 'open-acc';
										$disply = 'none';
										$class = '';
										$aicon = 'fa-chevron-down';
									}
								} catch (\Throwable $e) {
									\Illuminate\Support\Facades\Log::error('timetracker.blade:name-build:throwable', ['file' => __FILE__, 'name_key' => $nameKey, 'error' => $e->getMessage()]);
									$name_array = [];
									$total_name = __('Failed to get total time');
									$start_time = null;
									$end_time = null;
									$dateStr = __('No date available');
									$user_name = __('Could not find user name');
									$project_name = __('Could not find project name');
									$arrow = 'open-acc';
									$disply = 'none';
									$class = '';
									$aicon = 'fa-chevron-down';
								}
							@endphp
							<div class="row acc-mainmenu">
								<div class="col-3"><i class="ti ti-plus accodian-plus"></i> {{ is_string($nameKey) ? $nameKey : __('Could not find title') }}</div>
								<div class="col-1">{{ $project_name }}</div>
								<div class="col-1">{{ $user_name }}</div>
								<div class="col-2">#</div>
								<div class="col-1">{{ $dateStr }}</div>
								<div class="col-1">{{ $start_time ? date('H:i:s', strtotime($start_time)) : __('No start time available') }}</div>
								<div class="col-1">{{ $end_time ? date('H:i:s', strtotime($end_time)) : __('No end time available') }}</div>
								<div class="col-1">{{ $total_name }}</div>
								<div class="col-1"></div>
							</div>
							@if(!empty($name))
								<div class="acc-sub-menu" style="display: none;">
									@foreach ($name as $rowKey => $t)
										<div class="row acc-sub-menu-div">
											<div class="col-3">{{ (string) data_get($t, 'name', __('Could not find title')) }}</div>
											<div class="col-1">{{ (string) data_get($t, 'project_name', __('Could not find project name')) }}</div>
											<div class="col-1">{{ (string) data_get($t, 'user_name', __('Could not find user name')) }}</div>
											<div class="col-2">
												@if(empty($t->tags_name))
													<p>#</p>
												@else
													<p>
														@foreach($t->tags_name as $tag)
															#{{ $tag }},
														@endforeach
													</p>
												@endif
											</div>
											<div class="col-1">{{ data_get($t, 'start_time') ? date('M-d-Y', strtotime((string) data_get($t, 'start_time'))) : __('No date available') }}</div>
											<div class="col-1">{{ data_get($t, 'start_time') ? date('H:i:s', strtotime((string) data_get($t, 'start_time'))) : __('No start time available') }}</div>
											<div class="col-1">{{ data_get($t, 'end_time') ? date('H:i:s', strtotime((string) data_get($t, 'end_time'))) : __('No end time available') }}</div>
											<div class="col-1">{{ (string) data_get($t, 'total', __('Could not find total time')) }}</div>
											<div class="col-1">
												<img alt="Image placeholder" src="{{ asset('assets/images/gallery.png') }}" class="avatar view-images rounded-circle avatar-sm" data-toggle="tooltip" data-original-title="{{ __('View Screenshot images') }}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{ (string) data_get($t, 'id', '') }}" id="track-images-{{ (string) data_get($t, 'id', '') }}">
												<i data-id="{{ (string) data_get($t, 'id', '') }}" data-is_billable="{{ (int) data_get($t, 'is_billable', 0) }}" data-toggle="tooltip" data-original-title="{{ data_get($t, 'is_billable', 0) == 1 ? __('Click to Mark Non-Billable') : __('Click to Mark Billable') }}" class="change_billable ti ti-dollar-sign {{ data_get($t, 'is_billable', 0) == 1 ? 'doller-billable' : 'doller-non-billable' }}"></i>
												<i class="ti ti-times text-danger mx-2 pointer remove-track" data-toggle="tooltip" data-original-title="{{ __('Delete') }}" data-id="{{ (string) data_get($t, 'id', '') }}" data-url=""></i>
											</div>
										</div>
									@endforeach
								</div>
							@endif
						@endforeach
					@endforeach
				</div>
			</div>
		</div>
	</div>
@empty
	<div class="timetracker_options card p-5">
		<div class="selected_date week_total text-center mx-auto">
			<span class="week-date">{{ __('Records not found') }}</span>
		</div>
	</div>
@endforelse
<script src="{{ asset('assets/js/routes/timeTrackers/lang/time.js') }}"></script>
<script src="{{ asset('assets/js/routes/timeTrackers/time.js') }}"></script>
