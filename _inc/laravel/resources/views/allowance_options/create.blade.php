@php
$lang ??= '';
	$storeRoute ??= '#';
	$formId ??= 'allowance-option-store-form';
	$storeMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$storeRoute = Route::has(VW::ALW_OPT)
			? (route(VW::ALW_OPT) ?? '#')
			: '#';
		$storeMsg = Utility::fetchLinkMessage(
			$lang,
			VW::ALW_OPT,
			'allowance_option_store_route_unavailable'
		) ?? 'Allowance option store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in allowance_options/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in allowance_options/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in allowance_options/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open([
	'url'              => $storeRoute,
	'method'           => 'post',
	'id'               => $formId,
	'data-url'         => $storeRoute,
	'data-sv-localized'=> 'true',
	'data-guard-msg'   => $storeMsg,
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
		<input type="submit" value="{{ __('Create') }}" class="{{ VCN::BT_PRM }}">
	</div>
	<script async src="{{ asset('assets/js/routes/allowancesOptions/lang/store.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/allowancesOptions/store.js') }}"></script>
{{ Form::close() }}
