@php
    try {
$lang = Utility::fetchUserLang();

        $formId     = 'ot-pay-store-form';
        $storeBase  = VW::OT_PAY;
        $storeKebab = Str::kebab($storeBase);
        $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeRes ? route($storeRes) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::OT_PAY, 'other_payment_store_route_unavailable') ?? __('Other payment store route is unavailable. Please contact technical support or your domain administrator.');

        $employeeId = (string) data_get($employee ?? null, 'id', '');

        $titleErr = $errors->has('title');
        $titleAttrs = [
            'id'               => 'title',
            'class'            => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
            'required'         => 'required',
            'aria-invalid'     => $titleErr ? 'true' : 'false',
            'aria-describedby' => $titleErr ? 'title-error' : null,
            'autocomplete'     => 'off',
        ];

        $typesIsList = (is_array($otherpaytype ?? null) && count($otherpaytype ?? []) > 0) || (($otherpaytype ?? null) instanceof Collection && $otherpaytype->isNotEmpty());
        $typeOptions = $typesIsList ? (is_array($otherpaytype) ? $otherpaytype : $otherpaytype->toArray()) : ['' => __('No types available')];
        $typeErr     = $errors->has('type');
        $typeAttrs   = [
            'id'               => 'type',
            'class'            => trim(VC::FM_CT_SL . ' select amount_type ' . ($typeErr ? 'is-invalid' : '')),
            'required'         => 'required',
            'aria-invalid'     => $typeErr ? 'true' : 'false',
            'aria-describedby' => $typeErr ? 'type-error' : null,
        ];
        if (!$typesIsList) { $typeAttrs['disabled'] = 'disabled'; }

        $amountErr = $errors->has('amount');
        $amountAttrs = [
            'id'               => 'amount',
            'class'            => trim(VC::FM_CT . ' ' . ($amountErr ? 'is-invalid' : '')),
            'required'         => 'required',
            'aria-invalid'     => $amountErr ? 'true' : 'false',
            'aria-describedby' => $amountErr ? 'amount-error' : null,
            'step'             => '0.01',
            'inputmode'        => 'decimal',
        ];
    } catch (\Throwable $e) {
        \Log::error('other_payments/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
    {{ Form::hidden('employee_id', $employeeId, ['id' => 'employee_id']) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $typeOptions, null, $typeAttrs) }}
                @error('type')
                    <span id="type-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB . ' amount_label']) }}
                {{ Form::number('amount', null, $amountAttrs) }}
                @error('amount')
                    <span id="amount-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/otherPayments/store.js') }}"></script>
{{ Form::close() }}
