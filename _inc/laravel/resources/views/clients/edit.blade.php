@php
$lang ??= 'en';
	$updateRouteName ??= '';
	$updateGuard ??= '';
	$clientId ??= null;
	$formParams ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$updateRouteName = VW::CLT . '.update';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::CLT, 'update_client_route_unavailable')
			?? __('Update client route is unavailable. Please contact technical support or your domain administrator.');
		$clientId = data_get($client ?? null, 'id');
		$formParams = [
			'method'            => 'PUT',
			'id'                => 'edit_client',
			'data-sv-localized' => 'true',
			'data-guard-msg'    => $updateGuard,
			'data-action-href'  => ($clientId && Route::has($updateRouteName)) ? (route($updateRouteName, $clientId) ?? '#') : '#',
		];
		if ($clientId && Route::has($updateRouteName)) {
			$formParams['route'] = [$updateRouteName, $clientId];
		} else {
			$formParams['url'] = '#';
		}
	} catch (\Error $e) {
		Log::error('Error in clients/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in clients/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in clients/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::model($client, $formParams) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Client Name'), 'required' => 'required']) }}
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

            @if(!empty($customFields) && method_exists($customFields,'isEmpty') ? !$customFields->isEmpty() : !empty($customFields))
                @include(VW::CST_FD.'.formBuilder')
            @endif
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>

    <script defer src="{{ asset('assets/js/routes/clients/update.js') }}"></script>
{{ Form::close() }}
