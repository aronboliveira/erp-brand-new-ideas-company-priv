@php
	use App\Config\Constants\{ActivitiesConstants, ProjectsConstants, StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$project_id ??= null;
	$stage_id ??= null;
	$project ??= null;
	$hrs ??= [];

	$lang = Utility::fetchUserLang();
	$formId = 'create-project-task-form';

	$taskCreateBase = ViewsConstants::PRJ_TSK_C . '.create';
	$taskCreateResolved = null;
	$taskCreateUrl = '#';
	$taskCreateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'create_project_task_route_unavailable') ?? 'Create project task route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$taskCreateResolved = Route::has($taskCreateBase)
			? $taskCreateBase
			: (Route::has(Str::kebab($taskCreateBase)) ? Str::kebab($taskCreateBase) : null);
	} catch (\Error $e) {
		Log::error('Blade projects/tasks/create: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade projects/tasks/create: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade projects/tasks/create: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade projects/tasks/create: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$taskCreateUrl = ($taskCreateResolved && !empty($project_id) && !empty($stage_id))
			? route($taskCreateResolved, [$project_id, $stage_id])
			: '#';
	} catch (\Error $e) {
		Log::error('Blade projects/tasks/create: route URL generation error: ' . $e->getMessage());
		$taskCreateUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade projects/tasks/create: invalid argument while generating URL: ' . $e->getMessage());
		$taskCreateUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade projects/tasks/create: general exception while generating URL: ' . $e->getMessage());
		$taskCreateUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade projects/tasks/create: throwable while generating URL: ' . $e->getMessage());
		$taskCreateUrl = '#';
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
	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$createLabel = __('Save') ?: __('Failed to get create label');

	$milestones = is_iterable(data_get($project, 'milestones')) ? data_get($project, 'milestones') : [];
	$projUsers = is_iterable(data_get($project, 'users')) ? data_get($project, 'users') : [];
@endphp

{{ Form::open([
	'url'                  => $taskCreateUrl,
	'method'               => 'post',
	'id'                   => $formId,
	'data-resolved-action' => $taskCreateUrl,
	'data-guard-msg'       => $taskCreateGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="{{ VC::RW }}">
		<div class="{{ VC::C8 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_NM, $taskNameLabel, ['class' => VC::FM_LB]) }}
				{{ Form::text(ProjectsConstants::COL_NM, null, ['class' => VC::FM_CT, 'required' => 'required']) }}
			</div>
		</div>
		<div class="{{ VC::C4 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_ML_ID, $milestoneLabel, ['class' => VC::FM_LB]) }}
				<select class="{{ VC::FM_CT }}" name="milestone_id" id="milestone_id">
					<option value="0"></option>
					@foreach($milestones as $mVal)
						@php($mId = data_get($mVal, 'id') ?? '')
						@php($mTitle = data_get($mVal, 'title') ?? __('No title available'))
						<option value="{{ $mId }}">{{ $mTitle }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="{{ VC::C12 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ActivitiesConstants::COL_DESC, $descLabel, ['class' => VC::FM_LB]) }}
				<small class="{{ VC::TXSM }} mb-2 mt-0">{{ $descHelp }}</small>
				{{ Form::textarea(ActivitiesConstants::COL_DESC, null, ['class' => VC::FM_CT, 'rows' => '1', 'data-toggle' => 'autosize']) }}
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_E_HRS, $estLabel, ['class' => VC::FM_LB]) }}
				<small class="{{ VC::TXSM }} mb-2 mt-0">{{ $estHelp }}</small>
				{{ Form::number(ProjectsConstants::COL_E_HRS, null, ['class' => VC::FM_CT, 'required' => 'required', 'min' => '0', 'maxlength' => '8']) }}
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_PRT, $priorityLabel, ['class' => VC::FM_LB]) }}
				<small class="{{ VC::TXSM }} mb-2 mt-0">{{ $priorityHelp }}</small>
				<select class="{{ VC::FM_CT }}" name="priority" id="priority" required>
					@foreach(\App\Models\ProjectTask::$priority as $key => $val)
						<option value="{{ $key }}">{{ __($val) }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_S_DT, $startDateLabel, ['class' => VC::FM_LB]) }}
				{{ Form::date(ProjectsConstants::COL_S_DT, null, ['class' => VC::FM_CT]) }}
			</div>
		</div>
		<div class="{{ VC::C6 }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label(ProjectsConstants::COL_E_DT, $endDateLabel, ['class' => VC::FM_LB]) }}
				{{ Form::date(ProjectsConstants::COL_E_DT, null, ['class' => VC::FM_CT]) }}
			</div>
		</div>
	</div>

	<div class="{{ VC::FM_G }}">
		<label class="{{ VC::FM_LB }}">{{ $taskMembersLabel }}</label>
		<small class="{{ VC::TXSM }} mb-2 mt-0">{{ $belowUsersHelp }}</small>
	</div>

	<div class="{{ VC::LG_FLSH_MB4 }}">
		<div class="{{ VC::RW }}">
			@foreach($projUsers as $usr)
				@php($uid = data_get($usr, 'id') ?? '')
				@php($uname = data_get($usr, 'name') ?? __('No name available'))
				@php($uemail = data_get($usr, 'email') ?? __('No email available'))
				@php($avatarRaw = data_get($usr, 'img_avatar') ?? '')
				<div class="{{ VC::C6 }}">
					<div class="list-group-item px-0">
						<div class="row align-items-center">
							<div class="col-auto ml-3">
								<a href="#" class="avatar avatar-sm rounded-circle">
									<img {!! $avatarRaw !!} />
								</a>
							</div>
							<div class="col ml-n2">
								<p class="d-block h6 text-sm mb-0">{{ $uname }}</p>
								<p class="card-text text-sm text-muted mb-0">{{ $uemail }}</p>
							</div>
							<div class="col-auto text-end add_usr" data-id="{{ $uid }}">
								<button type="button" class="btn btn-xs btn-animated btn-primary rounded-pill btn-animated-y mr-3">
									<span class="btn-inner--visible">
										<i class="{{ VC::TI_PLS }}" id="usr_icon_{{ $uid }}"></i>
									</span>
									<span class="btn-inner--hidden" id="usr_txt_{{ $uid }}">{{ __('Add') }}</span>
								</button>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>
		{{ Form::hidden(ProjectsConstants::COL_ASGN, null, ['id' => 'project-task-assignees']) }}
	</div>

	<div class="text-end">
		<input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		{{ Form::button($createLabel, ['type' => 'submit', 'class' => 'btn btn-sm btn-primary rounded-pill']) }}
	</div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/projects/tasks/create.js') }}"></script>
