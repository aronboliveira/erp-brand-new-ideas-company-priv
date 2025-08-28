@php
	use App\Config\Constants\{ActivitiesConstants, ProjectsConstants, StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$task ??= null;
	$project ??= null;
	$hrs ??= [];
	$lang = Utility::fetchUserLang();
	$formId = 'edit-project-task-form';
	$updateBase = ViewsConstants::PRJ_TSK_C . '.update';
	$updateResolved = null;
	$updateUrl = '#';
	$updateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'update_project_task_route_unavailable') ?? 'Update project task route is unavailable. Please contact technical support or your domain administrator.';
	$projectId = data_get($project, 'id') ?? null;
	$taskId = data_get($task, 'id') ?? null;

	try {
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has(Str::kebab($updateBase)) ? Str::kebab($updateBase) : null);
	} catch (\Error $e) {
		Log::error('Blade projectTasks/edit: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade projectTasks/edit: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade projectTasks/edit: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade projectTasks/edit: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$updateUrl = ($updateResolved && !empty($projectId) && !empty($taskId)) ? route($updateResolved, [$projectId, $taskId]) : '#';
	} catch (\Error $e) {
		Log::error('Blade projectTasks/edit: route URL generation error: ' . $e->getMessage());
		$updateUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade projectTasks/edit: invalid argument while generating URL: ' . $e->getMessage());
		$updateUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade projectTasks/edit: general exception while generating URL: ' . $e->getMessage());
		$updateUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade projectTasks/edit: throwable while generating URL: ' . $e->getMessage());
		$updateUrl = '#';
	}

	$taskNameLabel = __('Task name') ?: __('No task name label available');
	$milestoneLabel = __('Milestone') ?: __('No milestone label available');
	$descLabel = __('Description') ?: __('No description label available');
	$descHelp = __('This textarea will autosize while you type') ?: __('No help text available');
	$estLabel = __('Estimated Hours') ?: __('No estimate label available');
	$estHelp = (__('Total hrs of project ') . (data_get($hrs, 'total', 0)) . __(' & allocated total ') . (data_get($hrs, 'allocated', 0)) . __(' hrs in other tasks')) ?: __('Failed to get estimation help');
	$priorityLabel = __('Priority') ?: __('No priority label available');
	$priorityHelp = __('Set Priority of your task') ?: __('No priority help available');
	$startDateLabel = __('Start Date') ?: __('No start date label available');
	$endDateLabel = __('End Date') ?: __('No end date label available');
	$taskMembersLabel = __('Task members') ?: __('No members label available');
	$belowUsersHelp = __('Below users are assigned in your project.') ?: __('No project users available');
	$updateBtnLabel = __('Update') ?: __('Failed to get update label');

	$milestones = is_iterable(data_get($project, 'milestones')) ? data_get($project, 'milestones') : [];
	$projUsers = is_iterable(data_get($project, 'users')) ? data_get($project, 'users') : [];
	$assignedCsv = (string) (data_get($task, 'assign_to') ?? '');
	$assigned = collect(explode(',', $assignedCsv))->filter(fn($v) => filled($v))->values()->all();
@endphp

{{ Form::model($task, [
	'url'                  => $updateUrl,
	'method'               => 'PUT',
	'id'                   => $formId,
	'data-resolved-action' => $updateUrl,
	'data-guard-msg'       => $updateGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="{{ VC::RW }}">
		<div class="{{ VC::C8 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_NM, $taskNameLabel, ['class' => 'form-label']) }}
				{{ Form::text(ProjectsConstants::COL_NM, null, ['class' => 'form-control', 'required' => 'required']) }}
			</div>
		</div>
		<div class="{{ VC::C4 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_ML_ID, $milestoneLabel, ['class' => 'form-label']) }}
				<select class="form-control" name="milestone_id" id="milestone_id">
					<option value="0"></option>
					@foreach($milestones as $mVal)
						@php($mId = data_get($mVal, 'id') ?? '')
						@php($mTitle = data_get($mVal, 'title') ?? __('No title available'))
						<option value="{{ $mId }}" {{ (data_get($task, 'milestone_id') == $mId) ? 'selected' : '' }}>{{ $mTitle }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="{{ VC::C12 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ActivitiesConstants::COL_DESC, $descLabel, ['class' => 'form-label']) }}
				<small class="form-text text-muted mb-2 mt-0">{{ $descHelp }}</small>
				{{ Form::textarea(ActivitiesConstants::COL_DESC, null, ['class' => 'form-control', 'rows' => '1', 'data-toggle' => 'autosize']) }}
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_E_HRS, $estLabel, ['class' => 'form-label']) }}
				<small class="form-text text-muted mb-2 mt-0">{{ $estHelp }}</small>
				{{ Form::number(ProjectsConstants::COL_E_HRS, null, ['class' => 'form-control', 'required' => 'required', 'min' => '0', 'maxlength' => '8']) }}
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_PRT, $priorityLabel, ['class' => 'form-label']) }}
				<small class="form-text text-muted mb-2 mt-0">{{ $priorityHelp }}</small>
				<select class="form-control" name="priority" id="priority" required>
					@foreach(\App\Models\ProjectTask::$priority as $key => $val)
						<option value="{{ $key }}" {{ ($key == data_get($task, 'priority')) ? 'selected' : '' }}>{{ __($val) }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_S_DT, $startDateLabel, ['class' => 'form-label']) }}
				{{ Form::date(ProjectsConstants::COL_S_DT, null, ['class' => 'form-control']) }}
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_E_DT, $endDateLabel, ['class' => 'form-label']) }}
				{{ Form::date(ProjectsConstants::COL_E_DT, null, ['class' => 'form-control']) }}
			</div>
		</div>
	</div>

	<div class="{{ VC::FM_G }}">
		<label class="form-label">{{ $taskMembersLabel }}</label>
		<small class="form-text text-muted mb-2 mt-0">{{ $belowUsersHelp }}</small>
	</div>

	<div class="{{ ViewClassNamesConstants::LG_FLSH_MB4 }}">
		<div class="{{ VC::RW }}">
			@foreach((is_iterable($projUsers) ? $projUsers : []) as $usr)
				@php($uid = data_get($usr, 'id') ?? '')
				@php($uname = data_get($usr, 'name') ?? __('No name available'))
				@php($uemail = data_get($usr, 'email') ?? __('No email available'))
				@php($avatarRaw = data_get($usr, 'img_avatar') ?? '')
				@php($isSel = in_array($uid, $assigned, true))
				<div class="{{ VC::C6 }}">
					<div class="list-group-item px-0">
						<div class="{{ VC::R_ALC }}">
							<div class="col-auto ml-3">
								<a href="#" class="{{ VC::AV_CC_SM }}">
									<img {!! $avatarRaw !!} />
								</a>
							</div>
							<div class="col ml-n2">
								<p class="d-block h6 text-sm mb-0">{{ $uname }}</p>
								<p class="card-text text-sm text-muted mb-0">{{ $uemail }}</p>
							</div>
							<div class="col-auto text-end add_usr {{ $isSel ? 'selected' : '' }}" data-id="{{ $uid }}">
								<button type="button" class="btn btn-xs btn-animated btn-primary rounded-pill btn-animated-y mr-3">
									<span class="btn-inner--visible">
										<i class="ti ti-{{ $isSel ? 'check' : 'plus' }}" id="usr_icon_{{ $uid }}"></i>
									</span>
									<span class="btn-inner--hidden" id="usr_txt_{{ $uid }}">{{ $isSel ? __('Added') : __('Add') }}</span>
								</button>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>
		{{ Form::hidden(ProjectsConstants::COL_ASGN, $assignedCsv, ['id' => 'project-task-assignees']) }}
	</div>

	<div class="text-end">
		{{ Form::button($updateBtnLabel, ['type' => 'submit', 'class' => '{{ VC::BT_SM_PM }} rounded-pill']) }}
	</div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/projectTasks/edit.js') }}"></script>
