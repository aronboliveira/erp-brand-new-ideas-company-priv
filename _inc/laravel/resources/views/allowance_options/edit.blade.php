@php
$allowanceoption ??= null;
	$lang ??= '';
	$updateRoute ??= '#';
	$formId ??= 'allowance-option-update-form';
	$updateMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$updateRoute = (!empty($allowanceoption) && isset($allowanceoption?->id) && Route::has(VW::ALW_OPT.'.update'))
			? (route(VW::ALW_OPT.'.update', $allowanceoption->id) ?? '#')
			: '#';
		$updateMsg = Utility::fetchLinkMessage(
			$lang,
			VW::ALW_OPT,
			'allowance_option_update_route_unavailable'
		) ?? 'Allowance option update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in allowance_options/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in allowance_options/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in allowance_options/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($allowanceoption) && isset($allowanceoption?->id))
	{{ Form::model($allowanceoption, [
		'route'             => [$updateRoute],
		'method'            => 'PUT',
		'id'                => $formId,
		'data-url'          => $updateRoute,
		'data-sv-localized' => 'true',
		'data-guard-msg'    => $updateMsg,
	]) }}
		<div class="modal-body">
			<div class="{{ VCN::RW }}">
				<div class="{{ VCN::C12 }}">
					<div class="{{ VCN::FM_G }}">
						{{ Form::label('name', __('Name'), ['class'=>VCN::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
						{{ Form::text('name', null, ['class'=>VCN::FM_CT, 'placeholder'=>__('Enter Allowance option Name')]) }}
						@error('name')
							<span class="invalid-name" role="alert">
								<strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
							</span>
						@enderror
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<input type="button" value="{{ __('Cancel') }}" class="{{ VCN::BT_LG }}" data-bs-dismiss="modal">
			<input type="submit" value="{{ __('Update') }}" class="{{ VCN::BT_PRM }}">
		</div>
		<script async src="{{ asset('assets/js/routes/allowanceOptions/lang/edit.js') }}"></script>
		<script defer src="{{ asset('assets/js/routes/allowanceOptions/edit.js') }}"></script>
	{{ Form::close() }}
@else
	<div class="modal-body">
		<div class="row">
			<div class="{{ VC::CM12 }}">
				<div class="{{ VCN::ALERT }} {{ VCN::ALERT_DANGER }}">
					<h4 class="{{ VC::TX_DNG }}">{{ __('No Allowance Option found') }}</h4>
					<p>{{ __('The allowance option data is invalid or not found. Please refresh the page and try again.') }}</p>
				</div>
			</div>
		</div>
	</div>
@endif
