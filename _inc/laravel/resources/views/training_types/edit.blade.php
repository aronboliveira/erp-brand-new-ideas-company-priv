@php
$lang ??= 'en';
	$formId ??= 'update_training_type_form';
	$typeId ??= '';
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateName ??= null;
	$updateUrl ??= '#';
	$guardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$typeId = data_get($trainingType ?? null, 'id', '');
		$updateBase = VW::TNG_TP . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateName = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateName && $typeId) ? (route($updateName, [$typeId]) ?? '#') : '#';
		$guardMsg = Utility::fetchLinkMessage($lang, VW::TNG_TP, 'update_training_type_route_unavailable') ?? 'Update training type route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in training_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in training_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in training_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($trainingType, [
	'url'                  => $updateUrl,
	'method'               => 'PUT',
	'id'                   => $formId,
	'data-resolved-action' => $updateUrl,
	'data-guard-msg'       => $guardMsg,
	'data-sv-localized'    => 'true',
]) !!}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
				{{ Form::text('name', null, ['class' => VC::FM_CT]) }}
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
	</div>
  <script defer src="{{ asset('assets/js/routes/trainings/types/update.js') }}"></script>
{!! Form::close() !!}
