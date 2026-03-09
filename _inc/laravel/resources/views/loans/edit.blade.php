@php
$hasLoan ??= false;
	$lang ??= 'en';
	$routeName ??= '';
	$actionUrl ??= '#';
	$guardMsg ??= '';
	try {
		$hasLoan = !empty($loan ?? null) && data_get($loan, 'id');
		$lang = Utility::fetchUserLang() ?? 'en';
		$routeName = VW::LN . '.update';
		$actionUrl = ($hasLoan && Route::has($routeName)) ? (route($routeName, $loan->id) ?? '#') : '#';
		$guardMsg = Utility::fetchLinkMessage($lang, VW::LN, 'update_route_unavailable')
			?? __('Update Loan route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in loans/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in loans/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in loans/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if($hasLoan)
    {{ Form::model($loan, [
        'url'               => $actionUrl,
        'method'            => 'PUT',
        'id'                => 'loan-update-form',
        'data-url'          => $actionUrl,
        'data-guard-msg'    => $guardMsg,
        'data-sv-localized' => 'true'
    ]) }}
        @csrf
        <div class="modal-body">
            <div class="{{ VC::CD_BD }} p-0">
                <div class="row">
                    <div class="{{ VC::FM_GCB12 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('title', __('Title')) }}
                            {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                        </div>
                    </div>

                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('loan_option', __('Loan Options')) }}<span class="{{ VC::TX_DNG }}">*</span>
                            {{ Form::select('loan_option', $loan_options, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                        </div>
                    </div>

                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                            {{ Form::select('type', $loans, null, ['class' => VC::FM_CT_SL.' amount_type', 'required' => 'required']) }}
                        </div>
                    </div>

                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('amount', __('Loan Amount'), ['class' => VC::FM_LB.' amount_label']) }}
                            {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                        </div>
                    </div>

                    <div class="{{ VC::FM_GCB12 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('reason', __('Reason')) }}
                            {{ Form::textarea('reason', null, ['class' => VC::FM_CT, 'required' => 'required', 'rows' => 3]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/loans/update.js') }}"></script>
    {{ Form::close() }}

@else
    <p>{{ __('The requested loan could not be found or is unavailable.') }}</p>
@endif
