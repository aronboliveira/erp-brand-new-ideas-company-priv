@php
$lang ??= 'en';
	$formId ??= 'edit_termination';
	$terminationId ??= '';
	$updateBase ??= '';
	$updateResolved ??= null;
	$updateActionUrl ??= '#';
	$updateGuardMsg ??= '';
	$genBase ??= 'generate';
	$genResolved ??= null;
	$genUrl ??= '#';
	$genGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$terminationId = data_get($termination ?? null, 'id', '');
		$updateBase = ViewsConstants::TMN . '.update';
		$updateResolved = Route::has($updateBase)
			? $updateBase
			: (Route::has(Str::kebab($updateBase)) ? Str::kebab($updateBase) : null);
		$updateActionUrl = ($updateResolved && $terminationId) ? (route($updateResolved, $terminationId) ?? '#') : '#';
		$updateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TMN, 'update_termination_unavailable')
			?? 'Update termination route is unavailable. Please contact technical support or your domain administrator.';
		$genResolved = Route::has($genBase)
			? $genBase
			: (Route::has(Str::kebab($genBase)) ? Str::kebab($genBase) : null);
		$genUrl = $genResolved ? (route($genResolved, ['termination']) ?? '#') : '#';
		$genGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TMN, 'generate_edit_unavailable')
			?? 'Generate content route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in terminations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in terminations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in terminations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($termination, [
	'url'                  => $updateActionUrl,
	'method'               => 'PUT',
	'id'                   => $formId,
	'data-resolved-action' => $updateActionUrl,
	'data-guard-msg'       => $updateGuardMsg,
	'data-sv-localized'    => 'true',
]) !!}
	<div class="modal-body">
		@php($plan = Utility::getChatGPTSettings())
		@if($plan?->{PlansConstants::COL_GPT} == 1)
			<div class="{{ VC::TX_END }}">
				<a id="gen-ai-termination"
				   href="{{ $genUrl }}"
				   data-url="{{ $genUrl }}"
				   data-guard-msg="{{ base64_encode($genGuardMsg) }}"
				   data-sv-localized="true"
				   data-size="md"
				   class="{{ VC::BT_SM_PM }} btn-icon"
				   data-ajax-popup-over="true"
				   data-bs-placement="top"
				   data-title="{{ __('Generate content with AI') }}">
					<i class="{{ VC::FAS_RB }}"></i>
					<span>{{ __('Generate with AI') }}</span>
				</a>
			</div>
		@endif

		<div class="row">
			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('employee_id', __('Employee'), ['class' => VC::FM_LB]) }}
				{{ Form::select('employee_id', $employees, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('termination_type', __('Termination Type'), ['class' => VC::FM_LB]) }}
				{{ Form::select('termination_type', $terminationtypes, null, ['class' => VC::FM_CT_SL, 'required' => true]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('notice_date', __('Notice Date'), ['class' => VC::FM_LB]) }}
				{{ Form::date('notice_date', null, ['class' => VC::FM_CT]) }}
			</div>

			<div class="{{ VC::FM_GCB6 }}">
				{{ Form::label('termination_date', __('Termination Date'), ['class' => VC::FM_LB]) }}
				{{ Form::date('termination_date', null, ['class' => VC::FM_CT]) }}
			</div>

			<div class="{{ VC::FM_GCB12 }}">
				{{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
				{{ Form::textarea('description', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Description')]) }}
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
	</div>
    <script defer src="{{ asset('assets/js/routes/terminations/update.js') }}"></script>
    @if($plan?->{PlansConstants::COL_GPT} == 1)
        <script defer src="{{ asset('assets/js/routes/ai/generate/terminationEdit.js') }}"></script>
    @endif
{!! Form::close() !!}
