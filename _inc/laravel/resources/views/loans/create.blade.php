@php
$lang ??= 'en';
	$formId ??= 'ln-store-form';
	$storeBase ??= '';
	$storeKebab ??= '';
	$storeRes ??= null;
	$storeUrl ??= '#';
	$storeGuard ??= '';
	$employeeId ??= '';
	$titleErr ??= false;
	$titleAttrs ??= [];
	$loanOptsIsList ??= false;
	$loanOpts ??= ['' => __('No loan options available')];
	$loanOptErr ??= false;
	$loanOptAttrs ??= [];
	$loanTypesIsList ??= false;
	$loanTypes ??= ['' => __('No loan types available')];
	$typeErr ??= false;
	$typeAttrs ??= [];
	$amountErr ??= false;
	$amountAttrs ??= [];
	$reasonErr ??= false;
	$reasonAttrs ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$storeBase = VW::LN;
		$storeKebab = Str::kebab($storeBase);
		$storeRes = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
		$storeUrl = $storeRes ? (route($storeRes) ?? '#') : '#';
		$storeGuard = Utility::fetchLinkMessage($lang, VW::LN, 'loan_store_route_unavailable')
			?? __('Loan store route is unavailable. Please contact technical support or your domain administrator.');
		$employeeId = (string) data_get($employee ?? null, 'id', '');
		$titleErr = !empty($errors) && method_exists($errors, 'has') ? $errors->has('title') : false;
		$titleAttrs = [
			'id' => 'title',
			'class' => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $titleErr ? 'true' : 'false',
			'aria-describedby' => $titleErr ? 'title-error' : null,
			'autocomplete' => 'off',
		];
		$loanOptsIsList = (is_array($loan_options ?? null) && count($loan_options ?? []) > 0)
			|| (($loan_options ?? null) instanceof Collection && $loan_options->isNotEmpty());
		$loanOpts = $loanOptsIsList
			? (is_array($loan_options) ? $loan_options : $loan_options->toArray())
			: ['' => __('No loan options available')];
		$loanOptErr = !empty($errors) && method_exists($errors, 'has') ? $errors->has('loan_option') : false;
		$loanOptAttrs = [
			'id' => 'loan_option',
			'class' => trim(VC::FM_CT_SL . ' select ' . ($loanOptErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $loanOptErr ? 'true' : 'false',
			'aria-describedby' => $loanOptErr ? 'loan_option-error' : null,
		];
		if (!$loanOptsIsList) { $loanOptAttrs['disabled'] = 'disabled'; }
		$loanTypesIsList = (is_array($loan ?? null) && count($loan ?? []) > 0)
			|| (($loan ?? null) instanceof Collection && $loan->isNotEmpty());
		$loanTypes = $loanTypesIsList
			? (is_array($loan) ? $loan : $loan->toArray())
			: ['' => __('No loan types available')];
		$typeErr = !empty($errors) && method_exists($errors, 'has') ? $errors->has('type') : false;
		$typeAttrs = [
			'id' => 'type',
			'class' => trim(VC::FM_CT_SL . ' select amount_type ' . ($typeErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $typeErr ? 'true' : 'false',
			'aria-describedby' => $typeErr ? 'type-error' : null,
		];
		if (!$loanTypesIsList) { $typeAttrs['disabled'] = 'disabled'; }
		$amountErr = !empty($errors) && method_exists($errors, 'has') ? $errors->has('amount') : false;
		$amountAttrs = [
			'id' => 'amount',
			'class' => trim(VC::FM_CT . ' ' . ($amountErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $amountErr ? 'true' : 'false',
			'aria-describedby' => $amountErr ? 'amount-error' : null,
			'step' => '0.01',
			'inputmode' => 'decimal',
		];
		$reasonErr = !empty($errors) && method_exists($errors, 'has') ? $errors->has('reason') : false;
		$reasonAttrs = [
			'id' => 'reason',
			'class' => trim(VC::FM_CT . ' ' . ($reasonErr ? 'is-invalid' : '')),
			'required' => 'required',
			'rows' => 3,
			'aria-invalid' => $reasonErr ? 'true' : 'false',
			'aria-describedby' => $reasonErr ? 'reason-error' : null,
		];
	} catch (\Error $e) {
		Log::error('Error in loans/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in loans/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in loans/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    {{ Form::hidden('employee_id', $employeeId, ['id' => 'employee_id']) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('loan_option', __('Loan Options'), ['class' => VC::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
                {{ Form::select('loan_option', $loanOpts, null, $loanOptAttrs) }}
                @error('loan_option')
                    <span id="loan_option-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $loanTypes, null, $typeAttrs) }}
                @error('type')
                    <span id="type-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('amount', __('Loan Amount'), ['class' => 'form-label amount_label']) }}
                {{ Form::number('amount', null, $amountAttrs) }}
                @error('amount')
                    <span id="amount-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('reason', __('Reason'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('reason', null, $reasonAttrs) }}
                @error('reason')
                    <span id="reason-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/loans/store.js') }}"></script>
{{ Form::close() }}
