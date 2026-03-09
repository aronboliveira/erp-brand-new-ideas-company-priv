@include('partials.helpers.route_helpers')
@php
$task ??= null;
	$project ??= null;
	$hrs ??= [];
	$lang ??= 'en';
	$formId ??= 'edit-project-task-form';
	$projectId ??= '';
	$taskId ??= '';
	$taskUpdate ??= ['url' => '#', 'guardMsg' => ''];
	$taskNameLabel ??= '';
	$milestoneLabel ??= '';
	$descLabel ??= '';
	$descHelp ??= '';
	$estLabel ??= '';
	$estHelp ??= '';
	$priorityLabel ??= '';
	$priorityHelp ??= '';
	$startDateLabel ??= '';
	$endDateLabel ??= '';
	$taskMembersLabel ??= '';
	$belowUsersHelp ??= '';
	$updateBtnLabel ??= '';
	$milestones ??= [];
	$projUsers ??= [];
	$assignedCsv ??= '';
	$assigned ??= [];
	$taskMilestoneId ??= null;
	$taskPriority ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$projectId = safeDataGet($project, 'id');
		$taskId = safeDataGet($task, 'id');
		$taskUpdate = resolveRouteWithGuard(VW::PRJ_TSK_C . '.update', $lang, VW::PRJ_TSK_C, 'update_project_task_route_unavailable', [$projectId, $taskId], 'Update task route unavailable.');
		$taskNameLabel = __('Task name') ?: __('No task name label available');
		$milestoneLabel = __('Milestone') ?: __('No milestone label available');
		$descLabel = __('Description') ?: __('No description label available');
		$descHelp = __('This textarea will autosize while you type') ?: __('No help text available');
		$estLabel = __('Estimated Hours') ?: __('No estimate label available');
		$estHelp = (__('Total hrs of project ') . data_get($hrs, 'total', 0) . __(' & allocated total ') . data_get($hrs, 'allocated', 0) . __(' hrs in other tasks')) ?: __('Failed to get estimation help');
		$priorityLabel = __('Priority') ?: __('No priority label available');
		$priorityHelp = __('Set Priority of your task') ?: __('No priority help available');
		$startDateLabel = __('Start Date') ?: __('No start date label available');
		$endDateLabel = __('End Date') ?: __('No end date label available');
		$taskMembersLabel = __('Task members') ?: __('No members label available');
		$belowUsersHelp = __('Below users are assigned in your project.') ?: __('No project users available');
		$updateBtnLabel = __('Update') ?: __('Failed to get update label');
		$milestones = ensureIterable(data_get($project, 'milestones'));
		$projUsers = ensureIterable(data_get($project, 'users'));
		$assignedCsv = (string)(data_get($task, 'assign_to') ?? '');
		$assigned = collect(explode(',', $assignedCsv))->filter(fn($v) => filled($v))->values()->all();
		$taskMilestoneId = safeDataGet($task, 'milestone_id');
		$taskPriority = safeDataGet($task, 'priority');
	} catch (\Error $e) {
		Log::error('Error in tasks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in tasks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in tasks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::model($task, [
    'url' => $taskUpdate['url'],
    'method' => 'PUT',
    'id' => $formId,
    'data-resolved-action' => $taskUpdate['url'],
    'data-guard-msg' => $taskUpdate['guardMsg'],
    'data-sv-localized' => 'true',
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
                        @php
                            $mId = safeDataGet($mVal, 'id');
                            $mTitle = safeDataGet($mVal, 'title') ?: __('No title available');
@endphp
                        <option value="{{ $mId }}" {{ ($taskMilestoneId == $mId) ? 'selected' : '' }}>{{ $mTitle }}</option>
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
                    @foreach(ProjectTask::$priority as $key => $val)
                        <option value="{{ $key }}" {{ ($key == $taskPriority) ? 'selected' : '' }}>{{ __($val) }}</option>
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
                @php
                    try {
                        $uid = safeDataGet($usr, 'id');
                        $uname = safeDataGet($usr, 'name') ?: __('No name available');
                        $uemail = safeDataGet($usr, 'email') ?: __('No email available');
                        $avatarRaw = safeDataGet($usr, 'img_avatar');
                        $isSel = in_array($uid, $assigned, true);
                    } catch (\Throwable $e) {
                        \Log::error('tasks/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <div class="{{ VC::C6 }}">
                    <div class="{{ VC::LG_IT }} px-0">
                        <div class="{{ VC::R_ALC }}">
                            <div class="{{ VC::C_AT }} ml-3">
                                <a href="#" class="{{ VC::AV_CC_SM }}"><img {!! $avatarRaw !!} /></a>
                            </div>
                            <div class="col ml-n2">
                                <p class="{{ VC::DBL }} h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ $uname }}</p>
                                <p class="card-text {{ VC::TXSM }} {{ VC::TXT_MT }} {{ VC::MB0 }}">{{ $uemail }}</p>
                            </div>
                            <div class="{{ VC::C_AT }} {{ VC::TX_END }} add_usr {{ $isSel ? 'selected' : '' }}" data-id="{{ $uid }}">
                                <button type="button" class="{{ VC::BT_XS }} btn-animated {{ VC::BT_PM }} rounded-pill btn-animated-y mr-3">
                                    <span class="btn-inner--visible"><i class="ti ti-{{ $isSel ? 'check' : 'plus' }}" id="usr_icon_{{ $uid }}"></i></span>
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

    <div class="{{ VC::TX_END }}">
        {{ Form::button($updateBtnLabel, ['type' => 'submit', 'class' => VC::BT_SM_PM.' rounded-pill']) }}
    </div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/projects/tasks/edit.js') }}"></script>
