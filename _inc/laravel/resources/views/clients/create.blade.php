@php
$customFields ??= null;
	$lang ??= '';
	$storeRouteName ??= VW::CLT;
	$storeGuard ??= '';
	$formParams ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$storeGuard = Utility::fetchLinkMessage($lang, VW::CLT, 'store_client_route_unavailable')
			?? __('Store client route is unavailable. Please contact technical support or your domain administrator.');
		$formParams = [
			'method'             => 'post',
			'id'                 => 'store_client',
			'data-sv-localized'  => 'true',
			'data-guard-msg'     => $storeGuard,
			'data-action-href'   => Route::has($storeRouteName) ? (route($storeRouteName) ?? '#') : '#',
		];
		if (Route::has($storeRouteName)) {
			$formParams['route'] = [$storeRouteName];
		} else {
			$formParams['url'] = '#';
		}
	} catch (\Error $e) {
		Log::error('Error in clients/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in clients/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in clients/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open($formParams) }}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::FM_G }}">
				{{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
				{{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter client Name'), 'required' => 'required']) }}
				@error('name')
					<small class="invalid-name" role="alert"><strong class="{{ VC::TXT_MT }}">{{ $message }}</strong></small>
				@enderror
			</div>
			<div class="{{ VC::FM_G }}">
				{{ Form::label('email', __('E-Mail Address'), ['class' => VC::FM_LB]) }}
				{{ Form::email('email', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Client Email'), 'required' => 'required']) }}
				@error('email')
					<small class="invalid-email" role="alert"><strong class="{{ VC::TXT_MT }}">{{ $message }}</strong></small>
				@enderror
			</div>
			<div class="{{ VC::FM_G }}">
				{{ Form::label('password', __('Password'), ['class' => VC::FM_LB]) }}
				{{ Form::password('password', ['class' => VC::FM_CT, 'placeholder' => __('Enter User Password'), 'required' => 'required', 'minlength' => 6]) }}
				@error('password')
					<small class="invalid-password" role="alert"><strong class="{{ VC::TXT_MT }}">{{ $message }}</strong></small>
				@enderror
			</div>
			@if(!empty($customFields) && method_exists($customFields,'isEmpty') ? !$customFields->isEmpty() : !empty($customFields))
				@include(VW::CST_FD . '.formBuilder')
			@endif
		</div>
	</div>
	<div class="modal-footer">
		<input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
	</div>
	<script defer src="{{ asset('assets/js/routes/clients/store.js') }}"></script>
{{ Form::close() }}
