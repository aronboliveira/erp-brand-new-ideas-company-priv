@php
$lang ??= 'en';
	$awardtypeId ??= null;
	$updateRoute ??= '#';
	$formId ??= 'awardtype-update-form';
	$updateMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$awardtypeId = data_get($awardtype ?? null, 'id');
		$updateRoute = $awardtypeId
			? (Route::has(ViewsConstants::AWD_TP . '.update')
				? (route(ViewsConstants::AWD_TP . '.update', $awardtypeId) ?? '#')
				: (Route::has(Str::kebab(ViewsConstants::AWD_TP . '.update'))
					? (route(Str::kebab(ViewsConstants::AWD_TP . '.update'), $awardtypeId) ?? '#')
					: '#'))
			: '#';
		$updateMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::AWD_TP,
			'award_type_update_route_unavailable'
		) ?? 'Award Type update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in award_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in award_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in award_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($awardtype) && isset($awardtype?->id))
    {{ Form::model($awardtype, [
        'url'               => $updateRoute,
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $updateRoute,
        'data-sv-localized' => 'true',
        'data-guard-msg'    => $updateMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ C::RW }}">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ C::FM_GB3 }}">
                        {{ Form::label('name', __('Name'), ['class'=>C::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
                        {{ Form::text('name', null, ['class'=>C::FM_CT,'placeholder'=>__('Enter Award Type Name'),'required'=>'required']) }}
                        @error('name')<span class="{{ VC::TX_DNG }}">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ C::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ C::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer src="{{ asset('assets/js/routes/awardTypes/edit.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <div class="{{ C::ALERT }} {{ C::ALERT_DANGER }}">
                    <h4 class="{{ VC::TX_DNG }}">{{ __('No Award Type found') }}</h4>
                    <p>{{ __('The award type data is invalid or not found. Please refresh the page and try again.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
