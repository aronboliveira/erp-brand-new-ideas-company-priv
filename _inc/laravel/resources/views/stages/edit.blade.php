@php
$lang = Utility::fetchUserLang();
	$stage = null;
	$pipelines = [];
	$updateFormId = 'update-stage-form';
	$stageId = (data_get($stage, 'id') ?? '0');
	$stageName = (string) (data_get($stage, 'name') ?? '');
	$stageUpdateBaseName = ViewsConstants::STG . '.update';
	$stageUpdateKebabName = Str::kebab($stageUpdateBaseName);
	$stageUpdateResolved = null;
	$stageUpdateActionUrl = '#';
	$stageUpdateGuardMsg = 'Update stage route is unavailable. Please contact technical support or your domain administrator.';
	try {
		$stageUpdateResolved = Route::has($stageUpdateBaseName) ? $stageUpdateBaseName : (Route::has($stageUpdateKebabName) ? $stageUpdateKebabName : null);
		$stageUpdateActionUrl = ($stageUpdateResolved && $stageId > 0) ? route($stageUpdateResolved, $stageId) : '#';
	} catch (UrlGenerationException $e) {
		$stageUpdateActionUrl = '#';
		Log::error('Blade stages.update UrlGenerationException in ' . (__FILE__ ?? 'unknown') . ' at line ' . (__LINE__ ?? 0) . ': ' . ($e->getMessage() ?? 'Unknown') . ';');
	} catch (RouteNotFoundException $e) {
		$stageUpdateActionUrl = '#';
		Log::error('Blade stages.update RouteNotFoundException in ' . (__FILE__ ?? 'unknown') . ' at line ' . (__LINE__ ?? 0) . ': ' . ($e->getMessage() ?? 'Unknown') . ';');
	} catch (\InvalidArgumentException $e) {
		$stageUpdateActionUrl = '#';
		Log::error('Blade stages.update InvalidArgumentException in ' . (__FILE__ ?? 'unknown') . ' at line ' . (__LINE__ ?? 0) . ': ' . ($e->getMessage() ?? 'Unknown') . ';');
	} catch (\Throwable $e) {
		$stageUpdateActionUrl = '#';
		Log::error('Blade stages.update Throwable in ' . (__FILE__ ?? 'unknown') . ' at line ' . (__LINE__ ?? 0) . ': ' . ($e->getMessage() ?? 'Unknown') . ';');
	}
@endphp

{{ Form::model($stage, [
	'url' => $stageUpdateActionUrl,
	'method' => 'put',
	'id' => $updateFormId,
	'data-resolved-action' => $stageUpdateActionUrl,
	'data-guard-msg' => $stageUpdateGuardMsg,
	'data-sv-localized' => 'false',
]) }}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_G }} {{ VC::C12 }}">
				{{ Form::label('name', __('Stage Name'), ['class' => VC::FM_LB]) }}
				{{ Form::text('name', $stageName, ['class' => VC::FM_CT, 'required' => 'required']) }}
			</div>
			<div class="{{ VC::FM_G }} {{ VC::C12 }}">
				{{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
				{{ Form::select('pipeline_id', (array) ($pipelines ?? []), data_get($stage, 'pipeline_id'), ['class' => VC::FM_CT_SL . ' select2', 'required' => 'required']) }}
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
	</div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/stages/update.js') }}"></script>
