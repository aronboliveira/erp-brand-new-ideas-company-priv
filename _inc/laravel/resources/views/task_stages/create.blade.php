@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$lang = Utility::fetchUserLang();

	$formId = 'store-project-task-stage-form';
	$stageStoreBaseName = VW::PRJ_TSK_STG . '.new';
	$stageStoreResolved = null;
	$stageStoreActionUrl = '#';
	$stageStoreGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_STG, 'store_project_task_stage_route_unavailable') ?? 'Store project task stage route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$stageStoreResolved = Route::has($stageStoreBaseName)
			? $stageStoreBaseName
			: (Route::has(Str::kebab($stageStoreBaseName)) ? Str::kebab($stageStoreBaseName) : null);
	} catch (\Error $e) {
		Log::error('Blade project_task_stage/create: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade project_task_stage/create: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade project_task_stage/create: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade project_task_stage/create: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$stageStoreActionUrl = $stageStoreResolved ? route($stageStoreResolved) : '#';
	} catch (\Error $e) {
		Log::error('Blade project_task_stage/create: route URL generation error: ' . $e->getMessage());
		$stageStoreActionUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade project_task_stage/create: invalid argument while generating URL: ' . $e->getMessage());
		$stageStoreActionUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade project_task_stage/create: general exception while generating URL: ' . $e->getMessage());
		$stageStoreActionUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade project_task_stage/create: throwable while generating URL: ' . $e->getMessage());
		$stageStoreActionUrl = '#';
	}

	$nameLabel = __('Project Task Stage Name') ?: __('No stage name label available');
	$colorLabel = __('Color') ?: __('No color label available');
	$chartHelp = __('For chart representation') ?: __('No help text available');
	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$createLabel = __('Create') ?: __('Failed to get create label');
@endphp

{{ Form::open([
	'route'                => Str::kebab($stageStoreActionUrl),
	'method'               => 'post',
	'id'                   => $formId,
	'data-resolved-action' => $stageStoreActionUrl,
	'data-guard-msg'       => $stageStoreGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_G }} {{ VC::C12 }}">
				{{ Form::label('name', $nameLabel, ['class' => VC::FM_LB]) }}
				{{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
			</div>
			<div class="{{ VC::FM_G }} {{ VC::C12 }}">
				{{ Form::label('color', $colorLabel, ['class' => VC::FM_LB]) }}
				<input class="jscolor {{ VC::FM_CT }}" value="FFFFFF" name="color" id="color" required>
				<small class="{{ VC::TXSM }}">{{ $chartHelp }}</small>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ $createLabel }}" class="{{ VC::BT_PRM }}">
	</div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/projectTaskStages/store.js') }}"></script>
