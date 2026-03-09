@php
$lang ??= 'en';
	$routeName ??= '';
	$storeRoute ??= '#';
	$formId ??= 'commission_create_form';
	$guardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$routeName = ViewsConstants::COM;
		$storeRoute = Route::has($routeName) ? (route($routeName) ?? '#') : '#';
		$formId = 'commission_create_form_' . (data_get($employee ?? null, 'id', '') ?: 'new');
		$guardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::COM, 'commission_store_route_unavailable')
			?? 'Commission store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in commissions/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in commissions/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in commissions/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open([
    'url'            => $storeRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $guardMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                @if(!empty($commissions) && ((is_array($commissions) && count($commissions)) || ($commissions instanceof \Illuminate\Support\Collection && !$commissions->isEmpty())))
                    {{ Form::select('type', $commissions, null, ['class' => VC::FM_CT . ' select amount_type', 'required' => 'required']) }}
                @else
                    {{ Form::select('type', ['' => __('No commission types available')], null, ['class' => VC::FM_CT . ' select amount_type', 'disabled' => 'disabled']) }}
                @endif
            </div>
            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB . ' amount_label']) }}
                {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Create') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
    <script defer>window.RouteGuard?.guardFormSubmit?.('{{ $formId }}');</script>
{{ Form::close() }}
