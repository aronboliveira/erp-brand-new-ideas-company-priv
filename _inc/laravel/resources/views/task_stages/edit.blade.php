@include('partials.helpers.route_helpers')
@php
$taskStage ??= null;
	$lang ??= '';
	$formId ??= 'update-project-task-stage-form';
	$stageId ??= '';
	$stageUpdate ??= ['url' => '#', 'guardMsg' => 'Route unavailable'];
	$nameLabel ??= 'Project Task Stage Title';
	$namePh ??= 'Enter project stage title';
	$colorLabel ??= 'Color';
	$chartHelp ??= 'For chart representation';
	$cancelLabel ??= 'Cancel';
	$updateLabel ??= 'Update';
	$stageName ??= '';
	$stageColor ??= 'FFFFFF';
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$stageId = safeDataGet($taskStage, 'id');
		$stageUpdate = resolveRouteWithGuard(VW::PRJ_TSK_STG.'.update', $lang, VW::PRJ_TSK_STG, 'update_project_task_stage_route_unavailable', [$stageId], 'Update stage route unavailable.');
		$nameLabel = __('Project Task Stage Title') ?: __('No stage title label available');
		$namePh = __('Enter project stage title') ?: __('Failed to get placeholder');
		$colorLabel = __('Color') ?: __('No color label available');
		$chartHelp = __('For chart representation') ?: __('No help text available');
		$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
		$updateLabel = __('Update') ?: __('Failed to get update label');
		$stageName = safeDataGet($taskStage, 'name');
		$stageColor = safeDataGet($taskStage, 'color') ?: 'FFFFFF';
	} catch (\Error $e) {
		Log::error('Error in task_stages/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in task_stages/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in task_stages/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::model($taskStage, [
    'url' => $stageUpdate['url'],
    'method' => 'PUT',
    'id' => $formId,
    'data-resolved-action' => $stageUpdate['url'],
    'data-guard-msg' => $stageUpdate['guardMsg'],
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('name', $nameLabel, ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', $stageName, ['class' => VC::FM_CT, 'placeholder' => $namePh]) }}
                </div>
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('color', $colorLabel, ['class' => VC::FM_LB]) }}
                <input class="jscolor {{ VC::FM_CT }}" value="{{ $stageColor }}" name="color" id="color" required>
                <small class="{{ VC::TXSM }}">{{ $chartHelp }}</small>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ $updateLabel }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/projects/tasks/stages/update.js') }}"></script>
