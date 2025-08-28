@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$taskStage ??= null;

	$lang = Utility::fetchUserLang();
	$formId = 'update-project-task-stage-form';
	$updateBaseName = ViewsConstants::PRJ_TSK_STG . '.update';
	$updateResolved = null;
	$updateActionUrl = '#';
	$updateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_STG, 'update_project_task_stage_route_unavailable') ?? 'Update project task stage route is unavailable. Please contact technical support or your domain administrator.';
	$stageId = data_get($taskStage, 'id') ?? null;

	$nameLabel = __('Project Task Stage Title') ?: __('No stage title label available');
	$namePh = __('Enter project stage title') ?: __('Failed to get placeholder');
	$colorLabel = __('Color') ?: __('No color label available');
	$chartHelp = __('For chart representation') ?: __('No help text available');
	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$updateLabel = __('Update') ?: __('Failed to get update label');

	try {
		$updateResolved = Route::has($updateBaseName)
			? $updateBaseName
			: (Route::has(Str::kebab($updateBaseName)) ? Str::kebab($updateBaseName) : null);
	} catch (\Error $e) {
		Log::error('Blade projectTaskStages/update: route name resolution error (Error): ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade projectTaskStages/update: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade projectTaskStages/update: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade projectTaskStages/update: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$updateActionUrl = ($updateResolved && !empty($stageId)) ? route($updateResolved, $stageId) : '#';
	} catch (\Error $e) {
		Log::error('Blade projectTaskStages/update: route URL generation error (Error): ' . $e->getMessage());
		$updateActionUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade projectTaskStages/update: invalid argument while generating URL: ' . $e->getMessage());
		$updateActionUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade projectTaskStages/update: general exception while generating URL: ' . $e->getMessage());
		$updateActionUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade projectTaskStages/update: throwable while generating URL: ' . $e->getMessage());
		$updateActionUrl = '#';
	}
@endphp

{{ Form::model($taskStage, [
	'route'                  => [$updateActionUrl],
	'method'               => 'PUT',
	'id'                   => $formId,
	'data-resolved-action' => $updateActionUrl,
	'data-guard-msg'       => $updateGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::C12 }}">
				<div class="{{ VC::FM_G }}">
					{{ Form::label('name', $nameLabel, ['class' => VC::FM_LB]) }}
					{{ Form::text('name', data_get($taskStage, 'name') ?? null, ['class' => VC::FM_CT, 'placeholder' => $namePh]) }}
				</div>
			</div>
			<div class="{{ VC::FM_G }} {{ VC::C12 }}">
				{{ Form::label('color', $colorLabel, ['class' => VC::FM_LB]) }}
				<input class="jscolor {{ VC::FM_CT }}" value="{{ data_get($taskStage, 'color') ?? 'FFFFFF' }}" name="color" id="color" required>
				<small class="{{ VC::TXSM }}">{{ $chartHelp }}</small>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ $updateLabel }}" class="{{ VC::BT_PRM }}">
	</div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/projectTaskStages/update.js') }}"></script>
