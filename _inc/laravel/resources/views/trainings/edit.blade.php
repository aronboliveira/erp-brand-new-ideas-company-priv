@php
$lang ??= 'en';
	$formId ??= 'edit_training';
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateName ??= null;
	$trainingId ??= '';
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$genBase ??= 'generate';
	$genName ??= null;
	$genUrl ??= '#';
	$genGuard ??= '';
	$genId ??= 'training-generate-link-edit';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$updateBase = VW::TNG . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateName = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$trainingId = data_get($training ?? null, 'id', '');
		$updateUrl = ($updateName && $trainingId) ? (route($updateName, [$trainingId]) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::TNG, 'update_training_route_unavailable')
			?? 'Update training route is unavailable. Please contact technical support or your domain administrator.';
		$genName = Route::has($genBase) ? $genBase : (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
		$genUrl = $genName ? (route($genName, ['training']) ?? '#') : '#';
		$genGuard = Utility::fetchLinkMessage($lang, VW::TNG, 'generate_training_edit_route_unavailable')
			?? 'Generate training content for editing route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in trainings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in trainings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in trainings/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($training, [
	'url'                  => $updateUrl,
	'method'               => 'PUT',
	'id'                   => $formId,
	'data-resolved-action' => $updateUrl,
	'data-guard-msg'       => $updateGuard,
	'data-sv-localized'    => 'true',
]) !!}
	<div class="modal-body">
		@php($plan = Utility::getChatGPTSettings())
		@if($plan?->{PlansConstants::COL_GPT} == 1)
			<div class="{{ VC::TX_END }}">
				<a href="{{ $genUrl }}"
				   id="{{ $genId }}"
				   data-size="md"
				   class="{{ VC::BT_SM_PM }} btn-icon"
				   data-ajax-popup-over="true"
				   data-url="{{ $genUrl }}"
				   data-bs-placement="top"
				   data-title="{{ __('Generate content with AI') }}"
				   data-guard-msg="{{ base64_encode($genGuard) }}"
				   data-sv-localized="true">
					<i class="{{ VC::FAS_RB }}"></i>
					<span>{{ __('Generate with AI') }}</span>
				</a>
			</div>
            <script defer src="{{ asset('assets/js/routes/trainings/generateEdit.js') }}"></script>
		@endif

		<div class="row">
			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('branch', __('Branch'), ['class' => VC::FM_LB]) }}
				{{ Form::select('branch', $branches ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('trainer_option', __('Trainer Option'), ['class' => VC::FM_LB]) }}
				{{ Form::select('trainer_option', $options ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('training_type', __('Training Type'), ['class' => VC::FM_LB]) }}
				{{ Form::select('training_type', $trainingTypes ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('trainer', __('Trainer'), ['class' => VC::FM_LB]) }}
				{{ Form::select('trainer', $trainers ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('training_cost', __('Training Cost'), ['class' => VC::FM_LB]) }}
				{{ Form::number('training_cost', null, ['class' => VC::FM_CT, 'step' => '0.01', 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('employee', __('Employee'), ['class' => VC::FM_LB]) }}
				{{ Form::select('employee', $employees ?? [], null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
				{{ Form::date('start_date', null, ['class' => VC::FM_CT]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
				{{ Form::date('end_date', null, ['class' => VC::FM_CT]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
				{{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Description')]) }}
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
	</div>
    <script defer src="{{ asset('assets/js/routes/trainings/update.js') }}"></script>
{!! Form::close() !!}
