@php
$employee ??= null;
	$allowance_options ??= [];
	$Allowancetypes ??= [];
	$lang ??= '';
	$allowanceStoreRoute ??= '#';
	$formId ??= 'allowance-store-form';
	$allowanceStoreMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$allowanceStoreRoute = Route::has(VW::ALW)
			? (route(VW::ALW) ?? '#')
			: '#';
		$allowanceStoreMsg = Utility::fetchLinkMessage(
			$lang,
			VW::ALW,
			'allowance_store_route_unavailable'
		) ?? 'Allowance store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in allowances/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in allowances/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in allowances/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($employee) && isset($employee?->id))
{{ Form::open([
    'url'              => $allowanceStoreRoute,
    'method'           => 'post',
    'id'               => $formId,
    'data-url'         => $allowanceStoreRoute,
    'data-sv-localized'=> 'true',
    'data-guard-msg'   => $allowanceStoreMsg,
]) }}
{{ Form::hidden('employee_id', $employee->id) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('allowance_option', __('Allowance Options'), ['class' => VC::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
                {{ Form::select('allowance_option', $allowance_options, null, ['class' => VC::FM_CT_SL, 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, ['class' => VC::FM_CT, 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $Allowancetypes, null, ['class' => VC::FM_CT_SL . ' amount_type', 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB . ' amount_label']) }}
                {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required', 'step' => '0.01']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script async src="{{ asset('assets/js/routes/allowances/lang/create.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/allowances/create.js') }}"></script>
{{ Form::close() }}
@else
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }}">
                    <h4 class="{{ VC::TX_DNG }}">{{ __('No Employee found') }}</h4>
                    <p>{{ __('The employee data is invalid or not found. Please refresh the page and try again.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
