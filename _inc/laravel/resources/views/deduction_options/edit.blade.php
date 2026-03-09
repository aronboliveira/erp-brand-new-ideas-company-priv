@php
$deductionOptionUpdateBaseRouteName ??= '';
	$deductionOptionUpdateKebabRouteName ??= '';
	$deductionOptionUpdateResolvedName ??= null;
	$deductionOptionIdValue ??= '';
	$deductionOptionUpdateUrl ??= '#';
	$deductionOptionUpdateFormId ??= 'deduction-option-update-form-x';
	$langValue ??= 'en';
	$deductionOptionUpdateGuardMessage ??= '';
	try {
		$deductionOptionUpdateBaseRouteName = ViewsConstants::DDT_OPT . '.update';
		$deductionOptionUpdateKebabRouteName = Str::kebab($deductionOptionUpdateBaseRouteName);
		$deductionOptionUpdateResolvedName = Route::has($deductionOptionUpdateBaseRouteName)
			? $deductionOptionUpdateBaseRouteName
			: (Route::has($deductionOptionUpdateKebabRouteName) ? $deductionOptionUpdateKebabRouteName : null);
		$deductionOptionIdValue = (string) data_get($deductionoption ?? null, 'id', '');
		$deductionOptionUpdateUrl = ($deductionOptionUpdateResolvedName && $deductionOptionIdValue !== '')
			? (route($deductionOptionUpdateResolvedName, $deductionOptionIdValue) ?? '#')
			: '#';
		$deductionOptionUpdateFormId = 'deduction-option-update-form-' . ($deductionOptionIdValue === '' ? 'x' : $deductionOptionIdValue);
		$langValue = isset($lang) ? $lang : (Utility::fetchUserLang() ?? 'en');
		$deductionOptionUpdateGuardMessage = Utility::fetchLinkMessage($langValue, ViewsConstants::DDT_OPT, 'update_deduction_option_route_unavailable')
			?? 'Update deduction option route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in deduction_options/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in deduction_options/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in deduction_options/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($deductionoption) && isset($deductionoption->id))
    {{ Form::model($deductionoption, [
        'method'            => 'PUT',
        'url'               => $deductionOptionUpdateUrl,
        'id'                => $deductionOptionUpdateFormId,
        'data-url'          => $deductionOptionUpdateUrl,
        'data-guard-msg'    => $deductionOptionUpdateGuardMessage,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                        @php
							try {
							    $hasError = (!empty($errors) && method_exists($errors, 'has')) ? $errors->has('name') : false;
							                                $attrs = [
							                                    'id'               => 'name',
							                                    'class'            => trim(VC::FM_CT . ' ' . ($hasError ? 'is-invalid' : '')),
							                                    'placeholder'      => __('Enter Deduction Option Name'),
							                                    'required'         => 'required',
							                                    'aria-invalid'     => $hasError ? 'true' : 'false',
							                                    'aria-describedby' => $hasError ? 'name-error' : null,
							                                    'autocomplete'     => 'off',
							                                ];
							} catch (\Throwable $e) {
							    \Log::error('deduction_options/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
							}
@endphp
                        {{ Form::text('name', null, $attrs) }}
                        @if($hasError)
                            <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert">
                                <strong class="{{ VC::TX_DNG }}">{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer>
            window.RouteGuard?.guardFormSubmit?.('{{ $deductionOptionUpdateFormId }}');
        </script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="{{ VC::ALERT }} {{ VC::ALERT_D }} mb-0" role="alert">
            {{ __('Deduction Option data is not available.') }}
        </div>
    </div>
@endif
