@php
$lang ??= 'en';
	$wid ??= '';
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateName ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$wid = (string) data_get($webhooksetting ?? null, 'id', '');
		$updateBase = VW::WBH . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateName = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateName && $wid !== '') ? (route($updateName, [$wid]) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::WBH, 'update_webhook_route_unavailable')
			?? 'Update webhook route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in webhooks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in webhooks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in webhooks/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($webhooksetting, [
    'url'                  => $updateUrl,
    'method'               => 'POST',
    'id'                   => 'edit_webhook',
    'data-resolved-action' => $updateUrl,
    'data-guard-msg'       => $updateGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('module', __('Module'), ['class' => VC::FM_LB]) }}
                {{ Form::select('module', ($modules ?? []), null, ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Module')]) }}
                @error('module')
                    <span class="invalid-module" role="alert">
                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('url', __('Url'), ['class' => VC::FM_LB]) }}
                {{ Form::text('url', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Webhook Url')]) }}
                @error('url')
                    <span class="invalid-name" role="alert">
                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('method', __('Method'), ['class' => VC::FM_LB]) }}
                {{ Form::select('method', ($methods ?? []), null, ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Method')]) }}
                @error('method')
                    <span class="invalid-method" role="alert">
                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/webhooks/update.js') }}"></script>
{!! Form::close() !!}
